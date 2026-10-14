<?php

namespace Tests\Feature;

use App\Models\EventKhusus;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventKhususTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Event Test',
            'username' => 'admin-event-test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_index_lists_events_and_database_classes_grouped_by_grade(): void
    {
        $kelas = Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 1']);
        Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 2']);
        $event = EventKhusus::create($this->eventPayload([
            'target_kelas' => [$kelas->id],
            'scope' => 'kelas_tertentu',
        ]));

        $this->actingAs($this->admin)
            ->get(route('admin.event-khusus.index'))
            ->assertOk()
            ->assertSee($event->nama_event)
            ->assertSee('XI RPL 1')
            ->assertSee('value="'.$kelas->id.'"', false)
            ->assertSee('<option value="daring"', false)
            ->assertSee('<option value="non_aktif"', false)
            ->assertDontSee('<option value="penyesuaian_jam"', false)
            ->assertDontSee('data-name="PTS Semester Ganjil"', false);
    }

    public function test_index_formats_fully_selected_grades_as_a_group(): void
    {
        $kelasX = [
            Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 1']),
            Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 2']),
        ];
        $kelasXI = [
            Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 1']),
            Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 2']),
        ];
        EventKhusus::create($this->eventPayload([
            'scope' => 'kelas_tertentu',
            'target_kelas' => collect($kelasX)->merge($kelasXI)->pluck('id')->all(),
        ]));

        $this->actingAs($this->admin)
            ->get(route('admin.event-khusus.index'))
            ->assertOk()
            ->assertSee('Semua Kelas X &amp; XI', false);
    }

    public function test_index_limits_partial_class_names_to_three_and_shows_remaining_count(): void
    {
        $kelas = collect([
            Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 1']),
            Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 2']),
            Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 3']),
            Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 4']),
            Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 1']),
            Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 2']),
            Kelas::create(['tingkat' => 'XI', 'nama_kelas' => 'RPL 3']),
        ]);
        EventKhusus::create($this->eventPayload([
            'scope' => 'kelas_tertentu',
            'target_kelas' => $kelas->only([0, 1, 2, 4, 5])->pluck('id')->all(),
        ]));

        $response = $this->actingAs($this->admin)
            ->get(route('admin.event-khusus.index'))
            ->assertOk();

        $this->assertSame(1, preg_match('/<tr data-event-row\b[^>]*>.*?<\/tr>/s', $response->getContent(), $matches));
        $this->assertStringContainsString('X AK 1, X AK 2, X AK 3', $matches[0]);
        $this->assertStringContainsString('+2 kelas lagi', $matches[0]);
        $this->assertStringNotContainsString('X AK 4', $matches[0]);
        $this->assertStringNotContainsString('XI RPL 2', $matches[0]);
    }

    public function test_store_persists_all_class_event_with_null_target_classes(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.event-khusus.store'), $this->eventPayload())
            ->assertRedirect(route('admin.event-khusus.index'))
            ->assertSessionHas('success');

        $event = EventKhusus::query()->firstOrFail();
        $this->assertNull($event->target_kelas);
        $this->assertSame('semua_kelas', $event->scope);
        $this->assertSame('non_aktif', $event->tipe_dampak);
    }

    public function test_store_persists_selected_class_ids_as_json_array(): void
    {
        $kelas = Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 1']);

        $this->actingAs($this->admin)
            ->post(route('admin.event-khusus.store'), $this->eventPayload([
                'scope' => 'kelas_tertentu',
                'target_kelas' => [(string) $kelas->id],
                'tipe_dampak' => 'daring',
            ]))
            ->assertRedirect(route('admin.event-khusus.index'));

        $event = EventKhusus::query()->firstOrFail();
        $this->assertSame([$kelas->id], $event->target_kelas);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'target_kelas' => json_encode([$kelas->id]),
        ]);
    }

    public function test_store_and_update_reject_removed_impact_type(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.event-khusus.index'))
            ->post(route('admin.event-khusus.store'), $this->eventPayload([
                'tipe_dampak' => 'penyesuaian_jam',
            ]))
            ->assertSessionHasErrors('tipe_dampak');

        $event = EventKhusus::create($this->eventPayload());

        $this->actingAs($this->admin)
            ->from(route('admin.event-khusus.index'))
            ->put(route('admin.event-khusus.update', $event), $this->eventPayload([
                'tipe_dampak' => 'penyesuaian_jam',
            ]))
            ->assertSessionHasErrors('tipe_dampak');

        $this->assertSame('non_aktif', $event->fresh()->tipe_dampak);
    }

    public function test_store_requires_at_least_one_valid_class_for_specific_scope(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.event-khusus.index'))
            ->post(route('admin.event-khusus.store'), $this->eventPayload([
                'scope' => 'kelas_tertentu',
            ]))
            ->assertSessionHasErrors('target_kelas');

        $this->actingAs($this->admin)
            ->from(route('admin.event-khusus.index'))
            ->post(route('admin.event-khusus.store'), $this->eventPayload([
                'scope' => 'kelas_tertentu',
                'target_kelas' => [999999],
            ]))
            ->assertSessionHasErrors('target_kelas');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_update_changes_event_and_selected_classes(): void
    {
        $kelasAwal = Kelas::create(['tingkat' => 'X', 'nama_kelas' => 'AK 1']);
        $kelasBaru = Kelas::create(['tingkat' => 'XII', 'nama_kelas' => 'RPL 2']);
        $event = EventKhusus::create($this->eventPayload([
            'scope' => 'kelas_tertentu',
            'target_kelas' => [$kelasAwal->id],
        ]));

        $this->actingAs($this->admin)
            ->put(route('admin.event-khusus.update', $event), $this->eventPayload([
                'nama_event' => 'Event Diperbarui',
                'scope' => 'kelas_tertentu',
                'target_kelas' => [(string) $kelasBaru->id],
                'tipe_dampak' => 'daring',
            ]))
            ->assertRedirect(route('admin.event-khusus.index'));

        $event->refresh();
        $this->assertSame('Event Diperbarui', $event->nama_event);
        $this->assertSame('daring', $event->tipe_dampak);
        $this->assertSame([$kelasBaru->id], $event->target_kelas);
    }

    public function test_destroy_soft_deletes_event(): void
    {
        $event = EventKhusus::create($this->eventPayload());

        $this->actingAs($this->admin)
            ->delete(route('admin.event-khusus.destroy', $event))
            ->assertRedirect(route('admin.event-khusus.index'));

        $this->assertSoftDeleted('events', ['id' => $event->id]);
        $this->assertDatabaseCount('events', 1);
        $this->assertSame(0, EventKhusus::query()->count());
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'nama_event' => 'Agenda Pengujian',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-02',
            'tipe_dampak' => 'non_aktif',
            'scope' => 'semua_kelas',
            'target_kelas' => [],
            'keterangan' => 'Catatan event pengujian',
        ], $overrides);
    }
}
