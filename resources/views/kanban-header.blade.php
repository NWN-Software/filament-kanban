<div class="flex flex-col flex-shrink-0 w-72">
    <div class="flex items-center flex-shrink-0 h-10 justify-between">

        <div class="flex items-center">
            <span x-tooltip="status.title.length > 40 ? status.title : false" class="block text-sm font-semibold mr-2" x-text="status.title.length > 40 ? status.title.substring(0, 40) + '...' : status.title"></span>

            <span
                class="fi-badge fi-size-sm"
                x-bind:class="{
                    'fi-color fi-color-gray': ! status.badgeColor || status.badgeColor === 'gray',
                    'fi-color': status.badgeColor && status.badgeColor !== 'gray',
                    ['fi-color-' + status.badgeColor]: status.badgeColor && status.badgeColor !== 'gray',
                }"
            >
                <span class="fi-badge-label-ctn">
                    <span class="fi-badge-label" x-text="status.count !== undefined ? status.count : status.records.length"></span>
                </span>
            </span>
        </div>

        <template x-if="status.totalAttribute">
            <span class="block text-sm font-normal"
                x-text="(status.total !== undefined ? status.total : status.records.reduce((acc, record) => acc + parseFloat(record[status.totalAttribute]), 0)).toLocaleString('nl-NL', { style: 'currency', currency: 'EUR' })">
            </span>
        </template>
    </div>
</div>
