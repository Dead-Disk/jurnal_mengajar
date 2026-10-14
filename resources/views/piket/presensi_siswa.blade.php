@extends('layouts.app')

@section('title', 'Presensi Siswa Harian - Guru Piket')

@push('styles')
<style>
    .status-pill-group {
        display: inline-flex;
        gap: 6px;
    }
    .status-pill-group .btn-check:checked + .btn-outline-success {
        background-color: #10b981;
        border-color: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);
    }
    .status-pill-group .btn-check:checked + .btn-outline-danger {
        background-color: #ef4444;
        border-color: #ef4444;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.25);
    }
    .status-pill-group .btn-check:checked + .btn-outline-primary {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.25);
    }
    .status-pill-group label {
        padding: 0.35rem 0.85rem;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
    }
    .status-pill-group label:hover {
        transform: translateY(-1px);
    }
    .camera-container {
        position: relative;
        background: #0f172a;
        border-radius: 1rem;
        overflow: hidden;
        min-height: 280px;
    }
    .camera-overlay-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        color: #fff;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 10;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .camera-switch-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 10;
        transition: all 0.2s;
    }
    .camera-switch-btn:hover {
        background: rgba(30, 41, 59, 0.9);
        color: #38bdf8;
    }
    .camera-scanline {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38bdf8, transparent);
        box-shadow: 0 0 8px #38bdf8;
        animation: scanlineAnim 2.5s ease-in-out infinite;
        z-index: 5;
    }
    @keyframes scanlineAnim {
        0% { top: 0%; opacity: 0.2; }
        50% { top: 96%; opacity: 0.9; }
        100% { top: 0%; opacity: 0.2; }
    }
    .dropzone-file {
        border: 2px dashed #cbd5e1;
        background-color: #f8fafc;
        border-radius: 1rem;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .dropzone-file:hover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-black text-dark mb-1" style="letter-spacing: -0.02em; font-weight: 800; font-size: 1.75rem;">
                Presensi Siswa Harian
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Input dan kelola status kehadiran siswa piket per kelas & jam pelajaran
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark border shadow-sm rounded-3 px-3 py-2 fw-semibold" style="font-size: 0.85rem;">
                <i class="bi bi-calendar3 me-1.5 text-primary"></i>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert"
             style="background: #ecfdf5; color: #065f46; font-size: 0.9rem;">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert"
             style="background: #fef2f2; color: #991b1b; font-size: 0.9rem;">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert"
             style="background: #fef2f2; color: #991b1b; font-size: 0.9rem;">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>
                <strong>Periksa kembali data presensi:</strong>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Form (Compact Header) --}}
    <form method="GET" action="{{ route('piket.presensi-siswa') }}" class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="filterKelas" class="form-label fw-semibold text-dark mb-0 text-nowrap" style="font-size: 0.85rem;">
                        <i class="bi bi-journal-text me-1 text-muted"></i>Kelas <span class="text-danger">*</span>
                    </label>
                    <select name="id_kelas" id="filterKelas"
                            class="form-select form-select-sm rounded-3 @error('id_kelas') is-invalid @enderror"
                            style="width: 230px;" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ $idKelas == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label for="filterTanggal" class="form-label fw-semibold text-dark mb-0 text-nowrap" style="font-size: 0.85rem;">
                        <i class="bi bi-calendar3 me-1 text-muted"></i>Tanggal <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="tanggal"
                           id="filterTanggal"
                           value="{{ $tanggal }}"
                           max="{{ $today }}"
                           class="form-control form-control-sm rounded-3 @error('tanggal') is-invalid @enderror"
                           style="width: 165px;"
                           required>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    @if($selectedJpId)
                        <input type="hidden" name="jp" value="{{ $selectedJpId }}">
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold rounded-3 px-3">
                        <i class="bi bi-search me-1"></i> Tampilkan Siswa
                    </button>
                    @if($idKelas)
                        <a href="{{ route('piket.presensi-siswa') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3 px-3">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- Filter Jam Pelajaran (Pill Buttons) — dinamis dari master data --}}
    @if($idKelas && $jamPelajaranList->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-clock-history me-1 text-primary"></i> Pilih Jam Pelajaran
                        <span class="badge bg-light text-dark border rounded-3 ms-1 px-2 py-1" style="font-size: 0.7rem;">Hari Terdeteksi: {{ $hari }}</span>
                    </h6>
                    <span class="ms-auto small text-muted d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #10b981;"></span>
                            <span class="text-dark">Terisi</span>
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="d-inline-block rounded-circle border" style="width: 10px; height: 10px; border: 2px solid #adb5bd !important;"></span>
                            <span class="text-dark">Belum terisi</span>
                        </span>
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($jamPelajaranList as $jp)
                        @php
                            $isActive = $selectedJpId == $jp->id;
                            $terisi = ($jumlahSiswaTerisiPerJp[$jp->id] ?? 0) > 0;
                            $btnClass = $isActive
                                ? 'btn-primary border-primary text-white shadow-sm'
                                : ($terisi
                                    ? 'btn-light border-success text-success'
                                    : 'btn-light border-secondary text-secondary');
                            $waktu = substr($jp->jam_mulai, 0, 5).'–'.substr($jp->jam_selesai, 0, 5);
                        @endphp
                        <a href="{{ route('piket.presensi-siswa', ['tanggal' => $tanggal, 'id_kelas' => $idKelas, 'jp' => $jp->id]) }}"
                           class="btn btn-sm rounded-pill border fw-semibold d-inline-flex align-items-center gap-2 {{ $btnClass }}"
                           style="padding: 0.35rem 0.9rem;"
                           title="JP {{ $jp->jam_ke }} &middot; {{ $waktu }}">
                            @if($isActive)
                                <i class="bi bi-check-circle-fill"></i>
                            @elseif($terisi)
                                <i class="bi bi-check-circle"></i>
                            @else
                                <i class="bi bi-circle"></i>
                            @endif
                            <span>JP {{ $jp->jam_ke }}</span>
                            <span class="opacity-75 text-nowrap" style="font-size: 0.72rem;">{{ $waktu }}</span>
                            @if($terisi)
                                <span class="badge rounded-pill {{ $isActive ? 'bg-white text-primary' : 'bg-success text-white' }}"
                                      title="Siswa terisi: {{ $jumlahSiswaTerisiPerJp[$jp->id] }}/{{ $dataSiswa->count() }}">
                                    {{ $jumlahSiswaTerisiPerJp[$jp->id] }}/{{ $dataSiswa->count() }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Presensi Table --}}
    @if($idKelas && $jamPelajaranList->isNotEmpty() && $selectedJp)
        <form method="POST" action="{{ route('piket.presensi-siswa.store') }}" id="formPresensi" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="id_kelas" value="{{ $idKelas }}">
            <input type="hidden" name="jp" value="{{ $selectedJp->id }}">

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom rounded-top-4 py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span>Daftar Siswa - {{ $dataSiswa->first()?->kelas?->nama_lengkap ?? 'Kelas' }}</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-3 px-2 py-1" style="font-size: 0.72rem;">
                                    JP {{ $selectedJp->jam_ke }} &middot; {{ substr($selectedJp->jam_mulai, 0, 5) }}–{{ substr($selectedJp->jam_selesai, 0, 5) }}
                                </span>
                                <span class="badge bg-light text-dark border rounded-3 px-2 py-1" id="countPresent">0/{{ $dataSiswa->count() }} Hadir</span>
                            </h5>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-3 fw-semibold px-3" onclick="setAllStatus('Hadir')">
                                <i class="bi bi-check-all me-1"></i> Set Semua Hadir
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary fw-semibold rounded-3 px-3 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Presensi JP {{ $selectedJp->jam_ke }}
                            </button>
                        </div>
                    </div>
                    <div class="text-muted mt-2 d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                        <i class="bi bi-info-circle text-primary"></i>
                        <span>Klik <strong>Sakit</strong> atau <strong>Izin</strong> untuk membuka pop-up input rentang tanggal & bukti surat/kamera. Status <strong>Hadir</strong> langsung diterapkan.</span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($dataSiswa->isNotEmpty())
                        <div class="table-responsive w-full overflow-x-auto">
                            <table class="table table-hover align-middle mb-0 min-w-full">
                                <thead class="bg-light text-secondary text-uppercase fw-semibold" style="font-size: 0.78rem; letter-spacing: 0.03em;">
                                    <tr>
                                        <th class="text-center whitespace-nowrap py-3" style="width: 50px;">NO</th>
                                        <th class="whitespace-nowrap py-3" style="width: 120px;">NISN</th>
                                        <th class="py-3">NAMA SISWA</th>
                                        <th class="whitespace-nowrap py-3" style="width: 320px;">STATUS ABSENSI</th>
                                        <th class="py-3" style="min-width: 250px;">KETERANGAN / STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($dataSiswa as $index => $siswa)
                                        @php
                                            $existing = $presensiExisting->get($siswa->id);
                                            $currentStatus = $existing ? $existing->status : 'Hadir';
                                            $currentKeterangan = $existing ? $existing->keterangan : '';
                                            $existingFoto = $existing ? $existing->foto_surat : null;
                                            $izinRentangAktif = $izinAktif->get($siswa->id);
                                            $tanggalMulaiIzin = $izinRentangAktif?->tanggal_mulai?->toDateString()
                                                ?? $existing?->tanggal_mulai_izin
                                                ?? $tanggal;
                                            $tanggalSelesaiIzin = $izinRentangAktif?->tanggal_selesai?->toDateString()
                                                ?? $existing?->tanggal_selesai_izin
                                                ?? $tanggal;
                                            $pillConfig = [
                                                'Hadir'  => ['cls' => 'btn-outline-success', 'icon' => 'bi-check-circle-fill'],
                                                'Sakit'  => ['cls' => 'btn-outline-danger',  'icon' => 'bi-heart-pulse-fill'],
                                                'Izin'   => ['cls' => 'btn-outline-primary', 'icon' => 'bi-file-earmark-text-fill'],
                                            ];
                                            $hasSurat = !empty($existingFoto);
                                            $isIzinOrSakit = in_array($currentStatus, ['Sakit', 'Izin'], true);
                                        @endphp
                                        <tr id="row_siswa_{{ $siswa->id }}" data-siswa-id="{{ $siswa->id }}" data-siswa-nama="{{ $siswa->nama }}" data-siswa-nisn="{{ $siswa->nisn ?? '-' }}">
                                            <td class="text-center fw-semibold text-dark whitespace-nowrap">{{ $loop->iteration }}</td>
                                            <td class="text-muted small whitespace-nowrap font-mono">{{ $siswa->nisn ?? '-' }}</td>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $siswa->nama }}</div>
                                                
                                                {{-- Hidden Inputs for Form Submission --}}
                                                <input type="hidden" name="presensi[{{ $siswa->id }}][id_siswa]" value="{{ $siswa->id }}">
                                                <input type="hidden" name="presensi[{{ $siswa->id }}][tanggal_mulai]" id="input_tgl_mulai_{{ $siswa->id }}" value="{{ $tanggalMulaiIzin }}">
                                                <input type="hidden" name="presensi[{{ $siswa->id }}][tanggal_selesai]" id="input_tgl_selesai_{{ $siswa->id }}" value="{{ $tanggalSelesaiIzin }}">
                                                <input type="hidden" name="presensi[{{ $siswa->id }}][keterangan]" id="input_keterangan_{{ $siswa->id }}" value="{{ $currentKeterangan }}">
                                                <input type="hidden" name="presensi[{{ $siswa->id }}][foto_surat_camera]" id="input_foto_camera_{{ $siswa->id }}" value="">
                                                <input type="file" name="presensi[{{ $siswa->id }}][foto_surat]" id="input_foto_file_{{ $siswa->id }}" class="d-none" accept="image/*,application/pdf">
                                            </td>
                                            <td>
                                                <div class="status-pill-group">
                                                    @foreach($pillConfig as $status => $cfg)
                                                        <span class="position-relative">
                                                            <input class="btn-check status-radio"
                                                                   type="radio"
                                                                   name="presensi[{{ $siswa->id }}][status]"
                                                                   id="status_{{ $siswa->id }}_{{ $status }}"
                                                                   value="{{ $status }}"
                                                                   data-siswa-id="{{ $siswa->id }}"
                                                                   data-prev-status="{{ $currentStatus }}"
                                                                   {{ $currentStatus === $status ? 'checked' : '' }}
                                                                   onchange="handleStatusChange({{ $siswa->id }}, '{{ $status }}')"
                                                                   required>
                                                            <label class="btn btn-sm rounded-pill {{ $cfg['cls'] }}"
                                                                   for="status_{{ $siswa->id }}_{{ $status }}"
                                                                   title="Pilih {{ $status }}">
                                                                <i class="bi {{ $cfg['icon'] }}"></i>{{ $status }}
                                                            </label>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                {{-- Dynamic Badge & Info Container --}}
                                                <div id="status_container_{{ $siswa->id }}" class="d-flex flex-wrap align-items-center gap-1.5">
                                                    @if($isIzinOrSakit)
                                                        @php
                                                            $badgeCls = $currentStatus === 'Sakit' ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-primary-subtle text-primary border-primary-subtle';
                                                            $iconCls = $currentStatus === 'Sakit' ? 'bi-heart-pulse-fill' : 'bi-file-earmark-text-fill';
                                                            $tglMulaiFmt = \Carbon\Carbon::parse($tanggalMulaiIzin)->translatedFormat('d M');
                                                            $tglSelesaiFmt = \Carbon\Carbon::parse($tanggalSelesaiIzin)->translatedFormat('d M');
                                                            $rangeLabel = $tanggalMulaiIzin === $tanggalSelesaiIzin ? $tglMulaiFmt : ($tglMulaiFmt . ' - ' . $tglSelesaiFmt);
                                                        @endphp
                                                        <span class="badge {{ $badgeCls }} border rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;">
                                                            <i class="bi {{ $iconCls }}"></i>
                                                            <span>{{ $currentStatus }} ({{ $rangeLabel }})</span>
                                                        </span>
                                                        
                                                        @if($hasSurat)
                                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2 fw-semibold d-inline-flex align-items-center gap-1"
                                                                    style="font-size: 0.72rem;"
                                                                    onclick="previewSuratUrl('{{ asset('storage/' . $existingFoto) }}', 'Surat Presensi - {{ addslashes($siswa->nama) }}')">
                                                                <i class="bi bi-file-earmark-image text-primary"></i>
                                                                <span>Lihat Surat</span>
                                                            </button>
                                                        @endif

                                                        <button type="button" class="btn btn-light border btn-sm rounded-pill py-0.5 px-2 text-muted fw-semibold d-inline-flex align-items-center gap-1"
                                                                style="font-size: 0.72rem;"
                                                                onclick="openModalForSiswa({{ $siswa->id }}, '{{ $currentStatus }}')">
                                                            <i class="bi bi-pencil-square"></i>
                                                            <span>Detail</span>
                                                        </button>

                                                        @if($currentKeterangan)
                                                            <div class="w-100 text-muted small mt-0.5 text-truncate" style="max-width: 280px; font-size: 0.76rem;" title="{{ $currentKeterangan }}">
                                                                <i class="bi bi-chat-left-text me-1 opacity-75"></i>{{ $currentKeterangan }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            <span>Hadir di Kelas</span>
                                                        </span>
                                                        @if($currentKeterangan)
                                                            <span class="text-muted small ms-1 text-truncate" style="max-width: 200px; font-size: 0.75rem;" title="{{ $currentKeterangan }}">
                                                                ({{ $currentKeterangan }})
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                            <p class="text-muted mb-0">Tidak ada siswa aktif di kelas ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    @elseif($idKelas)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-clock text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-dark mb-2">Tidak Ada Jam Pelajaran</h5>
                <p class="text-muted mb-3">Belum ada master data jam pelajaran KBM untuk hari <strong>{{ $hari }}</strong> pada tanggal terpilih.</p>
                <a href="{{ route('piket.presensi-siswa') }}" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-funnel-fill text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-dark mb-2">Pilih Kelas Terlebih Dahulu</h5>
                <p class="text-muted mb-3">Silakan pilih kelas dan tanggal pada filter di atas untuk menampilkan daftar siswa.</p>
            </div>
        </div>
    @endif

</div>

{{-- ========================================================================= --}}
{{-- POP-UP MODAL DETAIL PRESENSI (SAKIT / IZIN & KAMERA LIVE)                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="modalDetailPresensi" tabindex="-1" aria-labelledby="modalDetailPresensiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalDetailPresensiLabel">
                            <i class="bi bi-file-medical text-primary me-1"></i> Detail Presensi Siswa
                        </h5>
                        <span id="modalStatusBadge" class="badge bg-danger text-white rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                            Sakit
                        </span>
                    </div>
                    <p class="text-muted small mb-0 mt-0.5">
                        <span id="modalSiswaNama" class="fw-semibold text-dark">-</span> 
                        &middot; NISN: <span id="modalSiswaNisn" class="font-mono text-secondary">-</span>
                    </p>
                </div>
                <button type="button" class="btn-close" onclick="cancelModalPresensi()" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4">
                
                {{-- Toggle Status Pilihan (Sakit / Izin) --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.04em;">
                        Jenis Ketidakhadiran <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="modal_status_choice" id="modal_choice_sakit" value="Sakit" onchange="syncModalStatusStyle('Sakit')">
                        <label class="btn btn-outline-danger rounded-3 flex-fill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" for="modal_choice_sakit">
                            <i class="bi bi-heart-pulse-fill"></i> Sakit (S)
                        </label>

                        <input type="radio" class="btn-check" name="modal_status_choice" id="modal_choice_izin" value="Izin" onchange="syncModalStatusStyle('Izin')">
                        <label class="btn btn-outline-primary rounded-3 flex-fill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" for="modal_choice_izin">
                            <i class="bi bi-file-earmark-text-fill"></i> Izin (I)
                        </label>
                    </div>
                </div>

                {{-- Rentang Tanggal --}}
                <div class="card border border-slate-200 bg-slate-50/50 rounded-3 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark small text-uppercase mb-0" style="letter-spacing: 0.04em;">
                            <i class="bi bi-calendar-range text-primary me-1"></i> Rentang Tanggal Absensi
                        </label>
                        <span id="modalDurationBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                            1 Hari
                        </span>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1" for="modal_tanggal_mulai">Tanggal Mulai</label>
                            <input type="date" id="modal_tanggal_mulai" class="form-control form-control-sm rounded-3 fw-medium" onchange="calculateDuration()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1" for="modal_tanggal_selesai">Tanggal Selesai</label>
                            <input type="date" id="modal_tanggal_selesai" class="form-control form-control-sm rounded-3 fw-medium" onchange="calculateDuration()">
                        </div>
                    </div>

                    {{-- Preset Buttons --}}
                    <div class="d-flex flex-wrap gap-1.5 mt-2.5 pt-2 border-top">
                        <span class="text-muted small me-1 align-self-center" style="font-size: 0.72rem;">Preset Cepat:</span>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill text-dark py-0.5 px-2.5 fw-semibold" onclick="setPresetDays(1)">Hari Ini (1 Hari)</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill text-dark py-0.5 px-2.5 fw-semibold" onclick="setPresetDays(2)">2 Hari</button>
                        <button type="button" class="btn btn-xs btn-white border rounded-pill text-dark py-0.5 px-2.5 fw-semibold" onclick="setPresetDays(3)">3 Hari</button>
                    </div>
                </div>

                {{-- Input Catatan / Keterangan --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small text-uppercase mb-1" for="modal_keterangan" style="letter-spacing: 0.04em;">
                        Catatan / Alasan (Opsional)
                    </label>
                    <textarea id="modal_keterangan" class="form-control rounded-3" rows="2" placeholder="Contoh: Sakit demam tinggi / rawat inap / keperluan keluarga penting" style="font-size: 0.85rem;"></textarea>
                </div>

                {{-- Bukti Surat (Tabs: Mode File vs Mode Kamera Live) --}}
                <div>
                    <label class="form-label fw-bold text-dark small text-uppercase mb-2 d-flex justify-content-between align-items-center" style="letter-spacing: 0.04em;">
                        <span><i class="bi bi-paperclip text-primary me-1"></i> Bukti Surat / Dokumen</span>
                        <span class="text-muted fw-normal text-lowercase" style="font-size: 0.75rem;">(Opsional)</span>
                    </label>

                    {{-- Nav Tabs --}}
                    <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-3" id="buktiSuratTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-3 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1.5"
                                    id="tab-file-btn" data-bs-toggle="pill" data-bs-target="#tab-file-pane" type="button" role="tab" onclick="handleTabSwitch('file')">
                                <i class="bi bi-cloud-arrow-up"></i> Upload Berkas / Foto
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-3 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1.5"
                                    id="tab-camera-btn" data-bs-toggle="pill" data-bs-target="#tab-camera-pane" type="button" role="tab" onclick="handleTabSwitch('camera')">
                                <i class="bi bi-camera-video"></i> Kamera Live
                            </button>
                        </li>
                    </ul>

                    {{-- Tab Content --}}
                    <div class="tab-content" id="buktiSuratTabContent">
                        
                        {{-- Tab 1: Mode File Upload --}}
                        <div class="tab-pane fade show active" id="tab-file-pane" role="tabpanel">
                            <div class="dropzone-file" onclick="document.getElementById('modalFileInput').click()">
                                <i class="bi bi-file-earmark-arrow-up text-primary mb-2 d-block" style="font-size: 2rem;"></i>
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Pilih Berkas atau Tarik ke Sini</div>
                                <p class="text-muted small mb-0" style="font-size: 0.75rem;">Format didukung: JPG, PNG, WEBP, PDF (Maks. 10MB)</p>
                                <input type="file" id="modalFileInput" class="d-none" accept="image/*,application/pdf" onchange="handleModalFileSelect(this)">
                            </div>

                            {{-- File Preview Container --}}
                            <div id="modalFilePreviewBox" class="mt-2.5 p-2.5 border rounded-3 bg-light d-none align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <img id="modalFilePreviewImg" src="" alt="Preview" class="rounded border d-none" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div id="modalFilePreviewPdfIcon" class="d-none bg-danger text-white rounded p-2 text-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-file-pdf fs-4"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <div id="modalFileName" class="fw-semibold text-dark small text-truncate">-</div>
                                        <div id="modalFileSize" class="text-muted" style="font-size: 0.7rem;">-</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2" onclick="clearModalFile()" title="Hapus berkas">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            {{-- Info Surat Sebelumnya jika sudah ada di server --}}
                            <div id="modalExistingSuratBox" class="mt-2.5 p-2.5 border rounded-3 bg-success-subtle border-success-subtle d-none align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-success small">Surat Tersimpan Sebelumnya</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Dokumen sudah tersimpan di server. Unggah baru bila ingin mengganti.</div>
                                    </div>
                                </div>
                                <button type="button" id="modalBtnViewExisting" class="btn btn-sm btn-success rounded-pill py-0.5 px-2.5 fw-semibold" style="font-size: 0.75rem;">
                                    <i class="bi bi-eye me-1"></i> Buka Surat
                                </button>
                            </div>
                        </div>

                        {{-- Tab 2: Mode Kamera Live --}}
                        <div class="tab-pane fade" id="tab-camera-pane" role="tabpanel">
                            <div class="camera-container text-center">
                                
                                {{-- Badges & Controls Overlay --}}
                                <div class="camera-overlay-badge d-flex align-items-center gap-1.5">
                                    <span class="d-inline-block rounded-circle bg-danger" style="width: 8px; height: 8px; animation: pulse 1.5s infinite;"></span>
                                    <span id="cameraStatusLabel">Live View</span>
                                </div>

                                <button type="button" class="camera-switch-btn" onclick="toggleCameraFacing()" title="Balik Kamera Depan / Belakang">
                                    <i class="bi bi-arrow-repeat me-1"></i> Putar Kamera
                                </button>

                                <div class="camera-scanline" id="cameraScanline"></div>

                                {{-- Live Video Stream --}}
                                <video id="modalCameraVideo" autoplay playsinline muted class="w-100 h-100" style="max-height: 320px; object-fit: cover;"></video>
                                
                                {{-- Canvas Hidden for Snapshot --}}
                                <canvas id="modalCameraCanvas" class="d-none"></canvas>

                                {{-- Captured Image Preview --}}
                                <img id="modalCameraSnapshot" src="" alt="Captured Snapshot" class="w-100 h-100 d-none" style="max-height: 320px; object-fit: contain; background: #000;">
                            </div>

                            {{-- Camera Action Buttons --}}
                            <div class="mt-3 text-center">
                                {{-- State 1: Ready to Capture --}}
                                <div id="cameraControlsCapture">
                                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="takeCameraSnapshot()">
                                        <i class="bi bi-camera-fill fs-5"></i> Ambil Foto Surat
                                    </button>
                                </div>

                                {{-- State 2: Snapshot Taken (Retake or Use) --}}
                                <div id="cameraControlsReview" class="d-none d-flex justify-content-center align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" onclick="retakeCameraSnapshot()">
                                        <i class="bi bi-arrow-counterclockwise"></i> Ambil Ulang
                                    </button>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle-fill"></i> Foto Siap Digunakan
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3 fw-semibold" onclick="cancelModalPresensi()">
                    <i class="bi bi-x me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" onclick="applyModalPresensi()">
                    <i class="bi bi-check2-circle me-1.5"></i> Terapkan Presensi
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL PREVIEW SURAT / DOKUMEN                                             --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="modalPreviewSurat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6" id="modalPreviewSuratTitle">Preview Dokumen Surat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3 bg-black">
                <img id="previewSuratSrc" src="" alt="Preview" class="img-fluid rounded-3 d-none" style="max-height: 75vh; object-fit: contain;">
                <iframe id="previewSuratPdf" src="" class="w-100 rounded-3 d-none" style="height: 75vh; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Global State for Modal & Camera
    let activeSiswaId = null;
    let originalStatus = 'Hadir';
    let currentSelectedStatus = 'Sakit';
    let cameraStream = null;
    let cameraFacingMode = 'environment'; // 'environment' (back) or 'user' (front)
    let cameraBase64Data = null;
    let modalBsInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        updatePresentCount();
        const modalEl = document.getElementById('modalDetailPresensi');
        if (modalEl) {
            modalBsInstance = new bootstrap.Modal(modalEl);
            modalEl.addEventListener('hidden.bs.modal', function () {
                stopCameraStream();
            });
        }
    });

    // Update Counter "X/Y Hadir"
    function updatePresentCount() {
        const radios = document.querySelectorAll('input.status-radio:checked');
        let count = 0;
        radios.forEach(function (r) {
            if (r.value === 'Hadir') count++;
        });
        const total = {{ $dataSiswa->count() }};
        const badge = document.getElementById('countPresent');
        if (badge) {
            badge.textContent = count + '/' + total + ' Hadir';
        }
    }

    // Set Semua Hadir Shortcut
    function setAllStatus(status) {
        document.querySelectorAll('input.status-radio').forEach(function (radio) {
            if (radio.value === status) {
                radio.checked = true;
                const siswaId = radio.dataset.siswaId;
                if (siswaId) {
                    radio.dataset.prevStatus = status;
                    updateRowBadgeUI(siswaId, status, '', '', '', null);
                }
            }
        });
        updatePresentCount();
    }

    // Handle when user clicks status radio on main table
    function handleStatusChange(siswaId, newStatus) {
        const radioChecked = document.querySelector('input[name="presensi[' + siswaId + '][status]"]:checked');
        const prevStatus = radioChecked ? radioChecked.dataset.prevStatus : 'Hadir';

        if (newStatus === 'Hadir') {
            // Direct Hadir without modal
            radioChecked.dataset.prevStatus = 'Hadir';
            const ketInput = document.getElementById('input_keterangan_' + siswaId);
            const ket = ketInput ? ketInput.value : '';
            updateRowBadgeUI(siswaId, 'Hadir', '', '', ket, null);
            updatePresentCount();
        } else {
            // Sakit or Izin -> Open Modal
            openModalForSiswa(siswaId, newStatus);
        }
    }

    // Open Modal for Student
    function openModalForSiswa(siswaId, targetStatus) {
        activeSiswaId = siswaId;
        currentSelectedStatus = targetStatus || 'Sakit';

        const row = document.getElementById('row_siswa_' + siswaId);
        const siswaNama = row ? row.dataset.siswaNama : 'Siswa';
        const siswaNisn = row ? row.dataset.siswaNisn : '-';

        const currentRadio = document.querySelector('input[name="presensi[' + siswaId + '][status]"]:checked');
        originalStatus = currentRadio ? currentRadio.dataset.prevStatus || 'Hadir' : 'Hadir';

        // Set Info Header
        document.getElementById('modalSiswaNama').textContent = siswaNama;
        document.getElementById('modalSiswaNisn').textContent = siswaNisn;

        // Sync Status Radio in Modal
        if (currentSelectedStatus === 'Sakit') {
            document.getElementById('modal_choice_sakit').checked = true;
        } else {
            document.getElementById('modal_choice_izin').checked = true;
        }
        syncModalStatusStyle(currentSelectedStatus);

        // Populate Dates
        const tglMulaiInput = document.getElementById('input_tgl_mulai_' + siswaId);
        const tglSelesaiInput = document.getElementById('input_tgl_selesai_' + siswaId);
        const defaultPageDate = '{{ $tanggal }}';

        document.getElementById('modal_tanggal_mulai').value = (tglMulaiInput && tglMulaiInput.value) ? tglMulaiInput.value : defaultPageDate;
        document.getElementById('modal_tanggal_selesai').value = (tglSelesaiInput && tglSelesaiInput.value) ? tglSelesaiInput.value : defaultPageDate;
        calculateDuration();

        // Populate Note
        const ketInput = document.getElementById('input_keterangan_' + siswaId);
        document.getElementById('modal_keterangan').value = ketInput ? ketInput.value : '';

        // Reset Files & Camera States
        clearModalFile();
        resetCameraUI();

        // Check if existing camera snapshot or file input is already attached
        const cameraInput = document.getElementById('input_foto_camera_' + siswaId);
        if (cameraInput && cameraInput.value) {
            cameraBase64Data = cameraInput.value;
            showCapturedSnapshot(cameraBase64Data);
        }

        // Show Modal
        if (modalBsInstance) {
            modalBsInstance.show();
        }
    }

    // Sync Modal Badge and Color Theme
    function syncModalStatusStyle(status) {
        currentSelectedStatus = status;
        const badge = document.getElementById('modalStatusBadge');
        if (status === 'Sakit') {
            badge.className = 'badge bg-danger text-white rounded-pill px-2.5 py-1';
            badge.innerHTML = '<i class="bi bi-heart-pulse-fill me-1"></i> Sakit';
        } else {
            badge.className = 'badge bg-primary text-white rounded-pill px-2.5 py-1';
            badge.innerHTML = '<i class="bi bi-file-earmark-text-fill me-1"></i> Izin';
        }
    }

    // Date Duration Calculation
    function calculateDuration() {
        const startVal = document.getElementById('modal_tanggal_mulai').value;
        const endVal = document.getElementById('modal_tanggal_selesai').value;
        const badge = document.getElementById('modalDurationBadge');
        
        if (!startVal || !endVal) return;

        const d1 = new Date(startVal);
        const d2 = new Date(endVal);

        if (d2 < d1) {
            document.getElementById('modal_tanggal_selesai').value = startVal;
            badge.textContent = '1 Hari';
            return;
        }

        const diffTime = Math.abs(d2 - d1);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        badge.textContent = diffDays + ' Hari';
    }

    function setPresetDays(days) {
        const startInput = document.getElementById('modal_tanggal_mulai');
        const endInput = document.getElementById('modal_tanggal_selesai');
        const startDate = startInput.value ? new Date(startInput.value) : new Date('{{ $tanggal }}');
        
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + (days - 1));

        const yyyy = endDate.getFullYear();
        const mm = String(endDate.getMonth() + 1).padStart(2, '0');
        const dd = String(endDate.getDate()).padStart(2, '0');

        endInput.value = `${yyyy}-${mm}-${dd}`;
        calculateDuration();
    }

    // Tab Switch Handler
    function handleTabSwitch(tabName) {
        if (tabName === 'camera') {
            startCameraStream();
        } else {
            stopCameraStream();
        }
    }

    // Modal File Picker
    function handleModalFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const nameEl = document.getElementById('modalFileName');
            const sizeEl = document.getElementById('modalFileSize');
            const previewImg = document.getElementById('modalFilePreviewImg');
            const pdfIcon = document.getElementById('modalFilePreviewPdfIcon');
            const previewBox = document.getElementById('modalFilePreviewBox');

            nameEl.textContent = file.name;
            sizeEl.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            previewBox.classList.remove('d-none');
            previewBox.classList.add('d-flex');

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                    pdfIcon.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.classList.add('d-none');
                pdfIcon.classList.remove('d-none');
            }
        }
    }

    function clearModalFile() {
        const fileInput = document.getElementById('modalFileInput');
        if (fileInput) fileInput.value = '';
        const previewBox = document.getElementById('modalFilePreviewBox');
        if (previewBox) {
            previewBox.classList.add('d-none');
            previewBox.classList.remove('d-flex');
        }
    }

    // ==========================================
    // HTML5 WEBCAM / KAMERA LIVE LOGIC
    // ==========================================
    function startCameraStream() {
        const video = document.getElementById('modalCameraVideo');
        const scanline = document.getElementById('cameraScanline');
        const statusLabel = document.getElementById('cameraStatusLabel');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Perangkat/browser Anda tidak mendukung akses kamera HTML5.');
            return;
        }

        stopCameraStream();

        const constraints = {
            video: {
                facingMode: { ideal: cameraFacingMode },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(function (stream) {
                cameraStream = stream;
                video.srcObject = stream;
                video.classList.remove('d-none');
                document.getElementById('modalCameraSnapshot').classList.add('d-none');
                if (scanline) scanline.classList.remove('d-none');
                if (statusLabel) statusLabel.textContent = (cameraFacingMode === 'user' ? 'Kamera Depan' : 'Kamera Belakang');
            })
            .catch(function (err) {
                console.warn('Gagal dengan ideal facingMode, mencoba mode default: ', err);
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(function (fallbackStream) {
                        cameraStream = fallbackStream;
                        video.srcObject = fallbackStream;
                        video.classList.remove('d-none');
                        document.getElementById('modalCameraSnapshot').classList.add('d-none');
                    })
                    .catch(function (fallbackErr) {
                        alert('Tidak dapat mengakses kamera: ' + fallbackErr.message + '\nPastikan izin kamera telah diizinkan.');
                    });
            });
    }

    function stopCameraStream() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (track) {
                track.stop();
            });
            cameraStream = null;
        }
    }

    function toggleCameraFacing() {
        cameraFacingMode = (cameraFacingMode === 'user') ? 'environment' : 'user';
        startCameraStream();
    }

    function takeCameraSnapshot() {
        const video = document.getElementById('modalCameraVideo');
        const canvas = document.getElementById('modalCameraCanvas');
        if (!video || !video.videoWidth) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        cameraBase64Data = canvas.toDataURL('image/jpeg', 0.88);
        showCapturedSnapshot(cameraBase64Data);
    }

    function showCapturedSnapshot(base64Url) {
        const video = document.getElementById('modalCameraVideo');
        const snapshotImg = document.getElementById('modalCameraSnapshot');
        const scanline = document.getElementById('cameraScanline');
        const captureGroup = document.getElementById('cameraControlsCapture');
        const reviewGroup = document.getElementById('cameraControlsReview');

        snapshotImg.src = base64Url;
        snapshotImg.classList.remove('d-none');
        video.classList.add('d-none');
        if (scanline) scanline.classList.add('d-none');

        captureGroup.classList.add('d-none');
        reviewGroup.classList.remove('d-none');
    }

    function retakeCameraSnapshot() {
        cameraBase64Data = null;
        resetCameraUI();
        startCameraStream();
    }

    function resetCameraUI() {
        const video = document.getElementById('modalCameraVideo');
        const snapshotImg = document.getElementById('modalCameraSnapshot');
        const scanline = document.getElementById('cameraScanline');
        const captureGroup = document.getElementById('cameraControlsCapture');
        const reviewGroup = document.getElementById('cameraControlsReview');

        video.classList.remove('d-none');
        snapshotImg.classList.add('d-none');
        snapshotImg.src = '';
        if (scanline) scanline.classList.remove('d-none');

        captureGroup.classList.remove('d-none');
        reviewGroup.classList.add('d-none');
    }

    // Apply Presensi from Modal to Row
    function applyModalPresensi() {
        if (!activeSiswaId) return;

        const statusChoice = document.querySelector('input[name="modal_status_choice"]:checked')?.value || currentSelectedStatus;
        const tglMulai = document.getElementById('modal_tanggal_mulai').value;
        const tglSelesai = document.getElementById('modal_tanggal_selesai').value;
        const keterangan = document.getElementById('modal_keterangan').value;

        // Save to Hidden Inputs in Table Row
        const inputMulai = document.getElementById('input_tgl_mulai_' + activeSiswaId);
        const inputSelesai = document.getElementById('input_tgl_selesai_' + activeSiswaId);
        const inputKet = document.getElementById('input_keterangan_' + activeSiswaId);
        const inputCamera = document.getElementById('input_foto_camera_' + activeSiswaId);
        const inputFile = document.getElementById('input_foto_file_' + activeSiswaId);

        if (inputMulai) inputMulai.value = tglMulai;
        if (inputSelesai) inputSelesai.value = tglSelesai;
        if (inputKet) inputKet.value = keterangan;

        // Check if file was picked in modal and transfer to row file input
        const modalFileInput = document.getElementById('modalFileInput');
        let hasStagedFile = false;
        if (modalFileInput && modalFileInput.files && modalFileInput.files.length > 0 && inputFile) {
            try {
                const dt = new DataTransfer();
                dt.items.add(modalFileInput.files[0]);
                inputFile.files = dt.files;
                hasStagedFile = true;
            } catch (e) {
                console.warn('DataTransfer tidak didukung browser:', e);
            }
        }

        // Save camera base64 data
        if (inputCamera) {
            inputCamera.value = cameraBase64Data || '';
            if (cameraBase64Data) hasStagedFile = true;
        }

        // Update Row Radio Button
        const radioBtn = document.getElementById('status_' + activeSiswaId + '_' + statusChoice);
        if (radioBtn) {
            radioBtn.checked = true;
            radioBtn.dataset.prevStatus = statusChoice;
        }

        // Update UI Badge on Table Row
        updateRowBadgeUI(activeSiswaId, statusChoice, tglMulai, tglSelesai, keterangan, (hasStagedFile ? 'staged' : null));

        // Stop Camera & Hide Modal
        stopCameraStream();
        if (modalBsInstance) {
            modalBsInstance.hide();
        }
        updatePresentCount();
    }

    // Cancel Modal Presensi
    function cancelModalPresensi() {
        stopCameraStream();
        if (activeSiswaId) {
            const radioBtn = document.getElementById('status_' + activeSiswaId + '_' + originalStatus);
            if (radioBtn) {
                radioBtn.checked = true;
            }
        }
        if (modalBsInstance) {
            modalBsInstance.hide();
        }
        updatePresentCount();
    }

    // Update Row Visual Badge on Table
    function updateRowBadgeUI(siswaId, status, tglMulai, tglSelesai, keterangan, fileStatus) {
        const container = document.getElementById('status_container_' + siswaId);
        if (!container) return;

        if (status === 'Hadir') {
            container.innerHTML = `
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Hadir di Kelas</span>
                </span>
                ${keterangan ? `<span class="text-muted small ms-1 text-truncate" style="max-width: 200px; font-size: 0.75rem;">(${escapeHtml(keterangan)})</span>` : ''}
            `;
        } else {
            const badgeCls = status === 'Sakit' ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-primary-subtle text-primary border-primary-subtle';
            const iconCls = status === 'Sakit' ? 'bi-heart-pulse-fill' : 'bi-file-earmark-text-fill';
            const rangeStr = (tglMulai && tglSelesai) ? formatDateRange(tglMulai, tglSelesai) : '';

            let fileHtml = '';
            if (fileStatus === 'staged') {
                fileHtml = `
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill py-0.5 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bi bi-paperclip me-1"></i>Bukti Siap
                    </span>
                `;
            }

            container.innerHTML = `
                <span class="badge ${badgeCls} border rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;">
                    <i class="bi ${iconCls}"></i>
                    <span>${status} ${rangeStr ? '(' + rangeStr + ')' : ''}</span>
                </span>
                ${fileHtml}
                <button type="button" class="btn btn-light border btn-sm rounded-pill py-0.5 px-2 text-muted fw-semibold d-inline-flex align-items-center gap-1"
                        style="font-size: 0.72rem;"
                        onclick="openModalForSiswa(${siswaId}, '${status}')">
                    <i class="bi bi-pencil-square"></i>
                    <span>Detail</span>
                </button>
                ${keterangan ? `
                    <div class="w-100 text-muted small mt-0.5 text-truncate" style="max-width: 280px; font-size: 0.76rem;" title="${escapeHtml(keterangan)}">
                        <i class="bi bi-chat-left-text me-1 opacity-75"></i>${escapeHtml(keterangan)}
                    </div>
                ` : ''}
            `;
        }
    }

    // Helper: Format Date Range (e.g. "08 Okt - 10 Okt")
    function formatDateRange(d1Str, d2Str) {
        if (!d1Str || !d2Str) return '';
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const p1 = d1Str.split('-');
        const p2 = d2Str.split('-');
        if (p1.length < 3 || p2.length < 3) return `${d1Str} - ${d2Str}`;

        const m1 = months[parseInt(p1[1], 10) - 1];
        const m2 = months[parseInt(p2[1], 10) - 1];
        const day1 = parseInt(p1[2], 10);
        const day2 = parseInt(p2[2], 10);

        if (d1Str === d2Str) {
            return `${day1} ${m1}`;
        }
        return `${day1} ${m1} - ${day2} ${m2}`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Preview Saved Document Modal
    function previewSuratUrl(url, title) {
        if (!url) return;
        const modal = new bootstrap.Modal(document.getElementById('modalPreviewSurat'));
        document.getElementById('modalPreviewSuratTitle').textContent = title || 'Preview Dokumen Surat';
        
        const imgEl = document.getElementById('previewSuratSrc');
        const pdfEl = document.getElementById('previewSuratPdf');

        if (url.toLowerCase().endsWith('.pdf')) {
            imgEl.classList.add('d-none');
            pdfEl.src = url;
            pdfEl.classList.remove('d-none');
        } else {
            pdfEl.classList.add('d-none');
            pdfEl.src = '';
            imgEl.src = url;
            imgEl.classList.remove('d-none');
        }
        modal.show();
    }
</script>
@endpush