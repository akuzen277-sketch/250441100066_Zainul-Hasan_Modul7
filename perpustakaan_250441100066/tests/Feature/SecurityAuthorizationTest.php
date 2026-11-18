<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_search_binds_the_title_query(): void
    {
        $student = $this->userWithRole('siswa');

        $this->actingAs($student)
            ->getJson('/buku-cari?judul=%27%20OR%20%271%27=%271')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_guest_is_redirected_to_login_for_loan_details(): void
    {
        $this->get('/peminjaman/1')
            ->assertRedirect(route('login'));
    }

    public function test_book_list_escapes_titles_with_blade(): void
    {
        $student = $this->userWithRole('siswa', 'view buku');
        Buku::factory()->create([
            'judul' => '<script>alert(1)</script>',
        ]);

        $response = $this->actingAs($student)->get('/buku');

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_only_admin_and_petugas_can_open_book_creation(): void
    {
        $student = $this->userWithRole('siswa', 'view buku');

        $this->actingAs($student)
            ->get('/buku/create')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/buku')
            ->assertOk()
            ->assertDontSee('id="buku-form"', false)
            ->assertDontSee('buku-delete-form-template', false);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/buku/create')
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method"', false)
            ->assertSee('value="DELETE"', false);

        $this->actingAs($this->userWithRole('petugas'))
            ->get('/buku/create')
            ->assertOk();
    }

    public function test_loan_details_are_limited_to_the_owner_and_staff(): void
    {
        $owner = $this->userWithRole('siswa');
        $otherStudent = $this->userWithRole('siswa');
        $staff = $this->userWithRole('petugas');
        $admin = $this->userWithRole('admin');
        $member = Anggota::factory()->create(['user_id' => $owner->id]);
        $loan = Peminjaman::factory()->create(['anggota_id' => $member->id]);

        $this->actingAs($owner)
            ->get(route('peminjaman.show', $loan))
            ->assertOk();

        $this->actingAs($otherStudent)
            ->get(route('peminjaman.show', $loan))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('peminjaman.show', $loan))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('peminjaman.show', $loan))
            ->assertOk();
    }

    public function test_legacy_get_delete_url_is_not_registered(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/buku/1/hapus')
            ->assertNotFound();
    }

    public function test_admin_seeder_replaces_the_default_password(): void
    {
        $initialPassword = 'Library-Admin-2026-Secure!';
        config()->set('app.admin_initial_password', $initialPassword);

        $this->seed([
            RolePermissionSeeder::class,
            UserRoleSeeder::class,
        ]);

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->assertTrue(Hash::check($initialPassword, $admin->password));
        $this->assertFalse(Hash::check('password123', $admin->password));
    }

    private function userWithRole(string $roleName, ?string $permissionName = null): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        if ($permissionName !== null) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
            $role->givePermissionTo($permission);
        }

        return User::factory()->create()->assignRole($role);
    }
}
