<?php

namespace Tests\Feature;

use App\Models\JamPelajaran;
use App\Models\IzinPresensiSiswa;
use App\Models\Kelas;
use App\Models\PresensiSiswa;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PiketPresensiRentangTest extends TestCase
{
    use RefreshDatabase;

    public function test_izin_rentang_dibuat_untuk_semua_jp_dan_otomatis_ditampilkan(): void
    {
        $this->seed(UserSeeder::class);
        $guruPiket = User::where('email', 'it@school.id')->firstOrFail();
        $this->actingAs($guruPiket);

        $kelas = Kelas::create([
            'nama_kelas' => 'X Uji Rentang',
            'tingkat' => 'X',
        ]);
        $siswa = Siswa::create([
            'nisn' => '9911223344',
            'nis' => 'UJI-RENTANG',
            'nama' => 'Siswa Uji Rentang',
            'jenis_kelamin' => 'L',
            'id_kelas' => $kelas->id,
        ]);

        $slotKamis = $this->buatJamPelajaran('Kamis', 'Senin-Kamis', 1);
        $slotJumat = $this->buatJamPelajaran('Jumat', 'Jumat', 1);
        $slotSabtu = $this->buatJamPelajaran('Sabtu', 'Senin-Kamis', 1);
        Storage::fake('public');

        $this->post(route('piket.presensi-siswa.store'), [
            'tanggal' => '2026-10-08',
            'id_kelas' => $kelas->id,
            'jp' => $slotKamis->id,
            'presensi' => [
                $siswa->id => [
                    'id_siswa' => $siswa->id,
                    'status' => 'Sakit',
                    'tanggal_mulai' => '2026-10-08',
                    'tanggal_selesai' => '2026-10-10',
                    'keterangan' => 'Istirahat di rumah',
                    'foto_surat' => UploadedFile::fake()->createWithContent(
                        'surat-sakit.png',
                        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/xkYAAAAASUVORK5CYII=')
                    ),
                ],
            ],
        ])->assertRedirect(route('piket.presensi-siswa', [
            'tanggal' => '2026-10-08',
            'id_kelas' => $kelas->id,
            'jp' => $slotKamis->id,
        ]));

        $this->post(route('piket.presensi-siswa.store'), [
            'tanggal' => '2026-10-08',
            'id_kelas' => $kelas->id,
            'jp' => $slotKamis->id,
            'presensi' => [
                $siswa->id => [
                    'id_siswa' => $siswa->id,
                    'status' => 'Sakit',
                    'tanggal_mulai' => '2026-10-08',
                    'tanggal_selesai' => '2026-10-10',
                    'keterangan' => 'Istirahat di rumah',
                ],
            ],
        ])->assertRedirect(route('piket.presensi-siswa', [
            'tanggal' => '2026-10-08',
            'id_kelas' => $kelas->id,
            'jp' => $slotKamis->id,
        ]));

        $this->assertDatabaseCount('izin_presensi_siswa', 1);
        $izin = IzinPresensiSiswa::where('id_siswa', $siswa->id)
            ->whereDate('tanggal_mulai', '2026-10-08')
            ->whereDate('tanggal_selesai', '2026-10-10')
            ->firstOrFail();
        $this->assertSame('sakit', $izin->status);
        $this->assertSame('2026-10-08', substr((string) $izin->getRawOriginal('tanggal_mulai'), 0, 10));
        $this->assertSame('2026-10-10', substr((string) $izin->getRawOriginal('tanggal_selesai'), 0, 10));
        Storage::disk('public')->assertExists($izin->foto_surat);

        $this->assertDatabaseCount('presensi_siswa', 3);
        $presensiJumat = PresensiSiswa::where('id_siswa', $siswa->id)
            ->whereDate('tanggal', '2026-10-09')
            ->where('jam_pelajaran_id', $slotJumat->id)
            ->firstOrFail();
        $this->assertSame('Sakit', $presensiJumat->status);
        $this->assertSame('Istirahat di rumah', $presensiJumat->keterangan);
        $this->assertSame($izin->foto_surat, $presensiJumat->foto_surat);

        $presensiSabtu = PresensiSiswa::where('id_siswa', $siswa->id)
            ->whereDate('tanggal', '2026-10-10')
            ->where('jam_pelajaran_id', $slotSabtu->id)
            ->firstOrFail();
        $this->assertSame('Sakit', $presensiSabtu->status);
        $this->assertSame($izin->foto_surat, $presensiSabtu->foto_surat);

        $this->get(route('piket.presensi-siswa', [
            'tanggal' => '2026-10-09',
            'id_kelas' => $kelas->id,
            'jp' => $slotJumat->id,
        ]))
            ->assertOk()
            ->assertSee('name="presensi['.$siswa->id.'][status]"', false)
            ->assertSeeInOrder([
                'id="status_'.$siswa->id.'_Sakit"',
                'value="Sakit"',
                'checked',
            ], false)
            ->assertSee('value="2026-10-08"', false)
            ->assertSee('value="2026-10-10"', false);
    }

    public function test_tanggal_selesai_tidak_boleh_sebelum_tanggal_mulai(): void
    {
        $this->seed(UserSeeder::class);
        $guruPiket = User::where('email', 'it@school.id')->firstOrFail();
        $this->actingAs($guruPiket);

        $kelas = Kelas::create([
            'nama_kelas' => 'X Uji Validasi',
            'tingkat' => 'X',
        ]);
        $siswa = Siswa::create([
            'nisn' => '9911223345',
            'nis' => 'UJI-VALIDASI',
            'nama' => 'Siswa Uji Validasi',
            'jenis_kelamin' => 'L',
            'id_kelas' => $kelas->id,
        ]);
        $jamPelajaran = $this->buatJamPelajaran('Kamis', 'Senin-Kamis', 1);

        $this->from(route('piket.presensi-siswa'))
            ->post(route('piket.presensi-siswa.store'), [
                'tanggal' => '2026-10-08',
                'id_kelas' => $kelas->id,
                'jp' => $jamPelajaran->id,
                'presensi' => [
                    $siswa->id => [
                        'id_siswa' => $siswa->id,
                        'status' => 'Izin',
                        'tanggal_mulai' => '2026-10-09',
                        'tanggal_selesai' => '2026-10-08',
                    ],
                ],
            ])
            ->assertSessionHasErrors('presensi.'.$siswa->id.'.tanggal_selesai');

        $this->assertDatabaseCount('izin_presensi_siswa', 0);
        $this->assertDatabaseCount('presensi_siswa', 0);
    }

    public function test_simpan_presensi_dengan_foto_kamera_live_base64(): void
    {
        $this->seed(UserSeeder::class);
        $guruPiket = User::where('email', 'it@school.id')->firstOrFail();
        $this->actingAs($guruPiket);

        $kelas = Kelas::create([
            'nama_kelas' => 'X Uji Kamera',
            'tingkat' => 'X',
        ]);
        $siswa = Siswa::create([
            'nisn' => '9911223399',
            'nis' => 'UJI-KAMERA',
            'nama' => 'Siswa Uji Kamera',
            'jenis_kelamin' => 'L',
            'id_kelas' => $kelas->id,
        ]);
        $jamPelajaran = $this->buatJamPelajaran('Kamis', 'Senin-Kamis', 1);
        Storage::fake('public');

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/xkYAAAAASUVORK5CYII=';

        $this->post(route('piket.presensi-siswa.store'), [
            'tanggal' => '2026-10-08',
            'id_kelas' => $kelas->id,
            'jp' => $jamPelajaran->id,
            'presensi' => [
                $siswa->id => [
                    'id_siswa' => $siswa->id,
                    'status' => 'Izin',
                    'tanggal_mulai' => '2026-10-08',
                    'tanggal_selesai' => '2026-10-08',
                    'keterangan' => 'Izin via Live Camera',
                    'foto_surat_camera' => $base64Image,
                ],
            ],
        ])->assertRedirect(route('piket.presensi-siswa', [
            'tanggal' => '2026-10-08',
            'id_kelas' => $kelas->id,
            'jp' => $jamPelajaran->id,
        ]));

        $izin = IzinPresensiSiswa::where('id_siswa', $siswa->id)->firstOrFail();
        $this->assertNotNull($izin->foto_surat);
        Storage::disk('public')->assertExists($izin->foto_surat);

        $presensi = PresensiSiswa::where('id_siswa', $siswa->id)->firstOrFail();
        $this->assertSame('Izin', $presensi->status);
        $this->assertSame($izin->foto_surat, $presensi->foto_surat);
    }

    private function buatJamPelajaran(string $hari, string $kategoriHari, int $jamKe): JamPelajaran
    {
        return JamPelajaran::create([
            'hari' => $hari,
            'kategori_hari' => $kategoriHari,
            'jam_ke' => $jamKe,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
            'jenis' => 'kbm',
        ]);
    }
}
