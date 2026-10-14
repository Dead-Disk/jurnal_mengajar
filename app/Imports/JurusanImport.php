<?php

namespace App\Imports;

use App\Imports\Concerns\TargetsImportPartition;
use App\Models\Jurusan;
use App\Models\Scopes\TestingDataScope;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JurusanImport implements ToModel, WithHeadingRow
{
    use TargetsImportPartition;

    public int $importedCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public array $rowErrors = [];

    public function model(array $row): Model|array|null
    {
        // Abaikan baris kosong
        $rawValues = array_filter(array_values($row), fn ($val) => trim((string) $val) !== '');
        if (empty($rawValues)) {
            return null;
        }

        // Kolom B → kode jurusan, Kolom C → nama jurusan.
        // Mendukung slug dari header "KODE JURUSAN", "KODE", atau posisi index 1 (B)
        $kode = strtoupper(trim((string) ($row['kode_jurusan'] ?? $row['kode'] ?? '')));
        if ($kode === '') {
            $kode = strtoupper($this->cellAt($row, 1));
        }

        // Mendukung slug dari header "NAMA JURUSAN", "NAMA", "JURUSAN", atau posisi index 2 (C)
        $nama = trim((string) ($row['nama_jurusan'] ?? $row['nama'] ?? $row['jurusan'] ?? ''));
        if ($nama === '') {
            $nama = $this->cellAt($row, 2);
        }

        // Fallback jika file tanpa kolom NO (Kolom A = KODE, Kolom B = NAMA)
        if ($kode === '' && $nama === '') {
            $kode = strtoupper($this->cellAt($row, 0));
            $nama = $this->cellAt($row, 1);
        }

        if ($kode === '' || $nama === '') {
            $this->skippedCount++;
            $this->rowErrors[] = "Baris dengan kode '{$kode}' dilewati — kode / nama jurusan kosong.";

            return null;
        }

        // Kode jurusan bersifat unik (updateOrCreate)
        $existing = Jurusan::withTrashed()
            ->withoutGlobalScope(TestingDataScope::class)
            ->where('kode_jurusan', $kode)
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->forceFill([
                'nama_jurusan' => $nama,
                'is_testing_data' => $this->targetIsTestingData() ? 1 : 0,
            ])->save();
            $this->updatedCount++;

            return null;
        }

        $jurusan = new Jurusan;
        $jurusan->kode_jurusan = $kode;
        $jurusan->nama_jurusan = $nama;
        $jurusan->is_testing_data = $this->targetIsTestingData() ? 1 : 0;
        $jurusan->save();

        $this->importedCount++;

        return $jurusan;
    }
}
