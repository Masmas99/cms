@php
    $editingUser = $user ?? null;
    $currentRole = old('role', $editingUser?->role ?? \App\Models\User::ROLE_USER);
    $isEditingSelf = $editingUser?->is(Auth::user()) ?? false;
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nama" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-2 block w-full !border-blue-200 focus:!border-blue-500 focus:!ring-blue-500/30"
            :value="old('name', $editingUser?->name)"
            required
            autofocus
            autocomplete="name"
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input
            id="email"
            name="email"
            type="email"
            class="mt-2 block w-full !border-blue-200 focus:!border-blue-500 focus:!ring-blue-500/30"
            :value="old('email', $editingUser?->email)"
            required
            autocomplete="email"
        />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="role" value="Role" />

        @if ($isEditingSelf)
            <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_ADMIN }}">
        @endif

        <select
            id="role"
            name="role"
            @disabled($isEditingSelf)
            class="mt-2 block w-full rounded-xl border-blue-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
        >
            <option value="{{ \App\Models\User::ROLE_USER }}" @selected($currentRole === \App\Models\User::ROLE_USER)>User</option>
            <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected($currentRole === \App\Models\User::ROLE_ADMIN)>Admin</option>
        </select>
        <p class="mt-2 text-xs text-slate-500">
            @if ($isEditingSelf)
                Role akun sendiri tidak dapat diturunkan.
            @else
                Admin dapat mengakses dashboard admin dan manajemen user.
            @endif
        </p>
        <x-input-error class="mt-2" :messages="$errors->get('role')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="password" :value="$editingUser ? 'Password baru' : 'Password'" />
            <x-password-input
                id="password"
                name="password"
                class="mt-2 !border-blue-200 focus:!border-blue-500 focus:!ring-blue-500/30"
                autocomplete="new-password"
                :placeholder="$editingUser ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
                :required="! $editingUser"
            />
            @if ($editingUser)
                <p class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi password" />
            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                class="mt-2 !border-blue-200 focus:!border-blue-500 focus:!ring-blue-500/30"
                autocomplete="new-password"
                :required="! $editingUser"
            />
        </div>
    </div>
</div>
