@php
    $height = $this->kanbanHeight ?? 600;
@endphp

<div class="md:w-[19.3rem] flex-shrink-0 mb-5 md:min-h-full flex flex-col overflow-hidden"
    style="height: {{ $height }}px;">
    @include(static::$headerView)

    <div x-bind:data-status-id="status.id"
        x-on:scroll.debounce.150ms="if (status.hasMore && !status.loading && !status._cooldown && $el.scrollTop + $el.clientHeight >= $el.scrollHeight - 200) loadMore(status.id)"
        class="flex flex-col flex-1 gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl overflow-y-auto overflow-x-hidden"
        style="overflow-anchor: none;">
        <template x-for="record in status.records" :key="record.id">
            @include(static::$recordView)
        </template>
        <div x-show="status.loading" class="flex items-center justify-center gap-2 py-3 kanban-no-drag" x-cloak>
            <x-filament::loading-indicator class="h-5 w-5" />
            <span class="text-xs text-gray-400">{{ __('pages/post-calculation.loading') }}...</span>
        </div>
    </div>

    @if (self::$hasCreateAction ?? false)
        <div x-data="{
            createRecord() {
                this.$wire.mountAction('create', {
                    record: status.id
                });
            }
        }">
            <x-filament::button size="sm" x-on:click="createRecord" class="w-full">
                {{ $this->createAction->getLabel() }}
            </x-filament::button>
        </div>
    @endif
</div>
