<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$activeTab === 'overview'"
                tag="button"
                type="button"
                wire:click="$set('activeTab', 'overview')"
            >
                {{ __('admin.statistics') ?: 'Overview' }}
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$activeTab === 'orders'"
                tag="button"
                type="button"
                wire:click="$set('activeTab', 'orders')"
            >
                {{ __('strings.orders') }}
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$activeTab === 'branches'"
                tag="button"
                type="button"
                wire:click="$set('activeTab', 'branches')"
            >
                {{ __('strings.branches') }}
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$activeTab === 'users'"
                tag="button"
                type="button"
                wire:click="$set('activeTab', 'users')"
            >
                {{ __('strings.users') }}
            </x-filament::tabs.item>
        </x-filament::tabs>
    </div>
</x-filament-panels::page>
