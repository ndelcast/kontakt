<x-filament-panels::page>
    <div
        x-data
        wire:ignore.self
        class="md:flex gap-4 pb-4"
        style="overflow-x: auto; overflow-y: hidden; margin-left: calc(-1 * var(--page-padding)); margin-right: calc(-1 * var(--page-padding)); padding-left: var(--page-padding); padding-right: var(--page-padding);"
    >
        @foreach($statuses as $status)
            @include(static::$statusView)
        @endforeach

        <div wire:ignore>
            @include(static::$scriptsView)
        </div>
    </div>

    @unless($disableEditModal)
        <x-filament-kanban::edit-record-modal/>
    @endunless

    <style>
        :root {
            --page-padding: 1.5rem;
        }

        /* Remove the max-width constraint on the kanban page content */
        .fi-page-opportunities-kanban-board .fi-section-content,
        .fi-page-opportunities-kanban-board .fi-page-content {
            max-width: 100% !important;
        }
    </style>
</x-filament-panels::page>
