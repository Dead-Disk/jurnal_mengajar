<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class JurusanImportTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdminTU(): User
    {
        return User::create([
            'nama' => 'Petugas TU Test',
            'username' => 'tu_'.Str::random(6),
            'password' => bcrypt('password'),
            'role' => 'admin',
            'sub_role' => 'petugas_tu',
            'is_active' => true,
        ]);
    }

    public function test_halaman_jurusan_menampilkan_tombol_dan_modal_import(): void
    {
        $admin = $this->makeAdminTU();

        $response = $this->actingAs($admin)->get(route('jurusan.index'));

        $response->assertOk();
        $response->assertSee('Import Data');
        $response->assertSee('id="modalImportJurusan"', false);
        $response->assertSee(route('jurusan.import'), false);
        $response->assertSee(route('jurusan.template', ['format' => 'xlsx']), false);
    }

    public function test_import_jurusan_berhasil_membuat_data_baru(): void
    {
        $admin = $this->makeAdminTU();

        $csvContent = "NO,KODE JURUSAN,NAMA JURUSAN\n"
            ."1,RPL,Rekayasa Perangkat Lunak\n"
            ."2,TKJ,Teknik Komputer dan Jaringan\n"
            ."3,AKL,Akuntansi dan Keuangan Lembaga\n";

        $file = UploadedFile::fake()->createWithContent('data_jurusan.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('jurusan.import'), [
            'file_jurusan' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('jurusan', [
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
        ]);
        $this->assertDatabaseHas('jurusan', [
            'kode_jurusan' => 'TKJ',
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
        ]);
        $this->assertDatabaseHas('jurusan', [
            'kode_jurusan' => 'AKL',
            'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',
        ]);
    }

    public function test_import_jurusan_memperbarui_data_yang_sudah_ada(): void
    {
        $admin = $this->makeAdminTU();

        Jurusan::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Nama Lama RPL',
        ]);

        $csvContent = "NO,KODE JURUSAN,NAMA JURUSAN\n"
            ."1,RPL,Rekayasa Perangkat Lunak Baru\n";

        $file = UploadedFile::fake()->createWithContent('data_jurusan_update.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('jurusan.import'), [
            'file_jurusan' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('jurusan', [
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak Baru',
        ]);
    }

    public function test_download_template_jurusan_berhasil(): void
    {
        $admin = $this->makeAdminTU();

        $response = $this->actingAs($admin)->get(route('jurusan.template', ['format' => 'xlsx']));
        $response->assertOk();
    }
}
