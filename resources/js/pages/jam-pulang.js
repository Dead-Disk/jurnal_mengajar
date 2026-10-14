export function initJamPulangAutoSave() {
    if (window.__jamPulangAutoSaveBound) return;
    window.__jamPulangAutoSaveBound = true;

    const pendingSaves = new Map();

    function updateBadge(select) {
        const badge = Array.from(document.querySelectorAll('.jam-pulang-badge')).find((item) =>
            item.dataset.shift === select.dataset.shiftId
            && item.dataset.kategori === select.dataset.kategoriHari
            && item.dataset.tingkat === select.dataset.tingkat
        );
        if (!badge) return;

        badge.innerHTML = select.value === 'pkl'
            ? '<span class="badge text-bg-warning rounded-pill">PKL / DU-DI</span>'
            : (select.value
                ? '<span class="badge text-bg-danger rounded-pill">Batas: Jam ' + select.value + '</span>'
                : '<span class="badge text-bg-info rounded-pill">Semua Slot</span>');
    }

    function showSaveFeedback(message, type) {
        const container = document.getElementById('toastJamPelajaranContainer');
        if (!container || typeof bootstrap === 'undefined') {
            if (type === 'danger') window.alert(message);
            return;
        }

        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-' + (type === 'danger' ? 'danger' : 'success') + ' border-0 shadow rounded-3';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        const body = document.createElement('div');
        body.className = 'toast-body fw-semibold';
        body.textContent = message;
        toast.appendChild(body);
        container.appendChild(toast);
        toast.addEventListener('hidden.bs.toast', () => toast.remove(), { once: true });
        new bootstrap.Toast(toast, { delay: 5000 }).show();
    }

    async function saveSelection(select, value, previousValue) {
        const form = select.closest('#formJamPulang');
        const indicator = form?.querySelector('#jpSaveIndicator');

        try {
            const endpoint = form?.action;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!endpoint || !csrfToken) {
                throw new Error('Form atau token keamanan untuk menyimpan pengaturan jam pulang tidak ditemukan.');
            }

            if (indicator) {
                indicator.classList.remove('d-none');
                indicator.classList.add('d-inline-flex');
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    shift_id: Number(select.dataset.shiftId || 0),
                    kategori_hari: select.dataset.kategoriHari,
                    tingkat: select.dataset.tingkat,
                    max_jam_ke: value,
                }),
            });
            const data = await response.json();

            if (!response.ok || data.success !== true) {
                throw new Error(data.message || 'Gagal menyimpan pengaturan jam pulang.');
            }

            if (select.value === value) {
                select.dataset.initial = value;
                showSaveFeedback(data.message, 'success');
            }
        } catch (error) {
            if (select.value === value) {
                select.value = previousValue;
                updateBadge(select);
            }
            showSaveFeedback(error.message || 'Pengaturan jam pulang gagal disimpan.', 'danger');
        } finally {
            if (indicator) {
                indicator.classList.add('d-none');
                indicator.classList.remove('d-inline-flex');
            }
        }
    }

    document.addEventListener('change', function (event) {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        const select = target.closest('.jam-pulang-select');
        if (!select) return;

        updateBadge(select);
        const value = select.value;
        const previousValue = select.dataset.initial ?? '';
        const key = [select.dataset.shiftId, select.dataset.kategoriHari, select.dataset.tingkat].join('|');
        const previousSave = pendingSaves.get(key) || Promise.resolve();
        const nextSave = previousSave
            .catch((error) => {
                showSaveFeedback(error.message || 'Pengaturan jam pulang gagal disimpan.', 'danger');
            })
            .then(() => saveSelection(select, value, previousValue));
        pendingSaves.set(key, nextSave);
        const clearPendingSave = () => {
            if (pendingSaves.get(key) === nextSave) pendingSaves.delete(key);
        };
        nextSave.then(clearPendingSave, clearPendingSave);
    });
}
