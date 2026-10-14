@extends('layouts.app')

@section('title', 'Data Master Guru - WebJournal Management System')

@push('styles')
<style>
    /* === Filter Bar === */
    .filter-bar {
        background: #ffffff;
        border: 1px solid #e8eef5;
        border-radius: 14px;
        padding: 1.1rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 6px rgba(15, 23, 42, 0.04);
    }

    @media (max-width: 575.98px) {
        .filter-bar {
            padding: 0.9rem 0.9rem;
        }
    }

    .filter-bar .form-control,
    .filter-bar .form-select {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.875rem;
        padding: 0.55rem 0.85rem;
        color: #334155;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .filter-bar .form-control:focus,
    .filter-bar .form-select:focus {
        border-color: var(--primary-blue, #1677ff);
        box-shadow: 0 0 0 3px rgba(22, 119, 255, 0.12);
        background-color: #ffffff;
    }

    .filter-bar .search-wrapper {
        position: relative;
    }

    .filter-bar .search-wrapper i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
        pointer-events: none;
    }

    .filter-bar .search-wrapper .form-control {
        padding-left: 2.4rem;
    }

    /* === Custom Modern Pagination === */
    .custom-pagination-wrapper {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        gap: 0.85rem;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    @media (min-width: 640px) {
        .custom-pagination-wrapper {
            flex-direction: row;
        }
    }

    .pagination-info-text {
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
    }

    .pagination-info-text strong {
        color: #0f172a;
        font-weight: 700;
    }

    .pagination-controls {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.8rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        text-decoration: none;
        transition: all 0.15s ease;
        line-height: 1.25;
        user-select: none;
    }

    .pagination-btn:hover {
        color: #1677ff;
        background-color: #f8fafc;
        border-color: #bfdbfe;
        box-shadow: 0 2px 5px rgba(22, 119, 255, 0.1);
    }

    .pagination-btn.disabled,
    .pagination-btn:disabled {
        color: #cbd5e1;
        background-color: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
        box-shadow: none;
        pointer-events: none;
    }

    .pagination-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.7rem;
        font-size: 0.775rem;
        font-weight: 700;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        letter-spacing: 0.02em;
    }

    .pagination-svg-icon {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-black text-dark mb-1" style="letter-spacing: -0.02em; font-weight: 800; font-size: 1.75rem;">Data Master Guru</h2>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data guru pengajar dan wali kelas.</p>
        </div>
        @if(in_array(auth()->user()->role ?? '', ['admin_tu', 'admin', 'super_admin']) || (auth()->user() && auth()->user()->isTestingUser()))
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto flex-shrink-0">
                <!-- Tombol Export Guru -->
                <div class="dropdown w-full sm:w-auto">
                    <button class="btn btn-outline-primary rounded-3 px-3 py-2 fw-semibold shadow-sm dropdown-toggle w-full sm:w-auto" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0 rounded-3" style="z-index: 1050;">
                        <li>
                            <a href="{{ route('guru.export', ['format' => 'xlsx']) }}" class="dropdown-item py-2">
                                <i class="bi bi-file-earmark-excel me-2 text-success"></i> Export Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('guru.export', ['format' => 'csv']) }}" class="dropdown-item py-2">
                                <i class="bi bi-filetype-csv me-2 text-info"></i> Export CSV (.csv)
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Tombol Import Guru -->
                <button type="button" class="btn btn-outline-primary rounded-3 px-3 py-2 fw-semibold shadow-sm w-full sm:w-auto d-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    <span>Import Data</span>
                </button>

                @if(in_array(auth()->user()->sub_role, ['super_admin', 'petugas_tu']))
                    {{-- Tombol Tambah Guru (Hanya untuk sub_role: super_admin dan petugas_tu) --}}
                    <a href="{{ route('admin.guru.create') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold shadow-sm w-full sm:w-auto text-center"><i class="bi bi-plus-lg me-1"></i> Tambah Guru</a>
                @endif
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert"><i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert"><strong class="d-block mb-1">Gagal memproses data:</strong><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    @endif

    {{-- ====================================================== --}}
    {{-- FILTER BAR                                              --}}
    {{-- ====================================================== --}}
    <div class="filter-bar">
        {{-- Filter bekerja live via AJAX (debounce pada search, fetch partial hasil) --}}
        <form id="filterGuruForm" action="{{ route('guru.index') }}" method="GET">
            <div class="flex flex-col sm:flex-row gap-2">
                {{-- Input Cari Nama/NIP --}}
                <div class="w-full sm:flex-[5]">
                    <div class="search-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text"
                               id="searchGuru"
                               name="search"
                               class="form-control"
                               placeholder="Cari nama atau NIP guru..."
                               value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                </div>

                {{-- Dropdown Status --}}
                <div class="w-full sm:flex-[3]">
                    <select name="status" id="statusGuru" class="form-select">
                        <option value="Semua Status" {{ request('status') === 'Semua Status' || !request()->filled('status') ? 'selected' : '' }}>Semua Status</option>
                        <option value="Aktif" {{ request('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ request('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                {{-- Dropdown Penugasan Wali Kelas & Option Kelas --}}
                <div class="w-full sm:flex-[4]">
                    <select name="wali_kelas" id="waliKelasGuru" class="form-select">
                        <option value="Semua" {{ request('wali_kelas') === 'Semua' || !request()->filled('wali_kelas') ? 'selected' : '' }}>Semua Penugasan</option>
                        <option value="Ya" {{ request('wali_kelas') === 'Ya' ? 'selected' : '' }}>Wali Kelas</option>
                        <option value="Tidak" {{ request('wali_kelas') === 'Tidak' ? 'selected' : '' }}>Bukan Wali Kelas</option>
                        @if(isset($daftarKelas) && $daftarKelas->count() > 0)
                            <optgroup label="Filter Per Kelas Wali">
                                @foreach($daftarKelas as $kelas)
                                    <option value="kelas_{{ $kelas->id }}" {{ request('wali_kelas') === 'kelas_' . $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_lengkap }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- ====================================================== --}}
    {{-- HASIL FILTER (DI-UPDATE VIA AJAX)                       --}}
    {{-- Wrapper stabil utk delegasi event + overlay loading.    --}}
    {{-- ====================================================== --}}
    <div id="guruResultsWrapper" style="position: relative;">
        <div id="guruResults">
            @include('admin.guru._results')
        </div>

        {{-- Loading indicator (spinner tipis saat fetch berlangsung) --}}
        <div id="guruLoading" class="master-list-loading" style="display: none; position: absolute; inset: 0; z-index: 20; align-items: center; justify-content: center; background: rgba(255,255,255,0.65); border-radius: 16px;">
            <div class="d-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 shadow-sm">
                <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                <span class="small fw-semibold text-muted">Memuat data...</span>
            </div>
        </div>
    </div>
    {{-- ====================================================== --}}
    {{-- ZONA BERBAHAYA: RESET / HAPUS SEMUA DATA GURU           --}}
    {{-- Proteksi ketat: HANYA Super Admin                       --}}
    {{-- ====================================================== --}}
    @if(auth()->user()->sub_role === 'super_admin')
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="border: 1px solid #fee2e2 !important; background: #fff7f7;">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ef4444,#b91c1c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-trash3-fill text-white fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-danger mb-1">Zona Berbahaya — Reset / Hapus Semua Data Guru</h5>
                    <p class="text-muted mb-0 small" style="color: #991b1b !important;">
                        Hapus massal & permanen seluruh akun guru beserta jadwal pelajaran, jadwal piket, jurnal mengajar, izin guru, dan status kehadiran.
                    </p>
                </div>
            </div>
            <button type="button"
                    class="btn btn-danger rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 flex-shrink-0"
                    style="font-size:0.875rem;"
                    data-reset-route="{{ route('import.reset-guru') }}"
                    data-reset-label="seluruh data Guru"
                    data-reset-phrase="HAPUS DATA GURU"
                    data-bs-toggle="modal"
                    data-bs-target="#modalResetData">
                <i class="bi bi-trash3"></i>
                <span>Reset / Hapus Semua Data Guru</span>
            </button>
        </div>
    </div>
    @endif
</div>

{{-- ==================== MODAL IMPORT GURU ==================== --}}
<div class="modal fade" id="modalImportGuru" tabindex="-1" aria-labelledby="modalImportGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 38px; height: 38px;">
                        <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalImportGuruLabel">Import Master Guru</h5>
                        <p class="text-muted small mb-0">Format: NIP • NAMA GURU • STATUS (.xlsx / .xls / .csv)</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('import.guru') }}" method="POST" enctype="multipart/form-data" id="formImportGuru">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Form Upload --}}
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-dark">File Guru <span class="text-danger">*</span></label>
                                <input type="file" name="file_guru" class="form-control rounded-3" accept=".xlsx,.xls,.csv,.txt" required>
                                <div class="form-text text-muted small">Format didukung: .xlsx, .xls, .csv (Maks. 10 MB)</div>
                            </div>

                            <div class="alert alert-info border-0 rounded-3 small mb-0">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Akun guru baru dibuatkan otomatis dengan Username = <strong>NIP</strong> dan Password default = <strong>USERNAME123</strong>. NIP yang sudah ada akan diperbarui.
                            </div>
                        </div>

                        {{-- Panduan Format --}}
                        <div class="col-lg-5 border-start-lg">
                            <div class="bg-light p-3 rounded-3 border mb-3">
                                <div class="small fw-bold text-dark mb-2">
                                    <i class="bi bi-file-earmark-arrow-down text-primary me-1"></i> Template Master Guru
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('guru.export', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success rounded-2 fw-semibold flex-grow-1">
                                        <i class="bi bi-file-earmark-excel me-1"></i> .xlsx
                                    </a>
                                    <a href="{{ route('guru.export', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-info rounded-2 fw-semibold flex-grow-1">
                                        <i class="bi bi-filetype-csv me-1"></i> .csv
                                    </a>
                                </div>
                            </div>

                            <div class="small text-muted" style="font-size:0.8rem; line-height: 1.45;">
                                <div class="fw-bold text-dark mb-1">Panduan Kolom:</div>
                                <ul class="ps-3 mb-0">
                                    <li>Kolom: <strong>NO, NIP, NAMA GURU, STATUS</strong>.</li>
                                    <li>Opsional: <strong>PERAN, WALI KELAS, NO HP</strong>.</li>
                                    <li>Wali Kelas: isi <code>PERAN = "Wali Kelas"</code> & nama kelas di kolom <code>WALI KELAS</code>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                        <i class="bi bi-upload me-1"></i> Unggah & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.common._modal_reset')

@push('scripts')
<script>
    // ── Live AJAX Filter Data Master Guru ──────────────────────────────
    // Search & dropdown memicu fetch partial hasil tanpa me-refresh halaman.
    // Debounce 300ms pada input search. Partial DOM update + spinner loading.
    (function () {
        const wrapperEl = document.getElementById('guruResultsWrapper');
        const resultsEl = document.getElementById('guruResults');
        const loadingEl = document.getElementById('guruLoading');
        const form      = document.getElementById('filterGuruForm');
        if (!wrapperEl || !resultsEl || !form) return;

        const searchInput  = document.getElementById('searchGuru');
        const statusSelect = document.getElementById('statusGuru');
        const waliSelect   = document.getElementById('waliKelasGuru');
        const BASE_URL     = '{{ route("guru.index") }}';

        let currentPage = {{ (int) $dataGuru->currentPage() }};
        let requestSeq  = 0;

        function debounce(fn, ms) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), ms);
            };
        }

        function buildQuery() {
            const params = new URLSearchParams();
            const search = searchInput ? searchInput.value.trim() : '';
            if (search) params.set('search', search);
            if (statusSelect && statusSelect.value && statusSelect.value !== 'Semua Status') {
                params.set('status', statusSelect.value);
            }
            if (waliSelect && waliSelect.value && waliSelect.value !== 'Semua') {
                params.set('wali_kelas', waliSelect.value);
            }
            if (currentPage > 1) params.set('page', currentPage);
            return params;
        }

        function refresh() {
            const seq = ++requestSeq;
            loadingEl.style.display = 'flex';

            const params = buildQuery();
            const qs    = params.toString();
            const targetUrl = BASE_URL + (qs ? '?' + qs : '');

            // Sinkronkan URL tanpa me-refresh halaman (bisa di-share / di-bookmark)
            window.history.replaceState(null, '', targetUrl);

            fetch(targetUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(data => {
                if (seq !== requestSeq) return; // respon basi diabaikan
                resultsEl.innerHTML = data.html;
            })
            .catch(() => {
                if (seq !== requestSeq) return;
                // Fallback: muat ulang halaman agar data tetap tampil
                window.location.href = targetUrl;
            })
            .finally(() => {
                if (seq === requestSeq) loadingEl.style.display = 'none';
            });
        }

        // 1) Search input → debounce ±300ms
        if (searchInput) {
            searchInput.addEventListener('input', debounce(() => { currentPage = 1; refresh(); }, 300));
        }

        // 2) Dropdown berubah → filter langsung
        [statusSelect, waliSelect].forEach(sel => {
            if (sel) sel.addEventListener('change', () => { currentPage = 1; refresh(); });
        });

        // 3) Cegah submit GET biasa (Enter di input search)
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            currentPage = 1;
            refresh();
        });

        // 4) Pagination + Reset Filter tanpa reload (event delegation pada wrapper)
        wrapperEl.addEventListener('click', (e) => {
            const resetLink = e.target.closest('[data-reset-filter]');
            if (resetLink) {
                e.preventDefault();
                if (searchInput)  searchInput.value = '';
                if (statusSelect) statusSelect.value = 'Semua Status';
                if (waliSelect)   waliSelect.value = 'Semua';
                currentPage = 1;
                refresh();
                return;
            }

            const pageLink = e.target.closest('a.pagination-btn');
            if (!pageLink) return;
            e.preventDefault();
            const u  = new URL(pageLink.href);
            const p  = parseInt(u.searchParams.get('page') || '1', 10);
            if (!Number.isNaN(p) && p >= 1) {
                currentPage = p;
                refresh();
            }
        });
    })();

    // ── Script Modal Reset Data ──
    (function () {
        const modalEl  = document.getElementById('modalResetData');
        const form     = document.getElementById('formResetData');
        const input    = document.getElementById('resetConfirmInput');
        const btnReset = document.getElementById('btnConfirmReset');
        const lblText  = document.getElementById('resetLabelText');
        const phText   = document.getElementById('resetPhraseText');
        const titleText = document.getElementById('resetModalTitleText');

        if (!modalEl || !form || !input || !btnReset) return;

        let expectedPhrase = '';

        function lockReset() {
            input.value = '';
            btnReset.disabled = true;
        }

        document.querySelectorAll('[data-reset-route]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                expectedPhrase = (btn.dataset.resetPhrase || '').toUpperCase();
                form.setAttribute('action', btn.dataset.resetRoute);
                lblText.textContent = btn.dataset.resetLabel || 'seluruh data';
                phText.textContent  = expectedPhrase;
                if (titleText) titleText.textContent = btn.dataset.resetTitle || 'Konfirmasi Hapus Masal Data';
                lockReset();
            });
        });

        input.addEventListener('input', function () {
            btnReset.disabled = this.value.trim().toUpperCase() !== expectedPhrase;
        });

        modalEl.addEventListener('hidden.bs.modal', lockReset);
    })();
</script>
@endpush

@endsection
