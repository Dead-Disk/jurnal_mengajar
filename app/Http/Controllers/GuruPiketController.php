<?php

namespace App\Http\Controllers;

use App\Models\DispensasiSiswa;
use App\Models\EventKhusus;
use App\Models\IzinGuru;
use App\Models\JadwalPelajaran;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\IzinPresensiSiswa;
use App\Models\Kelas;
use App\Models\PresensiSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\JamSlotResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuruPiketController extends Controller
{
    protected function authorizeGuruPiket()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        if (! ($user->isPetugasIt() || $user->activeRole() === 'guru_piket' || $user->isPiketHariIni())) {
            $redirectRoute = $user->dashboardRouteName();
            if ($redirectRoute === 'piket.dashboard') {
                $redirectRoute = 'home';
            }

            session()->flash('error', 'Akses ditolak: Shift piket Anda sudah berakhir atau belum dimulai.');
            redirect()->route($redirectRoute)->send();
            exit();
        }
    }

    /**
     * Restriksi "batasi hari": guru piket (non-Petugas IT) hanya boleh mengelola
     * presensi pada tanggal yang merupakan hari tugas piketnya (jadwal_piket).
     * Petugas IT / Waka (impersonasi) bebas mengelola tanggal berapa pun.
     */
    protected function authorizeHariTugas(string $tanggal): void
    {
        $user = Auth::user();

        if (! $user instanceof User || $user->isPetugasIt()) {
            return;
        }

        $hari = JadwalPiket::namaHariTanggal(Carbon::parse($tanggal));
        if ($hari === null) {
            return; // Sabtu/Minggu: guard `authorizeGuruPiket` sudah menangani akses umum
        }

        $bertugas = $user->jadwalPiket()->where('hari', $hari)->exists();

        abort_unless(
            $bertugas,
            403,
            "Anda hanya dapat mengelola presensi pada hari tugas piket Anda ({$hari})."
        );
    }

    /**
     * Dashboard Guru Piket
     */
    public function dashboard()
    {
        $this->authorizeGuruPiket();

        $today = now()->toDateString();
        $hariIni = now()->translatedFormat('l');

        $tahunAktif = TahunAjaran::aktif();

        // 1. Total siswa tidak hadir (Sakit / Izin / Alpha) hari ini
        //    (presensi kini per JP — hitung siswa unik agar tidak ganda)
        $siswaTidakHadir = PresensiSiswa::whereDate('tanggal', $today)
            ->whereIn('status', ['Sakit', 'Izin', 'Alpha'])
            ->distinct()
            ->count('id_siswa');

        // 2. Guru tidak hadir / mengajukan izin hari ini
        $guruIzinHariIni = IzinGuru::whereDate('tanggal', $today)->count();

        // 3. Total dispensasi keluar / masuk hari ini
        $dispensasiHariIni = DispensasiSiswa::whereDate('tanggal', $today)->count();

        // 4. Kelas dengan KBM belum terisi (ada sesi KBM hari ini yang jurnalnya belum diisi)
        $jadwalHariIni = JadwalPelajaran::where('hari', $hariIni)
            ->when($tahunAktif, fn ($q) => $q->where('id_tahun_ajaran', $tahunAktif->id))
            ->get();
        $kelasNonAktifIds = EventKhusus::nonAktifClassIdsOnDate(
            $today,
            $jadwalHariIni->pluck('id_kelas')
        );
        $jadwalHariIni = $jadwalHariIni
            ->reject(fn ($jadwal) => $kelasNonAktifIds->contains($jadwal->id_kelas))
            ->values();

        $idJadwalHariIni = $jadwalHariIni->pluck('id');
        $jurnalTerisiIds = $idJadwalHariIni->isEmpty()
            ? collect()
            : Jurnal::whereDate('tanggal', $today)
                ->whereIn('id_jadwal', $idJadwalHariIni)
                ->whereNotNull('materi')
                ->where('materi', '!=', '')
                ->pluck('id_jadwal')
                ->unique();

        $kelasKbmBelumTerisi = $jadwalHariIni
            ->groupBy('id_kelas')
            ->filter(fn ($sessions) => $sessions->contains(fn ($s) => ! $jurnalTerisiIds->contains($s->id)))
            ->count();

        // Recent: 5 dispensasi terbaru hari ini
        $dispensasiTerbaru = DispensasiSiswa::with(['siswa.kelas'])
            ->whereDate('tanggal', $today)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Recent: daftar guru izin hari ini
        $izinGuruHariIni = IzinGuru::with('user')
            ->whereDate('tanggal', $today)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $izinPendingPiket = IzinGuru::where('status', IzinGuru::STATUS_PENDING_PIKET)->count();

        return view('piket.dashboard', compact(
            'today',
            'hariIni',
            'siswaTidakHadir',
            'guruIzinHariIni',
            'dispensasiHariIni',
            'kelasKbmBelumTerisi',
            'dispensasiTerbaru',
            'izinGuruHariIni',
            'izinPendingPiket',
        ));
    }

    /**
     * Presensi Guru Harian
     */
    public function presensiGuru()
    {
        $this->authorizeGuruPiket();

        return view('piket.presensi_guru');
    }

    /**
     * Jurnal KBM Harian — daftar jurnal untuk semua kelas hari ini
     */
    public function jurnalKBM(Request $request)
    {
        $this->authorizeGuruPiket();

        $today = now()->toDateString();
        $tanggal = $request->get('tanggal', $today);

        $dataJurnal = Jurnal::with([
            'guru',
            'guruPengganti',
            'jadwal.guru',
            'jadwal.mapel',
            'jadwal.kelas',
            'jadwal.jamPelajaran',
        ])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($jurnal) use ($today) {
                // Tambah flag editable: hanya bisa edit jika tanggal jurnal = hari ini
                $jurnal->is_editable = $jurnal->tanggal?->toDateString() === $today;

                return $jurnal;
            })
            ->sortBy(function ($jurnal) {
                return $jurnal->jadwal?->jamPelajaran?->jam_mulai ?? '99:99:99';
            })
            ->values();

        $dataJurnal = $this->groupJurnalBerurutan($dataJurnal);

        $gurus = User::orderBy('nama')->get();

        return view('piket.jurnal', compact('dataJurnal', 'tanggal', 'today', 'gurus'));
    }

    /**
     * Gabungkan jurnal yang identitasnya sama dan jamnya bersambung tepat.
     */
    protected function groupJurnalBerurutan($jurnals)
    {
        return $jurnals->reduce(function ($groups, $jurnal) {
            $jam = $jurnal->jadwal?->jamPelajaran;
            $guruId = $jurnal->id_guru ?? $jurnal->jadwal?->id_guru;
            $kelasId = $jurnal->jadwal?->id_kelas;
            $mapelId = $jurnal->jadwal?->id_mapel;

            if (! $jam || ! $jurnal->tanggal || $guruId === null || $kelasId === null || $mapelId === null) {
                $jurnal->display_jam_mulai = $jam?->jam_mulai;
                $jurnal->display_jam_selesai = $jam?->jam_selesai;
                $jurnal->display_materi = $jurnal->materi;
                $jurnal->display_catatan = $jurnal->catatan_kejadian;

                return $groups->push($jurnal);
            }

            $last = $groups->last();
            $lastJamSelesai = $last?->display_jam_selesai;
            $lastKey = $last?->group_key;
            $key = implode('|', [$jurnal->tanggal->toDateString(), $guruId, $kelasId, $mapelId]);

            if ($last && $lastKey === $key && $lastJamSelesai === $jam->jam_mulai) {
                $last->display_jam_selesai = $jam->jam_selesai;
                $last->display_materi = $this->gabungkanTeks($last->display_materi, $jurnal->materi);
                $last->display_catatan = $this->gabungkanTeks($last->display_catatan, $jurnal->catatan_kejadian);

                return $groups;
            }

            $jurnal->group_key = $key;
            $jurnal->display_jam_mulai = $jam->jam_mulai;
            $jurnal->display_jam_selesai = $jam->jam_selesai;
            $jurnal->display_materi = $jurnal->materi;
            $jurnal->display_catatan = $jurnal->catatan_kejadian;

            return $groups->push($jurnal);
        }, collect());
    }

    protected function gabungkanTeks(?string $sebelumnya, ?string $berikutnya): ?string
    {
        return collect([$sebelumnya, $berikutnya])
            ->filter(fn ($teks) => filled(trim($teks)))
            ->unique()
            ->implode(' | ') ?: null;
    }

    /**
     * Presensi Siswa Harian - Tampilkan form input presensi
     */
    public function presensiSiswa(Request $request)
    {
        $this->authorizeGuruPiket();

        $today = now()->toDateString();
        $tanggal = $request->get('tanggal', $today);
        $idKelas = $request->get('id_kelas');
        $kelasTerpilih = $idKelas ? Kelas::find($idKelas) : null;

        // Restriksi hari tugas (guru piket non-IT hanya pada tanggal jadwalnya)
        $this->authorizeHariTugas($tanggal);

        // Ambil daftar kelas untuk dropdown filter
        // (with('jurusan') agar aksesor nama_lengkap tidak memicu lazy loading N+1)
        $kelasList = Kelas::with('jurusan')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        // Ambil siswa berdasarkan kelas yang dipilih
        $siswaQuery = Siswa::with('kelas')
            ->where('status_siswa', 'Aktif')
            ->orderBy('nama');

        if ($idKelas) {
            $siswaQuery->where('id_kelas', $idKelas);
        }

        $dataSiswa = $siswaQuery->get();

        // JP aktif hanya dari master data untuk hari pada tanggal terpilih.
        $hari = Carbon::parse($tanggal)->locale('id')->translatedFormat('l');
        $jamPelajaranList = $this->jamPelajaranUntukPresensi($hari, $kelasTerpilih);

        // JP yang dipilih (default: JP pertama). Fallback bila param `jp` tidak valid.
        $selectedJpId = (int) $request->integer('jp');
        $selectedJp = $jamPelajaranList->firstWhere('id', $selectedJpId) ?? $jamPelajaranList->first();
        $selectedJpId = $selectedJp?->id;

        // Presensi yang sudah ada untuk kelas, tanggal & JP terpilih
        $presensiExisting = collect();
        $jumlahSiswaTerisiPerJp = collect();
        $izinAktif = collect();
        if ($idKelas && $selectedJpId) {
            $presensiExisting = PresensiSiswa::where('tanggal', $tanggal)
                ->where('id_kelas', $idKelas)
                ->where('jam_pelajaran_id', $selectedJpId)
                ->get()
                ->keyBy('id_siswa');

            // Indikator visual per tombol JP: jumlah siswa yang presensinya sudah terisi per JP
            $jumlahSiswaTerisiPerJp = PresensiSiswa::where('tanggal', $tanggal)
                ->where('id_kelas', $idKelas)
                ->whereNotNull('jam_pelajaran_id')
                ->get()
                ->groupBy('jam_pelajaran_id')
                ->map->count();

            $izinAktif = IzinPresensiSiswa::whereIn('id_siswa', $dataSiswa->pluck('id'))
                ->whereDate('tanggal_mulai', '<=', $tanggal)
                ->whereDate('tanggal_selesai', '>=', $tanggal)
                ->orderByDesc('id')
                ->get()
                ->unique('id_siswa')
                ->keyBy('id_siswa');

            foreach ($izinAktif as $siswaId => $izin) {
                $presensi = $presensiExisting->get($siswaId) ?? new PresensiSiswa;
                $presensi->fill([
                    'status' => ucfirst($izin->status),
                    'keterangan' => $izin->keterangan,
                    'foto_surat' => $izin->foto_surat,
                ]);
                $presensi->setAttribute('tanggal_mulai_izin', $izin->tanggal_mulai->toDateString());
                $presensi->setAttribute('tanggal_selesai_izin', $izin->tanggal_selesai->toDateString());
                $presensiExisting->put($siswaId, $presensi);
            }
        }

        return view('piket.presensi_siswa', compact(
            'kelasList',
            'dataSiswa',
            'presensiExisting',
            'izinAktif',
            'tanggal',
            'today',
            'idKelas',
            'jamPelajaranList',
            'selectedJp',
            'selectedJpId',
            'jumlahSiswaTerisiPerJp',
            'hari'
        ));
    }

    /**
     * Slot KBM presensi untuk shift efektif kelas, dengan fallback ke master
     * global hanya bila kelas tidak punya slot khusus shift pada hari tersebut.
     */
    protected function jamPelajaranUntukPresensi(string $hari, ?Kelas $kelas)
    {
        $shiftId = JamSlotResolver::shiftUntukKelas($kelas, TahunAjaran::aktif());
        $query = JamPelajaran::where('hari', $hari)
            ->where('jenis', 'kbm')
            ->whereNotNull('jam_ke');

        if ($shiftId) {
            $slotShift = (clone $query)->where('shift_id', $shiftId);
            $query = $slotShift->exists()
                ? $slotShift
                : $query->whereNull('shift_id');
        } else {
            $query->whereNull('shift_id');
        }

        return $query->urutkanWaktu()
            ->get()
            ->sortByDesc('id')
            ->unique('jam_ke')
            ->sortBy('jam_mulai')
            ->values();
    }

    /**
     * Simpan Presensi Siswa Harian
     */
    public function storePresensiSiswa(Request $request)
    {
        $this->authorizeGuruPiket();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'id_kelas' => 'required|exists:kelas,id',
            'jp' => 'required|integer',
            'presensi' => 'required|array',
            'presensi.*.id_siswa' => 'required|exists:siswa,id',
            'presensi.*.status' => 'required|in:Hadir,Sakit,Izin',
            'presensi.*.keterangan' => 'nullable|string|max:255',
            'presensi.*.foto_surat' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'presensi.*.foto_surat_camera' => 'nullable|string',
            'presensi.*.tanggal_mulai' => 'required_if:presensi.*.status,Sakit,Izin|nullable|date',
            'presensi.*.tanggal_selesai' => 'required_if:presensi.*.status,Sakit,Izin|nullable|date|after_or_equal:presensi.*.tanggal_mulai',
        ]);

        // Restriksi hari tugas : guru piket non-IT hanya pada tanggal jadwalnya
        $this->authorizeHariTugas($validated['tanggal']);

        // Master JP: hanya JP yang tersedia untuk hari & shift kelas yang sah.
        $hari = Carbon::parse($validated['tanggal'])->locale('id')->translatedFormat('l');
        $kelasTerpilih = Kelas::findOrFail($validated['id_kelas']);
        $jamPelajaran = $this->jamPelajaranUntukPresensi($hari, $kelasTerpilih)
            ->firstWhere('id', (int) $request->integer('jp'));

        abort_unless(
            $jamPelajaran !== null,
            422,
            'Jam pelajaran tidak valid untuk tanggal yang dipilih.'
        );

        $user = Auth::user();

        $siswaIds = collect($validated['presensi'])->pluck('id_siswa')->unique()->values()->all();
        $siswaMap = $siswaIds ? Siswa::whereIn('id', $siswaIds)->get()->keyBy('id') : collect();
        abort_if(
            $siswaMap->count() !== count($siswaIds)
                || $siswaMap->contains(fn ($siswa) => (int) $siswa->id_kelas !== (int) $validated['id_kelas']),
            422,
            'Daftar siswa tidak sesuai dengan kelas yang dipilih.'
        );

        // Prefix file: SRT_{KELAS}_JP{n}_{NIS}_{TANGGAL}_{hash}
        $kelas = Kelas::find($validated['id_kelas']);
        $namaKelas = strtoupper(preg_replace('/[\s-]+/', '-', trim(preg_replace('/[^a-zA-Z0-9\s-]/', '', (string) ($kelas?->nama_kelas ?? 'KELAS'))))) ?: 'KELAS';
        $tglStr = str_replace('-', '', (string) $validated['tanggal']);
        $labelJp = $jamPelajaran->jam_ke ? 'JP'.$jamPelajaran->jam_ke : 'JP';

        DB::transaction(function () use ($validated, $request, $user, $siswaMap, $namaKelas, $tglStr, $labelJp, $jamPelajaran, $kelasTerpilih) {
            foreach ($validated['presensi'] as $item) {
                // Hindari galat unique [id_siswa, tanggal, jam_pelajaran_id] saat baris
                // lama ter-soft delete: temukan termasuk baris trashed, lalu restore &
                // perbarui baris yang sama.
                $presensi = $this->presensiUntukSiswaPadaJp(
                    $item['id_siswa'],
                    $validated['tanggal'],
                    $jamPelajaran->id
                );

                // Guard: presensi data testing hanya dapat diubah oleh IT/QA.
                if ($presensi->exists) {
                    $this->authorizeTestingMutation($presensi);
                }

                if ($presensi->trashed()) {
                    $presensi->restore();
                }

                $siswa = $siswaMap->get($item['id_siswa']);
                $nis = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($siswa?->nis ?? $siswa?->nisn ?? $item['id_siswa'])) ?: (string) $item['id_siswa'];
                $hash = substr(md5(uniqid((string) time(), true)), 0, 6);

                // Upload foto surat (opsional) jika status Sakit/Izin (File Upload atau Kamera Live Base64).
                $fotoSuratPath = null;
                $fileSurat = $request->file("presensi.{$item['id_siswa']}.foto_surat");
                $cameraBase64 = $item['foto_surat_camera'] ?? null;

                if (in_array($item['status'], ['Sakit', 'Izin'], true)) {
                    // 1. Cek unggahan file biasa
                    if ($fileSurat && $fileSurat->isValid()) {
                        $ext = strtolower($fileSurat->getClientOriginalExtension()) ?: 'jpg';
                        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
                            $ext = 'jpg';
                        }
                        $filename = "SRT_{$namaKelas}_{$labelJp}_{$nis}_{$tglStr}_{$hash}.{$ext}";
                        $fotoSuratPath = $fileSurat->storeAs('foto_surat', $filename, 'public');
                    }
                    // 2. Cek capture kamera live Base64
                    elseif ($cameraBase64 && preg_match('/^data:(image\/(\w+)|application\/pdf);base64,/', $cameraBase64, $type)) {
                        $data = substr($cameraBase64, strpos($cameraBase64, ',') + 1);
                        $decoded = base64_decode($data);
                        if ($decoded !== false) {
                            $ext = strtolower($type[2] ?? 'jpg');
                            if ($ext === 'jpeg') {
                                $ext = 'jpg';
                            }
                            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
                                $ext = 'jpg';
                            }
                            $filename = "SRT_{$namaKelas}_{$labelJp}_{$nis}_{$tglStr}_{$hash}.{$ext}";
                            Storage::disk('public')->put("foto_surat/{$filename}", $decoded);
                            $fotoSuratPath = "foto_surat/{$filename}";
                        }
                    }

                    // Hapus file lama bila diganti dengan file baru.
                    if ($fotoSuratPath && $presensi->exists && $presensi->foto_surat && $presensi->foto_surat !== $fotoSuratPath) {
                        foreach (['public', 'local'] as $disk) {
                            if (Storage::disk($disk)->exists($presensi->foto_surat)) {
                                Storage::disk($disk)->delete($presensi->foto_surat);
                                break;
                            }
                        }
                    }
                }

                $presensi->fill([
                    'id_kelas' => $validated['id_kelas'],
                    'jp_ke' => $jamPelajaran->jam_ke,
                    'status' => $item['status'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'foto_surat' => $fotoSuratPath ?? $presensi->foto_surat,
                    'id_guru_piket' => $user->id,
                ])->save();

                if (in_array($item['status'], ['Sakit', 'Izin'], true)) {
                    $tanggalMulai = $item['tanggal_mulai'];
                    $tanggalSelesai = $item['tanggal_selesai'];
                    $izin = IzinPresensiSiswa::where('id_siswa', $item['id_siswa'])
                        ->whereDate('tanggal_mulai', $tanggalMulai)
                        ->whereDate('tanggal_selesai', $tanggalSelesai)
                        ->orderByDesc('id')
                        ->first() ?? new IzinPresensiSiswa;

                    if ($izin->exists) {
                        $this->authorizeTestingMutation($izin);
                    }

                    $izin->fill([
                        'id_siswa' => $item['id_siswa'],
                        'id_kelas' => $validated['id_kelas'],
                        'id_guru_piket' => $user->id,
                        'status' => strtolower($item['status']),
                        'tanggal_mulai' => $tanggalMulai,
                        'tanggal_selesai' => $tanggalSelesai,
                        'keterangan' => $item['keterangan'] ?? null,
                        'foto_surat' => $fotoSuratPath ?? $izin->foto_surat,
                    ])->save();

                    $this->terapkanIzinPresensiRentang($izin, $siswaMap->get($item['id_siswa']), $kelasTerpilih, $user);
                }
            }
        });

        return redirect()->route('piket.presensi-siswa', [
            'tanggal' => $validated['tanggal'],
            'id_kelas' => $validated['id_kelas'],
            'jp' => $jamPelajaran->id,
        ])->with('success', 'Presensi siswa berhasil disimpan, termasuk penerapan rentang Sakit/Izin pada seluruh JP.');
    }

    protected function terapkanIzinPresensiRentang(IzinPresensiSiswa $izin, Siswa $siswa, Kelas $kelas, User $user): void
    {
        $tanggal = Carbon::parse((string) $izin->getRawOriginal('tanggal_mulai'));
        $tanggalSelesai = Carbon::parse((string) $izin->getRawOriginal('tanggal_selesai'));

        while ($tanggal->lte($tanggalSelesai)) {
            $hari = $tanggal->locale('id')->translatedFormat('l');
            $jamPelajaranList = $this->jamPelajaranUntukPresensi($hari, $kelas);

            foreach ($jamPelajaranList as $jamPelajaran) {
                $presensi = $this->presensiUntukSiswaPadaJp(
                    $siswa->id,
                    $tanggal->toDateString(),
                    $jamPelajaran->id
                );

                if ($presensi->exists) {
                    $this->authorizeTestingMutation($presensi);
                }

                if ($presensi->trashed()) {
                    $presensi->restore();
                }

                $presensi->fill([
                    'id_kelas' => $kelas->id,
                    'jp_ke' => $jamPelajaran->jam_ke,
                    'status' => ucfirst($izin->status),
                    'keterangan' => $izin->keterangan,
                    'foto_surat' => $izin->foto_surat ?? $presensi->foto_surat,
                    'id_guru_piket' => $user->id,
                ])->save();
            }

            $tanggal->addDay();
        }
    }

    protected function presensiUntukSiswaPadaJp(int $siswaId, string $tanggal, int $jamPelajaranId): PresensiSiswa
    {
        return PresensiSiswa::withTrashed()
            ->where('id_siswa', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->where('jam_pelajaran_id', $jamPelajaranId)
            ->first() ?? new PresensiSiswa([
                'id_siswa' => $siswaId,
                'tanggal' => $tanggal,
                'jam_pelajaran_id' => $jamPelajaranId,
            ]);
    }
}
