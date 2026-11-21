<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_view_the_user_management_page(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Manajemen User')
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.com')
            ->assertSee(route('admin.users.create'), false)
            ->assertSee('Edit Budi Santoso', false)
            ->assertSee('confirmDelete', false);
    }

    public function test_user_management_uses_the_application_pagination_style(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        User::factory()->count(15)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Showing', false)
            ->assertSee('results', false)
            ->assertSee('bg-brand-600', false);
    }

    public function test_user_management_shows_uploaded_profile_photos(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        User::factory()->create([
            'name' => 'Budi Santoso',
            'avatar' => 'avatars/budi.jpg',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('storage/avatars/budi.jpg', false)
            ->assertSee('alt="Budi Santoso"', false);
    }

    public function test_regular_users_cannot_access_user_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login_from_user_management(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admins_can_view_the_create_and_edit_user_forms(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Tambah User');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('Edit User')
            ->assertSee('Budi Santoso');
    }

    public function test_admins_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'role' => User::ROLE_USER,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('popup');

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_admins_can_create_another_admin(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Admin Baru',
                'email' => 'admin.baru@example.com',
                'role' => User::ROLE_ADMIN,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin.baru@example.com',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_user_creation_validates_input(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);

        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'existing@example.com',
                'role' => 'superadmin',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_admins_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'role' => User::ROLE_ADMIN,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('popup');

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_admins_can_change_a_user_password(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_admins_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $admin))
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_USER,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.edit', $admin))
            ->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admins_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('popup');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admins_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('popup');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_management_uses_a_custom_delete_confirmation_dialog(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Hapus user?')
            ->assertSee('Tindakan ini tidak dapat dibatalkan.')
            ->assertDontSee("return confirm('Yakin ingin menghapus user ini?')", false);
    }

    public function test_user_management_success_message_is_displayed_as_a_top_right_toast(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Current']);
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User berhasil dihapus')
            ->assertSee('Akun Budi Santoso telah dihapus.')
            ->assertSee('right-4 top-20', false);
    }

    public function test_admins_can_search_users(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Current',
            'email' => 'current.admin@example.com',
        ]);
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
        User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'budi@example.com']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah');
    }

    public function test_admins_can_filter_users_by_role(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Current',
            'email' => 'current.admin@example.com',
        ]);
        User::factory()->admin()->create([
            'name' => 'Admin Lain',
            'email' => 'other.admin@example.com',
        ]);
        User::factory()->create([
            'name' => 'User Biasa',
            'email' => 'regular.user@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => User::ROLE_USER]))
            ->assertOk()
            ->assertSee('regular.user@example.com')
            ->assertDontSee('other.admin@example.com');
    }
}
