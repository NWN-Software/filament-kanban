<x-filament-panels::page class="{{ isset($showKanban) && $showKanban == false ? 'hidden' : '' }}">
    <div x-data="kanbanBoardComponent({
        livewireId: @js($this->getId()),
    })">
        @if ((isset($enableSearch) && $enableSearch) || (isset($enableFilter) && $enableFilter))
            <div class="flex justify-end gap-8 items-center">
                @if ($enableSearch ?? false)
                    {{-- <x-filament-tables::search-field @input.debounce="search" /> --}}
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" @input.debounce="search"
                            placeholder="{{ __('webshops.search') }}" />
                    </x-filament::input.wrapper>
                @endif

                @if ($enableFilter ?? false)
                    <x-filament::dropdown placement="bottom-start" width="sm">
                        <x-slot name="trigger">
                            <x-filament::icon-button icon="heroicon-o-funnel"
                                badge="{{ isset($filtersBadgeCount) && $filtersBadgeCount > 0 ? $filtersBadgeCount : '' }}" />
                        </x-slot>

                        <x-filament-panels::form class="font-normal p-4" wire:submit.prevent="onFilter">
                            {{ $this->filtersForm }}

                            <x-filament::button class="w-full -mt-2" type="submit" wire:loading.attr="disabled"
                                wire:target="onFilter">
                                {{ __('general.apply') }}
                            </x-filament::button>
                        </x-filament-panels::form>
                    </x-filament::dropdown>
                @endif
            </div>
        @endif

        <div wire:ignore class="relative md:flex overflow-x-auto overflow-y-hidden gap-2 pb-4 min-h-[66.5vh] h-full">

            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-opacity-75 z-50  ">
                <x-filament::loading-indicator class="h-8 w-8" />


            </div>
            <template x-for="status in statuses" :key="status.id">
                @include(static::$statusView)
            </template>

        </div>

        @unless ($disableEditModal)
            @if (isset($this->recordDeletable) && $this->recordDeletable)
                @include('filament.app.widgets.kanban-edit-record-modal')
            @else
                <x-filament-kanban::edit-record-modal />
            @endif
        @endunless
    </div>
    <script>
        function kanbanBoardComponent({
            livewireId
        }) {
            return {
                statuses: [],
                async search(e) {
                    this.loading = true
                    const statuses = await this.$wire.search(e.target.value)
                    this.statuses = statuses
                    this.loading = false
                },
                init() {
                    document.addEventListener('statusesUpdated', (e) => {
                        this.statuses = e.detail[0].statuses
                    })

                    document.addEventListener('updateStatusFromTo', (e) => {
                        const {
                            recordId,
                            fromStatus,
                            toStatus
                        } = e.detail[0]

                        this.handleStatusChange(recordId, fromStatus, toStatus)
                    })

                    document.addEventListener('updateRecordAttributes', (e) => {
                        const {
                            recordId,
                            statusId,
                            attributes
                        } = e.detail[0]

                        this.updateRecordAttributes(statusId, recordId, attributes)
                    })

                    document.addEventListener('deleteRecord', (e) => {
                        const {
                            statusId,
                            recordId
                        } = e.detail[0]

                        this.deleteRecord(statusId, recordId)
                    })

                    document.addEventListener('addRecord', (e) => {
                        const {
                            statusId,
                            record
                        } = e.detail[0]

                        this.addRecord(statusId, record)
                    })

                    this.loading = true

                    this.$wire.statuses().then(statuses => {
                        this.statuses = statuses
                        this.loading = false
                        this.$nextTick(() => this.initSortable())
                    })


                },
                handleStatusChange(recordId, fromStatus, toStatus) {
                    const record = this.statuses.find(status => status.id == fromStatus).records.find(record =>
                        record.id == recordId)
                    if (!record) return

                    const fromContainer = this.statuses.find(status => status.id == fromStatus)
                    if (fromContainer) {
                        fromContainer.records = fromContainer.records.filter(r => r.id !== recordId)
                    }

                    const toContainer = this.statuses.find(status => status.id == toStatus)
                    if (toContainer) {
                        toContainer.records.push(record)
                    }
                },
                addRecord(statusId, record) {
                    const status = this.statuses.find(status => status.id == statusId)
                    if (!status) return

                    status.records.push(record)
                },
                deleteRecord(statusId, recordId) {
                    const record = this.statuses.find(status => status.id == statusId).records.find(record =>
                        record.id == recordId)
                    if (!record) return

                    this.statuses.find(status => status.id == statusId).records = this.statuses.find(status => status.id ==
                        statusId).records.filter(r => r.id !== recordId)
                },
                updateRecordAttributes(statusId, recordId, attributes) {
                    let record = this.statuses.find(status => status.id == statusId).records.find(record =>
                        record.id == recordId)
                    if (!record) return

                    const newRecord = {
                        ...record,
                        ...attributes
                    }

                    Object.assign(record, newRecord)
                },
                initSortable() {
                    const self = this
                    this.statuses.forEach(status => {
                        const el = document.querySelector(`[data-status-id='${status.id}']`)
                        if (!el) return

                        Sortable.create(el, {
                            group: 'filament-kanban',
                            ghostClass: 'opacity-50',
                            animation: 0,
                            onStart() {
                                document.body.classList.add("grabbing")
                            },
                            onEnd() {
                                document.body.classList.remove("grabbing")
                            },
                            onAdd(e) {
                                const position = e.newIndex;
                                const from = e.from;
                                const to = e.to;

                                const recordId = e.item.id
                                const newStatusId = to.dataset.statusId
                                const oldStatusId = from.dataset.statusId

                                const fromOrderedIds = Array.from(e.from.children).map(c => c.id).filter(
                                    id =>
                                    id)
                                const toOrderedIds = Array.from(e.to.children).map(c => c.id).filter(id =>
                                    id)
                                self.$wire.statusChanged(recordId, newStatusId,
                                    fromOrderedIds, toOrderedIds)

                                const oldStatus = self.statuses.find(status => status.id == oldStatusId)
                                const record = oldStatus.records.find(r => r.id == recordId)
                                const newStatus = self.statuses.find(status => status.id == newStatusId)

                                oldStatus.records = oldStatus.records.filter(r => r.id != recordId)
                                newStatus.records.splice(position, 0, record);

                                e.item.remove()
                            },
                            onUpdate(e) {
                                const recordId = e.item.id
                                const status = e.from.dataset.statusId
                                const orderedIds = Array.from(e.from.children).map(c => c.id).filter(id =>
                                    id)
                                self.$wire.sortChanged(recordId, status, orderedIds)
                            },
                            setData(dataTransfer, el) {
                                dataTransfer.setData('id', el.id)
                            },
                        })
                    })
                }
            }
        }
    </script>
</x-filament-panels::page>
