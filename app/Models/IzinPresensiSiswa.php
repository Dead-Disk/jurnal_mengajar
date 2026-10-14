<?php

namespace App\Models;

use App\Models\Concerns\HasTestingData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinPresensiSiswa extends Model
{
    use HasTestingData;

    protected $table = 'izin_presensi_siswa';

    protected $fillable = [
        'id_siswa',
        'id_kelas',
        'id_guru_piket',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'foto_surat',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_testing_data' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
