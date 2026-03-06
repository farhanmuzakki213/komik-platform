<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<div class="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6 bg-white dark:bg-white/[0.03]">
    <div class="mb-6">
        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Update Password
        </h4>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Pastikan akun Anda menggunakan kata sandi acak yang panjang agar tetap aman.
        </p>
    </div>

    <form wire:submit="updatePassword" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 lg:gap-7">

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Current Password</label>
                <input wire:model="current_password" type="password" autocomplete="current-password" class="h-11 w-full lg:w-1/2 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2 text-sm text-red-500" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">New Password</label>
                <input wire:model="password" type="password" autocomplete="new-password" class="h-11 w-full lg:w-1/2 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Confirm Password</label>
                <input wire:model="password_confirmation" type="password" autocomplete="new-password" class="h-11 w-full lg:w-1/2 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-500" />
            </div>

        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                Save Password
            </button>

            <x-action-message class="me-3 text-green-500" on="password-updated">
                Password diperbarui.
            </x-action-message>
        </div>
    </form>
</div>
