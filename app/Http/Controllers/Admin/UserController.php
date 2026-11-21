<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->trim()->toString();

        $users = User::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, [User::ROLE_ADMIN, User::ROLE_USER], true), function (Builder $query) use ($role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('pages.admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->forceFill([
                'role' => $data['role'],
                'email_verified_at' => now(),
            ])->save();

            return $user;
        });

        return redirect()
            ->route('admin.users.index')
            ->with('popup', [
                'type' => 'success',
                'title' => 'User berhasil ditambahkan',
                'message' => 'Akun '.$user->name.' berhasil dibuat.',
            ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('pages.admin.users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->is($request->user()) && $data['role'] !== User::ROLE_ADMIN) {
            return redirect()
                ->back()
                ->withErrors(['role' => 'Anda tidak dapat menurunkan role akun sendiri.'])
                ->withInput();
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->role = $data['role'];
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('popup', [
                'type' => 'success',
                'title' => 'User berhasil diperbarui',
                'message' => 'Perubahan pada akun '.$user->name.' telah disimpan.',
            ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return redirect()
                ->route('admin.users.index')
                ->with('popup', [
                    'type' => 'error',
                    'title' => 'Tidak dapat menghapus akun',
                    'message' => 'Anda tidak dapat menghapus akun sendiri.',
                ]);
        }

        $name = $user->name;

        try {
            $user->delete();
        } catch (QueryException $exception) {
            if (! $this->isForeignKeyConstraintViolation($exception)) {
                throw $exception;
            }

            return redirect()
                ->route('admin.users.index')
                ->with('popup', [
                    'type' => 'error',
                    'title' => 'User tidak dapat dihapus',
                    'message' => 'Akun '.$name.' masih terhubung dengan data transaksi. Hapus atau pindahkan data terkait terlebih dahulu.',
                ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('popup', [
                'type' => 'success',
                'title' => 'User berhasil dihapus',
                'message' => 'Akun '.$name.' telah dihapus.',
            ]);
    }

    /**
     * Determine whether the exception was caused by a foreign key constraint.
     */
    private function isForeignKeyConstraintViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23000'
            && str_contains(strtolower($exception->getMessage()), 'foreign key');
    }
}
