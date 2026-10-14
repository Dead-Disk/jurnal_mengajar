<?php

namespace App\Models;

use App\Models\Concerns\HasTestingData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaRutin extends Model
{
    use HasFactory, HasTestingData;

    protected $table = 'agenda_rutin';

    protected $fillable = [
        'hari',
        'jam_ke',
        'nama_agenda',
        'is_active',
        'shift_id',
        'tahun_ajaran_id',
        'is_testing_data',
    ];

    protected $casts = [
        'jam_ke' => 'integer',
        'is_active' => 'boolean',
        'shift_id' => 'integer',
        'tahun_ajaran_id' => 'integer',
        'is_testing_data' => 'boolean',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id', 'id');
    }

    /**
     * Scope: konfigurasi agenda milik satu konteks shift.
     *
     * shift_id = 0 berarti Global (mode penjadwalan global / kelas tanpa shift).
     * null diperlakukan sama dengan 0 untuk nilai yang belum pernah di-set.
     */
    public function scopeOfShift($query, ?int $shiftId)
    {
        return $query->whereRaw('COALESCE(shift_id, 0) = ?', [(int) ($shiftId ?: 0)]);
    }

    public function scopeOfTahunAjaran($query, ?int $tahunAjaranId)
    {
        return $tahunAjaranId === null
            ? $query->whereNull('tahun_ajaran_id')
            : $query->where('tahun_ajaran_id', $tahunAjaranId);
    }
}