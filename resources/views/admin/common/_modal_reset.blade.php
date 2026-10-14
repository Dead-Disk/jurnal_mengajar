{{-- Modal Konfirmasi Reset / Hapus Masal Data --}}
<div class="modal fade" id="modalResetData" tabindex="-1" aria-labelledby="modalResetDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" id="formResetData" action="" class="modal-content border-0 shadow rounded-4 overflow-hidden">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" id="resetTahunAjaranId">
            <div class="modal-header" style="background:#fef2f2;border-bottom:1px solid #fee2e2;">
                <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="modalResetDataLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i><span id="resetModalTitleText">Konfirmasi Hapus Masal Data</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger border-0 rounded-3 d-flex align-items-start gap-2 mb-3" style="background:#fef2f2;color:#7f1d1d;">
                    <i class="bi bi-exclamation-octagon-fill fs-5 mt-1"></i>
                    <div>
                        Anda akan menghapus <strong id="resetLabelText">seluruh data</strong> beserta seluruh data terkait secara
                        <strong>permanen</strong>. Tindakan ini <strong class="text-danger">tidak dapat dibatalkan</strong>.
                    </div>
                </div>
                <label for="resetConfirmInput" class="form-label fw-semibold" style="font-size:0.875rem;color:#374151;">
                    Ketik frasa konfirmasi <code id="resetPhraseText" class="text-danger fw-bold px-2 py-1" style="background:#fee2e2;border-radius:6px;">HAPUS DATA</code>
                    pada kolom di bawah untuk mengaktifkan tombol hapus:
                </label>
                <input type="text"
                       class="form-control form-control-lg @error('reset_confirm') is-invalid @enderror"
                       id="resetConfirmInput"
                       name="reset_confirm"
                       placeholder="Ketik frasa konfirmasi di sini…"
                       autocomplete="off"
                       autofocus
                       style="border-radius:10px;border:1px solid #fecaca;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"
                       required>
                @error('reset_confirm')
                    <div class="text-danger mt-2" style="font-size:0.8rem;">{{ $message }}</div>
                @enderror
            </div>
            <div class="modal-footer border-top bg-light px-4 py-3">
                <button type="button" class="btn btn-light border rounded-3 px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="btnConfirmReset" class="btn btn-danger rounded-3 px-4 fw-semibold d-inline-flex align-items-center gap-2" disabled>
                    <i class="bi bi-trash3-fill"></i> Ya, Hapus Semua Data
                </button>
            </div>
        </form>
    </div>
</div>
