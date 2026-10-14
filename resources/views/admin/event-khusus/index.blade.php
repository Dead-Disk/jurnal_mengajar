@extends('layouts.app')

@section('title', 'Agenda & Event Khusus Sekolah - WebJournal Management System')

@push('styles')
<style>
    .event-page .event-stat-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.2rem;
    }

    .event-page .event-table th {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .event-page .event-table td {
        vertical-align: middle;
    }

    .event-page .event-name {
        min-width: 190px;
    }

    .event-modal-dialog {
        width: min(95vw, 64rem);
        max-width: 64rem;
    }

    .event-modal-content {
        position: relative;
        max-height: 90vh;
        max-height: 90dvh;
        overflow: hidden;
    }

    .event-modal-content form {
        display: flex;
        min-height: 0;
        flex-direction: column;
    }

    .event-modal-body {
        min-height: 0;
        overflow-y: auto;
    }

    .event-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1rem;
    }

    .event-main-column {
        min-width: 0;
    }

    .event-class-panel {
        position: absolute;
        inset: 0;
        z-index: 5;
        display: flex;
        flex-direction: column;
        background: #fff;
        visibility: hidden;
        transform: translateX(100%);
        transition: transform 240ms ease, visibility 0s linear 240ms;
    }

    .event-class-panel.is-open {
        visibility: visible;
        transform: translateX(0);
        transition: transform 240ms ease, visibility 0s;
    }

    .event-mobile-class-trigger[hidden] {
        display: none !important;
    }

    .event-class-desktop-header {
        display: none;
    }

    .event-class-mobile-header {
        display: flex;
    }

    .event-class-drag-handle {
        display: none;
    }

    .event-class-scroll {
        min-height: 0;
        flex: 1;
        overflow-y: auto;
    }

    .event-class-workspace {
        display: grid;
        min-height: 100%;
        grid-template-columns: minmax(10rem, 0.8fr) minmax(0, 1.2fr);
        gap: 0.75rem;
        padding: 0.75rem;
    }

    .event-grade-master,
    .event-grade-detail {
        min-width: 0;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
    }

    .event-grade-master {
        padding: 0.75rem;
    }

    .event-grade-detail {
        padding: 1rem;
    }

    .event-grade-master-button {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding: 0.75rem;
        border: 1px solid transparent;
        border-radius: 0.65rem;
        background: #f8fafc;
        color: #334155;
        text-align: left;
        transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease;
    }

    .event-grade-master-button:last-child {
        margin-bottom: 0;
    }

    .event-grade-master-button.is-active {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .event-grade-detail[hidden],
    .event-grade-classes[hidden] {
        display: none;
    }

    .event-grade-mobile-back {
        display: none;
    }

    .event-mobile-class-trigger {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.75rem;
    }

    @media (min-width: 768px) {
        .event-form-grid.has-specific-scope {
            grid-template-columns: minmax(0, 1fr) minmax(18rem, 0.9fr);
            align-items: start;
        }

        .event-form-grid.has-specific-scope .event-class-panel {
            position: static;
            inset: auto;
            display: flex;
            min-width: 0;
            height: 400px;
            max-height: 400px;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            visibility: visible;
            transform: none;
            transition: none;
        }

        .event-class-desktop-header {
            display: flex;
        }

        .event-class-mobile-header,
        .event-mobile-class-trigger {
            display: none;
        }

        .event-class-scroll {
            max-height: calc(400px - 68px);
        }
    }

    @media (max-width: 767.98px) {
        .event-modal-dialog {
            width: auto;
            max-width: none;
            margin: 0.5rem;
        }

        .event-modal-content {
            overflow: visible;
        }

        .event-class-panel {
            position: fixed;
            inset: auto 0 0;
            z-index: 1070;
            width: 100%;
            height: auto;
            max-height: 80vh;
            max-height: 80dvh;
            padding: 0 0 env(safe-area-inset-bottom);
            border-top: 1px solid #e2e8f0;
            border-radius: 1.25rem 1.25rem 0 0;
            background: #fff;
            box-shadow: 0 -1rem 2.5rem rgba(15, 23, 42, 0.2);
            transform: translateY(100%);
            transition: transform 240ms ease, visibility 0s linear 240ms;
        }

        .event-class-panel.is-open {
            transform: translateY(0);
            transition: transform 240ms ease, visibility 0s;
        }

        .event-class-drag-handle {
            display: block;
            width: 3rem;
            height: 0.375rem;
            flex: 0 0 auto;
            margin: 0.5rem auto 0.25rem;
            border-radius: 9999px;
            background: #d1d5db;
        }

        .event-class-mobile-header {
            flex: 0 0 auto;
            padding: 0.5rem 1rem 0.75rem !important;
        }

        .event-class-scroll {
            max-height: calc(80vh - 6.5rem);
            max-height: calc(80dvh - 6.5rem);
            flex: 0 1 auto;
            overflow: hidden;
        }

        .event-class-workspace {
            position: relative;
            display: flex;
            width: 200%;
            min-height: auto;
            gap: 0;
            padding: 0.25rem 0.75rem 0.75rem;
            transition: transform 240ms ease;
            transform: translateX(0);
        }

        .event-class-workspace.is-grade-detail {
            transform: translateX(-50%);
        }

        .event-grade-master,
        .event-grade-detail {
            width: calc(50% - 0.375rem);
            max-height: calc(80dvh - 7rem);
            flex: 0 0 calc(50% - 0.375rem);
        }

        .event-grade-master {
            margin-right: 0.75rem;
        }

        .event-grade-mobile-back {
            display: inline-flex;
            margin: -0.25rem 0 0.75rem;
        }
    }

</style>
@endpush

@section('content')
@php
    $oldTargetClasses = old('target_kelas', []);
    $oldTargetClasses = is_array($oldTargetClasses) ? $oldTargetClasses : [];
    $formatEventScope = static function ($event) use ($kelasByTingkat) {
        $selectedClassIds = collect($event->target_kelas ?? [])->map(fn ($id) => (int) $id)->unique();
        $fullGrades = $kelasByTingkat->filter(function ($gradeClasses) use ($selectedClassIds) {
            $gradeClassIds = $gradeClasses->pluck('id')->map(fn ($id) => (int) $id);

            return $gradeClassIds->isNotEmpty() && $gradeClassIds->diff($selectedClassIds)->isEmpty();
        });
        $fullGradeClassIds = $fullGrades->flatten()->pluck('id')->map(fn ($id) => (int) $id);
        $partialClasses = $kelasByTingkat->flatten()->filter(
            fn ($kelas) => $selectedClassIds->contains((int) $kelas->id)
                && ! $fullGradeClassIds->contains((int) $kelas->id)
        )->pluck('nama_lengkap');

        return [
            'full_grades' => $fullGrades->keys(),
            'classes' => $partialClasses,
        ];
    };
@endphp
<div class="container-fluid px-0 event-page">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="event-stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-calendar2-event"></i>
                </span>
                <h2 class="fw-black text-dark mb-0" style="font-weight: 900; font-size: 1.65rem; letter-spacing: -0.02em;">
                    Agenda &amp; Event Khusus Sekolah
                </h2>
            </div>
            <p class="text-muted mb-0 ms-1" style="font-size: 0.9rem;">
                Kelola agenda sekolah yang berdampak pada kegiatan belajar mengajar dan cakupan kelas.
            </p>
        </div>
        <button type="button" class="btn btn-primary rounded-3 fw-semibold px-3 py-2"
                data-bs-toggle="modal" data-bs-target="#eventModal" id="addEventButton">
            <i class="bi bi-plus-lg me-1"></i> Tambah Event Khusus
        </button>
    </div>

    <div class="alert alert-info border-0 rounded-3 d-flex align-items-start gap-2 mb-4" role="note">
        <i class="bi bi-info-circle-fill mt-1"></i>
        <div class="small">
            <strong>Informasi.</strong> Agenda pada halaman ini tersimpan untuk partisi data akun yang sedang digunakan.
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <span class="event-stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-week"></i></span>
                    <div>
                        <div class="text-muted small">Total agenda</div>
                        <div class="fw-bold fs-4">{{ $events->count() }} <span class="fs-6 fw-normal text-muted">event</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <span class="event-stat-icon bg-success-subtle text-success"><i class="bi bi-play-circle"></i></span>
                    <div>
                        <div class="text-muted small">Sedang berlangsung</div>
                        <div class="fw-bold fs-4">{{ $events->filter(fn ($event) => $event->tanggal_mulai->lte(today()) && $event->tanggal_selesai->gte(today()))->count() }} <span class="fs-6 fw-normal text-muted">event</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <span class="event-stat-icon bg-warning-subtle text-warning-emphasis"><i class="bi bi-hourglass-split"></i></span>
                    <div>
                        <div class="text-muted small">Agenda mendatang</div>
                        <div class="fw-bold fs-4">{{ $events->filter(fn ($event) => $event->tanggal_mulai->gt(today()))->count() }} <span class="fs-6 fw-normal text-muted">event</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Daftar Agenda &amp; Event</h5>
                    <p class="text-muted small mb-0">Agenda yang memengaruhi jadwal KBM sekolah.</p>
                </div>
                <span class="badge rounded-pill bg-light text-secondary border px-3 py-2">
                    <i class="bi bi-list-ul me-1"></i> {{ $events->count() }} agenda
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table event-table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Nama Event</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Tipe Dampak KBM</th>
                            <th class="py-3">Scope</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            @php
                                $status = $event->tanggal_mulai->gt(today())
                                    ? 'Mendatang'
                                    : ($event->tanggal_selesai->lt(today()) ? 'Selesai' : 'Aktif');
                                $impact = [
                                    'non_aktif' => ['Non-Aktifkan KBM', 'bg-danger-subtle text-danger-emphasis'],
                                    'penyesuaian_jam' => ['Penyesuaian Jam (lama)', 'bg-secondary-subtle text-secondary-emphasis'],
                                    'daring' => ['KBM Daring', 'bg-info-subtle text-info-emphasis'],
                                ][$event->tipe_dampak];
                                $statusClass = ['Aktif' => 'bg-success-subtle text-success-emphasis', 'Mendatang' => 'bg-warning-subtle text-warning-emphasis', 'Selesai' => 'bg-secondary-subtle text-secondary-emphasis'][$status];
                                $eventScope = $formatEventScope($event);
                                $fullGradeLabel = $eventScope['full_grades']->isNotEmpty()
                                    ? 'Semua Kelas '.$eventScope['full_grades']->join(' & ')
                                    : null;
                                $visibleClasses = $eventScope['classes']->take(3);
                                $remainingClassCount = max(0, $eventScope['classes']->count() - $visibleClasses->count());
                            @endphp
                            <tr data-event-row
                                data-event-id="{{ $event->id }}"
                                data-name="{{ $event->nama_event }}"
                                data-start="{{ $event->tanggal_mulai->toDateString() }}"
                                data-end="{{ $event->tanggal_selesai->toDateString() }}"
                                data-impact="{{ $event->tipe_dampak }}"
                                data-scope="{{ $event->scope }}"
                                data-classes="{{ implode(',', $event->target_kelas ?? []) }}"
                                data-notes="{{ $event->keterangan }}"
                                data-update-url="{{ route('admin.event-khusus.update', $event) }}">
                                <td class="ps-4 py-3 event-name">
                                    <div class="fw-bold text-dark">{{ $event->nama_event }}</div>
                                    @if($event->keterangan)
                                        <div class="small text-muted">{{ $event->keterangan }}</div>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    {{ $event->tanggal_mulai->translatedFormat('j M Y') }}
                                    @if(!$event->tanggal_mulai->isSameDay($event->tanggal_selesai))
                                        – {{ $event->tanggal_selesai->translatedFormat('j M Y') }}
                                    @endif
                                </td>
                                <td><span class="badge rounded-pill {{ $impact[1] }} px-2 py-2">{{ $impact[0] }}</span></td>
                                <td>
                                    @if($event->scope === 'semua_kelas')
                                        <span class="text-nowrap"><i class="bi bi-people-fill text-primary me-1"></i>Semua Kelas</span>
                                    @else
                                        <span>
                                            <i class="bi bi-people text-primary me-1"></i>
                                            @if($fullGradeLabel)
                                                {{ $fullGradeLabel }}@if($visibleClasses->isNotEmpty()), @endif
                                            @endif
                                            {{ $visibleClasses->isNotEmpty() ? $visibleClasses->join(', ') : ($fullGradeLabel ? '' : 'Rombel tidak tersedia') }}
                                            @if($remainingClassCount > 0)
                                                <span class="badge rounded-pill bg-light text-secondary border ms-1">+{{ $remainingClassCount }} kelas lagi</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td><span class="badge rounded-pill {{ $statusClass }} px-2 py-2">{{ $status }}</span></td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-2 me-1" data-edit-event aria-label="Edit {{ $event->nama_event }}">
                                        <i class="bi bi-pencil-square"></i><span class="d-none d-lg-inline ms-1">Edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.event-khusus.destroy', $event) }}" class="d-inline"
                                          onsubmit="return confirm('Hapus event khusus ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" aria-label="Hapus {{ $event->nama_event }}">
                                            <i class="bi bi-trash"></i><span class="d-none d-lg-inline ms-1">Hapus</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar2-x fs-3 d-block mb-2"></i>
                                    Belum ada agenda event khusus.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 px-4 py-3 rounded-bottom-4">
            <div class="small text-muted"><i class="bi bi-lightbulb me-1"></i> Status dan data pada tabel hanya untuk pratinjau UI.</div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered max-w-5xl event-modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow max-h-[90vh] event-modal-content">
            <form id="eventForm" method="POST" action="{{ route('admin.event-khusus.store') }}"
                  data-store-url="{{ route('admin.event-khusus.store') }}"
                  data-reopen-on-error="{{ $errors->any() ? 'true' : 'false' }}">
                @csrf
                <input type="hidden" name="_method" id="eventFormMethod" value="POST">
                <input type="hidden" name="event_id" id="eventFormEventId" value="{{ old('event_id') }}">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div>
                        <h5 class="modal-title fw-bold" id="eventModalTitle">Tambah Event Khusus</h5>
                        <p class="text-muted small mb-0">Isi detail agenda sekolah pada formulir dummy ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body px-4 py-3 event-modal-body">
                    <div class="event-form-grid" id="eventFormGrid">
                        <div class="event-main-column">
                    @if($errors->any())
                        <div class="alert alert-danger small py-2 mb-3" role="alert">
                            <div class="fw-bold mb-1">Periksa kembali data event:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="eventName" class="form-label fw-semibold">Nama Kegiatan / Event <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="eventName" name="nama_event" required
                               value="{{ old('nama_event') }}"
                               placeholder="Contoh: PTS Semester Ganjil">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="eventStart" class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control rounded-3" id="eventStart" name="tanggal_mulai" required value="{{ old('tanggal_mulai') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="eventEnd" class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control rounded-3" id="eventEnd" name="tanggal_selesai" required value="{{ old('tanggal_selesai') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="eventImpact" class="form-label fw-semibold">Tipe Dampak KBM <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="eventImpact" name="tipe_dampak" required>
                            <option value="" selected disabled>Pilih dampak kegiatan</option>
                            <option value="daring" @selected(old('tipe_dampak') === 'daring')>KBM Daring (PJJ / Belajar Mandiri)</option>
                            <option value="non_aktif" @selected(old('tipe_dampak') === 'non_aktif')>Non-Aktifkan KBM (Libur Khusus / Kegiatan Full)</option>
                        </select>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label fw-semibold mb-2">Scope Kelas <span class="text-danger">*</span></legend>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="scopeAll" value="semua_kelas" @checked(old('scope', 'semua_kelas') === 'semua_kelas')>
                                <label class="form-check-label" for="scopeAll">Semua Kelas</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="scopeSpecific" value="kelas_tertentu" @checked(old('scope') === 'kelas_tertentu')>
                                <label class="form-check-label" for="scopeSpecific">Kelas Tertentu</label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary rounded-3 event-mobile-class-trigger"
                                id="mobileClassTrigger" hidden aria-controls="specificClassOptions">
                            <span>Pilih Rombel Kelas (<span id="selectedClassCount">0</span> terpilih)</span>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </fieldset>

                    <div class="mb-1">
                        <label for="eventNotes" class="form-label fw-semibold">Keterangan / Catatan Tambahan</label>
                        <textarea class="form-control rounded-3" id="eventNotes" name="keterangan" rows="3"
                                  placeholder="Tambahkan informasi atau catatan untuk agenda ini...">{{ old('keterangan') }}</textarea>
                    </div>
                    <div class="alert alert-secondary small py-2 mt-3 mb-0" role="note">
                        <i class="bi bi-info-circle me-1"></i> Data event dan pilihan kelas akan tersimpan sesuai scope yang dipilih.
                    </div>
                        </div>

                        <aside id="specificClassOptions" class="event-class-panel" aria-hidden="true" aria-label="Pilih rombel kelas">
                            <div class="event-class-drag-handle" aria-hidden="true"></div>
                            <div class="event-class-mobile-header align-items-center justify-content-between gap-2 border-bottom px-3">
                                <button type="button" class="btn btn-link text-decoration-none px-0 fw-semibold" id="backToEventForm">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </button>
                                <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 py-2" id="finishClassSelection">
                                    Selesai / Simpan
                                </button>
                            </div>
                            <div class="event-class-desktop-header align-items-center justify-content-between gap-2 px-3 py-3 border-bottom">
                                <div>
                                    <h6 class="fw-bold mb-1">Pilih Rombel Kelas</h6>
                                    <div class="small text-muted"><span data-class-count>0</span> kelas dipilih</div>
                                </div>
                                <i class="bi bi-people text-primary fs-5"></i>
                            </div>
                            <div class="event-class-scroll max-h-[400px] overflow-y-auto">
                                <div class="event-class-workspace" id="eventClassWorkspace">
                                    <section class="event-grade-master" aria-label="Pilih tingkat kelas">
                                        <div class="small text-uppercase fw-bold text-muted mb-2 px-2">Tingkat Kelas</div>
                                        @forelse($kelasByTingkat as $tingkat => $daftarKelas)
                                            @php($gradeKey = \Illuminate\Support\Str::slug($tingkat))
                                            <div class="event-grade-master-row d-flex align-items-center gap-2 mb-2">
                                                <button type="button"
                                                        class="event-grade-master-button flex-grow-1 mb-0 {{ $loop->first ? 'is-active' : '' }}"
                                                        data-grade-select="{{ $gradeKey }}"
                                                        data-grade-label="{{ $tingkat }}"
                                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                                    <span>Kelas {{ $tingkat }}</span><i class="bi bi-chevron-right"></i>
                                                </button>
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input mt-0"
                                                           type="checkbox"
                                                           id="selectAllGrade{{ $loop->index }}"
                                                           data-grade-select-all="{{ $gradeKey }}"
                                                           aria-label="Pilih semua rombel Kelas {{ $tingkat }}">
                                                    <label class="visually-hidden" for="selectAllGrade{{ $loop->index }}">Pilih semua rombel Kelas {{ $tingkat }}</label>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="small text-muted mb-0 px-2">Belum ada data kelas.</p>
                                        @endforelse
                                    </section>

                                    <section class="event-grade-detail" aria-label="Daftar rombel">
                                        <button type="button" class="btn btn-link text-decoration-none fw-semibold px-0 event-grade-mobile-back" id="backToGradeList">
                                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Tingkat
                                        </button>
                                        <h6 class="fw-bold mb-3" id="activeGradeTitle">Pilih tingkat kelas</h6>
                                        @foreach($kelasByTingkat as $tingkat => $daftarKelas)
                                            @php($gradeKey = \Illuminate\Support\Str::slug($tingkat))
                                            <div class="event-grade-classes" data-grade-classes="{{ $gradeKey }}" data-grade-label="{{ $tingkat }}" hidden>
                                                @foreach($daftarKelas as $kelas)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="target_kelas[]"
                                                               id="targetClass{{ $kelas->id }}"
                                                               value="{{ $kelas->id }}"
                                                               data-grade="{{ $gradeKey }}"
                                                               @checked(in_array($kelas->id, $oldTargetClasses))>
                                                        <label class="form-check-label" for="targetClass{{ $kelas->id }}">{{ $kelas->nama_lengkap }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                        @if($kelasByTingkat->isEmpty())
                                            <div class="small text-muted">Data rombel belum tersedia.</div>
                                        @endif
                                    </section>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-3">
                        <i class="bi bi-check2 me-1"></i> Simpan Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="eventToastContainer" aria-live="polite" aria-atomic="true"
     style="position: fixed; top: 18px; right: 18px; z-index: 1090;"></div>
@endsection

@push('scripts')
<script>
    (() => {
        const page = document.querySelector('.event-page');
        const modalElement = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        if (!page || !modalElement || !form) return;

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const title = document.getElementById('eventModalTitle');
        const specificClassOptions = document.getElementById('specificClassOptions');
        const formGrid = document.getElementById('eventFormGrid');
        const classWorkspace = document.getElementById('eventClassWorkspace');
        const mobileClassTrigger = document.getElementById('mobileClassTrigger');
        const selectedClassCount = document.getElementById('selectedClassCount');
        const classCheckboxes = specificClassOptions.querySelectorAll('input[name="target_kelas[]"]');
        const isMobile = () => window.matchMedia('(max-width: 767.98px)').matches;
        const defaultGrade = specificClassOptions.querySelector('[data-grade-select]')?.dataset.gradeSelect || '';
        const closeMobileClassPanel = (restoreFocus = false) => {
            specificClassOptions.classList.remove('is-open');
            specificClassOptions.setAttribute('aria-hidden', 'true');
            if (restoreFocus && !mobileClassTrigger.hidden) mobileClassTrigger.focus();
        };
        const updateSelectedClassCount = () => {
            const count = [...classCheckboxes].filter((checkbox) => checkbox.checked).length;
            selectedClassCount.textContent = count;
            specificClassOptions.querySelectorAll('[data-class-count]').forEach((counter) => {
                counter.textContent = count;
            });
        };
        const setScope = (isSpecific) => {
            formGrid.classList.toggle('has-specific-scope', isSpecific);
            mobileClassTrigger.hidden = !isSpecific;
            if (!isSpecific) {
                closeMobileClassPanel();
            } else {
                specificClassOptions.setAttribute(
                    'aria-hidden',
                    String(isMobile() && !specificClassOptions.classList.contains('is-open'))
                );
            }
        };
        const openMobileClassPanel = () => {
            classWorkspace.classList.remove('is-grade-detail');
            specificClassOptions.classList.add('is-open');
            specificClassOptions.setAttribute('aria-hidden', 'false');
            document.getElementById('backToEventForm').focus();
        };
        const syncGradeSelection = (grade) => {
            const selectAll = specificClassOptions.querySelector(`[data-grade-select-all="${grade}"]`);
            const checkboxes = specificClassOptions.querySelectorAll(`[data-grade-classes="${grade}"] input[name="target_kelas[]"]`);
            const checkedCount = [...checkboxes].filter((checkbox) => checkbox.checked).length;

            selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        };
        const selectGrade = (grade, showDetail = true) => {
            specificClassOptions.querySelectorAll('[data-grade-select]').forEach((button) => {
                const isActive = button.dataset.gradeSelect === grade;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', String(isActive));
            });
            specificClassOptions.querySelectorAll('[data-grade-classes]').forEach((classes) => {
                classes.hidden = classes.dataset.gradeClasses !== grade;
            });
            const selectedGradeButton = specificClassOptions.querySelector(`[data-grade-select="${grade}"]`);
            document.getElementById('activeGradeTitle').textContent = selectedGradeButton
                ? `Rombel ${selectedGradeButton.dataset.gradeLabel}`
                : 'Pilih tingkat kelas';
            if (isMobile()) {
                classWorkspace.classList.toggle('is-grade-detail', showDetail);
            }
        };

        const resetForm = () => {
            form.reset();
            form.action = form.dataset.storeUrl;
            document.getElementById('eventFormMethod').value = 'POST';
            title.textContent = 'Tambah Event Khusus';
            document.getElementById('eventFormEventId').value = '';
            setScope(false);
            document.getElementById('scopeAll').checked = true;
            specificClassOptions.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.checked = false;
                checkbox.indeterminate = false;
            });
            selectGrade(defaultGrade, false);
            updateSelectedClassCount();
        };

        document.getElementById('addEventButton')?.addEventListener('click', resetForm);

        modalElement.querySelectorAll('[name="scope"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                if (radio.checked) setScope(radio.value === 'kelas_tertentu');
            });
        });

        mobileClassTrigger.addEventListener('click', openMobileClassPanel);
        document.getElementById('backToEventForm').addEventListener('click', () => closeMobileClassPanel(true));
        document.getElementById('finishClassSelection').addEventListener('click', () => closeMobileClassPanel(true));
        modalElement.addEventListener('hidden.bs.modal', () => closeMobileClassPanel());
        modalElement.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isMobile() && specificClassOptions.classList.contains('is-open')) {
                event.stopPropagation();
                closeMobileClassPanel(true);
            }
        });

        specificClassOptions.querySelectorAll('[data-grade-select]').forEach((button) => {
            button.addEventListener('click', () => {
                selectGrade(button.dataset.gradeSelect);
            });
        });

        specificClassOptions.querySelectorAll('[data-grade-select-all]').forEach((selectAll) => {
            selectAll.addEventListener('change', () => {
                specificClassOptions.querySelectorAll(`[data-grade-classes="${selectAll.dataset.gradeSelectAll}"] input[name="target_kelas[]"]`)
                    .forEach((checkbox) => {
                        checkbox.checked = selectAll.checked;
                    });
                syncGradeSelection(selectAll.dataset.gradeSelectAll);
                updateSelectedClassCount();
            });
        });

        document.getElementById('backToGradeList').addEventListener('click', () => {
            classWorkspace.classList.remove('is-grade-detail');
        });

        classCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncGradeSelection(checkbox.dataset.grade);
                updateSelectedClassCount();
            });
        });

        page.addEventListener('click', (event) => {
            const editButton = event.target.closest('[data-edit-event]');
            if (!editButton) return;

            const row = editButton.closest('[data-event-row]');
            if (!row) return;

            resetForm();
            title.textContent = 'Edit Event Khusus';
            form.action = row.dataset.updateUrl;
            document.getElementById('eventFormMethod').value = 'PUT';
            document.getElementById('eventFormEventId').value = row.dataset.eventId;
            document.getElementById('eventName').value = row.dataset.name;
            document.getElementById('eventStart').value = row.dataset.start;
            document.getElementById('eventEnd').value = row.dataset.end;
            document.getElementById('eventImpact').value = row.dataset.impact;
            document.getElementById('eventNotes').value = row.dataset.notes || '';
            document.querySelector(`[name="scope"][value="${row.dataset.scope}"]`).checked = true;
            setScope(row.dataset.scope === 'kelas_tertentu');
            if (row.dataset.scope === 'kelas_tertentu') {
                const selectedClasses = (row.dataset.classes || '').split(',');
                classCheckboxes.forEach((checkbox) => {
                    checkbox.checked = selectedClasses.includes(checkbox.value);
                });
                const selectedGrade = [...classCheckboxes].find((checkbox) => checkbox.checked)?.dataset.grade || defaultGrade;
                specificClassOptions.querySelectorAll('[data-grade-select-all]').forEach((selectAll) => {
                    const grade = selectAll.dataset.gradeSelectAll;
                    syncGradeSelection(grade);
                });
                selectGrade(selectedGrade, false);
            } else {
                selectGrade(defaultGrade, false);
            }
            updateSelectedClassCount();
            modal.show();
        });

        form.addEventListener('submit', (event) => {
            if (document.getElementById('eventEnd').value < document.getElementById('eventStart').value) {
                event.preventDefault();
                document.getElementById('eventEnd').setCustomValidity('Tanggal selesai harus sama atau setelah tanggal mulai.');
                document.getElementById('eventEnd').reportValidity();
                document.getElementById('eventEnd').setCustomValidity('');
                return;
            }
            document.getElementById('eventEnd').setCustomValidity('');
        });

        if (form.dataset.reopenOnError === 'true') {
            const oldEventId = document.getElementById('eventFormEventId').value;
            const oldEventRow = [...page.querySelectorAll('[data-event-row]')]
                .find((row) => row.dataset.eventId === oldEventId);
            if (oldEventRow) {
                title.textContent = 'Edit Event Khusus';
                form.action = oldEventRow.dataset.updateUrl;
                document.getElementById('eventFormMethod').value = 'PUT';
            }
            const oldScopeSpecific = document.getElementById('scopeSpecific').checked;
            setScope(oldScopeSpecific);
            const oldSelectedGrade = [...classCheckboxes].find((checkbox) => checkbox.checked)?.dataset.grade || defaultGrade;
            specificClassOptions.querySelectorAll('[data-grade-select-all]').forEach((selectAll) => {
                syncGradeSelection(selectAll.dataset.gradeSelectAll);
            });
            selectGrade(oldSelectedGrade, false);
            updateSelectedClassCount();
            modal.show();
        }
    })();
</script>
@endpush
