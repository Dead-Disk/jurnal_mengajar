<?php

namespace App\Models;

use App\Models\Concerns\HasTestingData;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventKhusus extends Model
{
    use HasTestingData, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'nama_event',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe_dampak',
        'scope',
        'target_kelas',
        'keterangan',
        'is_testing_data',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'target_kelas' => 'array',
        'is_testing_data' => 'boolean',
    ];

    /**
     * Kelas yang tercakup event Non-Aktif KBM pada tanggal tertentu.
     *
     * @param Collection<int, int|string|null> $classIds
     * @return Collection<int, int|string>
     */
    public static function nonAktifClassIdsOnDate(string $tanggal, Collection $classIds): Collection
    {
        $classIds = $classIds->filter()->unique()->values();
        if ($classIds->isEmpty()) {
            return collect();
        }

        $events = static::query()
            ->where('tipe_dampak', 'non_aktif')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->get(['scope', 'target_kelas']);

        $candidateIds = $classIds->map(fn ($id) => (string) $id);
        if ($events->contains(fn (self $event) => $event->scope === 'semua_kelas')) {
            return $classIds;
        }

        $targetIds = $events
            ->where('scope', 'kelas_tertentu')
            ->flatMap(fn (self $event) => $event->target_kelas ?? [])
            ->map(fn ($id) => (string) $id)
            ->unique();

        $blockedIds = $candidateIds->intersect($targetIds)->flip();

        return $classIds->filter(fn ($id) => $blockedIds->has((string) $id))->values();
    }
}
