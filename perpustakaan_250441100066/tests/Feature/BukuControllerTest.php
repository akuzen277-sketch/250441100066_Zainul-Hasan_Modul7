<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BukuControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_details_render_for_users_with_view_permission(): void
    {
        $buku = Buku::factory()->create([
            'judul' => 'Belajar Laravel',
            'penulis' => 'Ayu Pratiwi',
            'tahun_terbit' => 2024,
        ]);

        $this->actingAs($this->userWithBookViewPermission())
            ->get(route('buku.show', $buku))
            ->assertOk()
            ->assertViewIs('buku.show')
            ->assertSeeText('Belajar Laravel')
            ->assertSeeText('Ayu Pratiwi')
            ->assertSeeText('2024')
            ->assertSeeText($buku->kategori->nama);
    }

    public function test_book_details_return_not_found_for_a_missing_book(): void
    {
        $this->actingAs($this->userWithBookViewPermission())
            ->get(route('buku.show', ['buku' => 999999]))
            ->assertNotFound();
    }

    public function test_book_details_are_forbidden_without_view_permission(): void
    {
        $buku = Buku::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('buku.show', $buku))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login_for_book_details(): void
    {
        $buku = Buku::factory()->create();

        $this->get(route('buku.show', $buku))
            ->assertRedirect(route('login'));
    }

    private function userWithBookViewPermission(): User
    {
        $role = Role::firstOrCreate([
            'name' => 'siswa',
            'guard_name' => 'web',
        ]);
        $permission = Permission::firstOrCreate([
            'name' => 'view buku',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);

        return User::factory()->create()->assignRole($role);
    }
}
