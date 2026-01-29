<x-filament-panels::page>
    {{-- Pending Invitations --}}
    @php
        $invitations = $this->getPendingInvitations();
    @endphp

    @if($invitations->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
                {{ __('Pending Invitations') }}
            </h3>
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($invitations as $invitation)
                        <li class="p-4 flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $invitation->email }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Role') }}: {{ $invitation->role === 'admin' ? __('Admin') : __('Member') }}
                                    &middot;
                                    {{ __('Expires') }}: {{ $invitation->expires_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="resendInvitation({{ $invitation->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900 transition"
                                >
                                    <x-heroicon-m-envelope class="w-4 h-4" />
                                    {{ __('Resend') }}
                                </button>
                                <div
                                    x-data="{ copied: false }"
                                    class="relative"
                                >
                                    <button
                                        type="button"
                                        x-on:click="
                                            navigator.clipboard.writeText('{{ $invitation->getAcceptUrl() }}');
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                                    >
                                        <x-heroicon-m-clipboard-document class="w-4 h-4" />
                                        <span x-show="!copied">{{ __('Copy Link') }}</span>
                                        <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    wire:click="cancelInvitation({{ $invitation->id }})"
                                    wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-danger-600 dark:text-danger-400 bg-danger-50 dark:bg-danger-950 rounded-lg hover:bg-danger-100 dark:hover:bg-danger-900 transition"
                                >
                                    <x-heroicon-m-x-mark class="w-4 h-4" />
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Team Members Table --}}
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
            {{ __('Team Members') }}
        </h3>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
