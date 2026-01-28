<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-3 cursor-grab font-medium text-gray-600 dark:text-gray-200 shadow-sm"
    style="border-left: 4px solid {{ $record->pipelineStage?->color ?? '#6366f1' }}"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}, true) < 3)
        x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
    @endif
>
    <div class="flex items-center justify-between gap-2">
        <div class="font-semibold text-sm truncate">{{ $record->name }}</div>
        @if($record->value > 0)
            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                {{ number_format($record->value, 2, ',', ' ') }} €
            </div>
        @endif
    </div>

    @if($record->company)
        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 mt-1.5">
            <x-heroicon-m-building-office class="w-3 h-3 shrink-0" />
            <span class="truncate">{{ $record->company->name }}</span>
        </div>
    @endif

    @if($record->contact)
        <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 mt-1.5">
            <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold shrink-0">
                {{ collect(explode(' ', $record->contact->name))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') }}
            </div>
            <span class="truncate">{{ $record->contact->name }}</span>
        </div>
    @endif

    @if($record->expected_close_date)
        <div class="flex items-center justify-end mt-1.5">
            <span class="text-xs {{ $record->expected_close_date->isPast() && !$record->pipelineStage?->is_won && !$record->pipelineStage?->is_lost ? 'text-rose-500 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">
                {{ $record->expected_close_date->format('d/m/Y') }}
            </span>
        </div>
    @endif
</div>
