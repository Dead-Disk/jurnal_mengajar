<?php

namespace App\Http\Controllers;

use App\Models\EventKhusus;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventKhususController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdminArea();

        $events = EventKhusus::query()
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $kelasByTingkat = Kelas::query()
            ->orderBy('nama_kelas')
            ->get()
            ->groupBy(fn (Kelas $kelas) => strtoupper(trim((string) preg_replace(
                '/^kelas\s+/i',
                '',
                $kelas->tingkat
            ))))
            ->sortBy(fn ($kelas, string $tingkat) => match ($tingkat) {
                'X' => 0,
                'XI' => 1,
                'XII' => 2,
                default => 3,
            });

        return view('admin.event-khusus.index', compact('events', 'kelasByTingkat'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdminArea();

        $validated = $this->validateEvent($request);
        $validated['target_kelas'] = $this->validatedTargetClasses($validated);

        EventKhusus::create($validated);

        return redirect()
            ->route('admin.event-khusus.index')
            ->with('success', 'Event khusus berhasil ditambahkan.');
    }

    public function update(Request $request, EventKhusus $eventKhusus): RedirectResponse
    {
        $this->authorizeAdminArea();
        $this->authorizeTestingMutation($eventKhusus);

        $validated = $this->validateEvent($request);
        $validated['target_kelas'] = $this->validatedTargetClasses($validated);
        $eventKhusus->update($validated);

        return redirect()
            ->route('admin.event-khusus.index')
            ->with('success', 'Event khusus berhasil diperbarui.');
    }

    public function destroy(EventKhusus $eventKhusus): RedirectResponse
    {
        $this->authorizeAdminArea();
        $this->authorizeTestingMutation($eventKhusus);

        $eventKhusus->delete();

        return redirect()
            ->route('admin.event-khusus.index')
            ->with('success', 'Event khusus berhasil dihapus.');
    }

    private function authorizeAdminArea(): void
    {
        abort_unless($this->isAuthorizedAdminArea(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'tipe_dampak' => ['required', Rule::in(['non_aktif', 'daring'])],
            'scope' => ['required', Rule::in(['semua_kelas', 'kelas_tertentu'])],
            'target_kelas' => ['exclude_unless:scope,kelas_tertentu', 'required', 'array', 'min:1'],
            'target_kelas.*' => ['required', 'integer', 'distinct'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ], [
            'nama_event.required' => 'Nama kegiatan wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'tipe_dampak.required' => 'Tipe dampak KBM wajib dipilih.',
            'scope.required' => 'Scope kelas wajib dipilih.',
            'target_kelas.required' => 'Pilih minimal satu rombel untuk scope kelas tertentu.',
            'target_kelas.min' => 'Pilih minimal satu rombel untuk scope kelas tertentu.',
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<int>|null
     */
    private function validatedTargetClasses(array $validated): ?array
    {
        if ($validated['scope'] === 'semua_kelas') {
            return null;
        }

        $classIds = array_map('intval', $validated['target_kelas']);
        $availableCount = Kelas::query()->whereIn('id', $classIds)->count();

        if ($availableCount !== count($classIds)) {
            throw ValidationException::withMessages([
                'target_kelas' => 'Satu atau beberapa rombel tidak tersedia untuk akun ini.',
            ]);
        }

        return $classIds;
    }
}
