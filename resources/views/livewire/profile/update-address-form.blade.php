<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $country = '';
    public string $city = '';
    public string $postal_code = '';

    public function mount(): void
    {
        $this->country = Auth::user()->country ?? '';
        $this->city = Auth::user()->city ?? '';
        $this->postal_code = Auth::user()->postal_code ?? '';
    }

    public function updateAddress(): void
    {
        $validated = $this->validate([
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        Auth::user()->fill($validated);
        Auth::user()->save();

        $this->dispatch('address-updated');
    }
}; ?>

<div class="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6 bg-white dark:bg-white/[0.03]">
    <div class="mb-6">
        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Address
        </h4>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Detail domisili Anda untuk keperluan administratif.
        </p>
    </div>

    <form wire:submit="updateAddress" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7">

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Country</label>
                <input wire:model="country" type="text" placeholder="Indonesia" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">City/State</label>
                <input wire:model="city" type="text" placeholder="Jakarta" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Postal Code</label>
                <input wire:model="postal_code" type="text" placeholder="10110" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>

        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                Save Address
            </button>

            <x-action-message class="me-3 text-green-500" on="address-updated">
                Alamat Tersimpan.
            </x-action-message>
        </div>
    </form>
</div>
