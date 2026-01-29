<x-filament-panels::page.simple>
    <div class="text-center">
        <div class="mb-6">
            <x-heroicon-o-clock class="w-16 h-16 mx-auto text-warning-500" />
        </div>

        <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white mb-4">
            {{ __('Account Pending Approval') }}
        </h2>

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            {{ __('Registration successful!') }}
            <br>
            {{ __('Please wait for admin approval.') }}
        </p>

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
            {{ __('You will be able to access your dashboard once an administrator approves your account.') }}
        </p>

        <x-filament::button
            color="gray"
            wire:click="logout"
        >
            {{ __('Sign Out') }}
        </x-filament::button>
    </div>
</x-filament-panels::page.simple>
