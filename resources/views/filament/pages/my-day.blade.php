<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Overdue Tasks --}}
        @php
            $overdueTasks = $this->getOverdueTasks();
        @endphp
        @if($overdueTasks->isNotEmpty())
            <x-filament::section
                icon="heroicon-o-exclamation-triangle"
                icon-color="danger"
                collapsible
            >
                <x-slot name="heading">
                    <span class="text-danger-600 dark:text-danger-400">
                        {{ __('Overdue') }} ({{ $overdueTasks->count() }})
                    </span>
                </x-slot>

                <div class="space-y-2">
                    @foreach($overdueTasks as $task)
                        <x-filament::card class="bg-danger-50 dark:bg-danger-950/20 border-danger-200 dark:border-danger-800">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <x-filament::badge :color="$task->type->getColor()">
                                        <x-filament::icon
                                            :icon="$task->type->getIcon()"
                                            class="w-3 h-3 mr-1"
                                        />
                                        {{ $task->type->getLabel() }}
                                    </x-filament::badge>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ $task->title }}
                                        </p>
                                        @if($task->opportunity || $task->contact || $task->company)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ $task->opportunity?->name ?? $task->contact?->name ?? $task->company?->name }}
                                            </p>
                                        @endif
                                    </div>

                                    <x-filament::badge :color="$task->priority_color" size="sm">
                                        {{ $task->priority_label }}
                                    </x-filament::badge>

                                    <div class="text-sm text-danger-600 dark:text-danger-400 whitespace-nowrap">
                                        <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 inline" />
                                        {{ $task->due_date->format('d/m/Y') }}
                                        @if($task->due_time)
                                            {{ $task->due_time->format('H:i') }}
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-filament::button
                                        size="sm"
                                        color="success"
                                        icon="heroicon-o-check-circle"
                                        wire:click="completeTask({{ $task->id }})"
                                    >
                                        {{ __('Complete') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        </x-filament::card>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Today's Tasks --}}
        @php
            $todayTasks = $this->getTodayTasks();
        @endphp
        <x-filament::section
            icon="heroicon-o-sun"
            icon-color="warning"
            collapsible
        >
            <x-slot name="heading">
                <span class="text-warning-600 dark:text-warning-400">
                    {{ __('Today') }} ({{ $todayTasks->count() }})
                </span>
            </x-slot>

            @if($todayTasks->isEmpty())
                <div class="text-center py-8">
                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="w-12 h-12 mx-auto text-success-500"
                    />
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        {{ __('No tasks for today. Great job!') }}
                    </p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($todayTasks as $task)
                        <x-filament::card class="bg-warning-50 dark:bg-warning-950/20 border-warning-200 dark:border-warning-800">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <x-filament::badge :color="$task->type->getColor()">
                                        <x-filament::icon
                                            :icon="$task->type->getIcon()"
                                            class="w-3 h-3 mr-1"
                                        />
                                        {{ $task->type->getLabel() }}
                                    </x-filament::badge>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ $task->title }}
                                        </p>
                                        @if($task->opportunity || $task->contact || $task->company)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ $task->opportunity?->name ?? $task->contact?->name ?? $task->company?->name }}
                                            </p>
                                        @endif
                                    </div>

                                    <x-filament::badge :color="$task->priority_color" size="sm">
                                        {{ $task->priority_label }}
                                    </x-filament::badge>

                                    @if($task->due_time)
                                        <div class="text-sm text-warning-600 dark:text-warning-400 whitespace-nowrap">
                                            <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 inline" />
                                            {{ $task->due_time->format('H:i') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-filament::button
                                        size="sm"
                                        color="success"
                                        icon="heroicon-o-check-circle"
                                        wire:click="completeTask({{ $task->id }})"
                                    >
                                        {{ __('Complete') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        </x-filament::card>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Upcoming Tasks --}}
        @php
            $upcomingTasks = $this->getUpcomingTasks();
        @endphp
        <x-filament::section
            icon="heroicon-o-calendar-days"
            icon-color="info"
            collapsible
            collapsed
        >
            <x-slot name="heading">
                <span class="text-info-600 dark:text-info-400">
                    {{ __('Upcoming') }} ({{ $upcomingTasks->count() }})
                </span>
            </x-slot>

            @if($upcomingTasks->isEmpty())
                <div class="text-center py-8">
                    <x-filament::icon
                        icon="heroicon-o-calendar"
                        class="w-12 h-12 mx-auto text-gray-400"
                    />
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        {{ __('No upcoming tasks scheduled.') }}
                    </p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($upcomingTasks as $task)
                        <x-filament::card>
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <x-filament::badge :color="$task->type->getColor()">
                                        <x-filament::icon
                                            :icon="$task->type->getIcon()"
                                            class="w-3 h-3 mr-1"
                                        />
                                        {{ $task->type->getLabel() }}
                                    </x-filament::badge>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ $task->title }}
                                        </p>
                                        @if($task->opportunity || $task->contact || $task->company)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ $task->opportunity?->name ?? $task->contact?->name ?? $task->company?->name }}
                                            </p>
                                        @endif
                                    </div>

                                    <x-filament::badge :color="$task->priority_color" size="sm">
                                        {{ $task->priority_label }}
                                    </x-filament::badge>

                                    @if($task->due_date)
                                        <div class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 inline" />
                                            {{ $task->due_date->format('d/m/Y') }}
                                            @if($task->due_time)
                                                {{ $task->due_time->format('H:i') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-filament::button
                                        size="sm"
                                        color="success"
                                        icon="heroicon-o-check-circle"
                                        wire:click="completeTask({{ $task->id }})"
                                    >
                                        {{ __('Complete') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        </x-filament::card>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
