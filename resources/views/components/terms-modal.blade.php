<x-filament::modal id="termsModal" wire:model.defer="showModal">
    <x-slot name="header">
        <h2 class="text-lg font-bold">{{ __('Terms and Conditions') }}</h2>
    </x-slot>

    <div class="text-sm text-gray-700">
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sunt fugit inventore fugiat minima unde assumenda. Dicta eveniet aperiam quia illo repellat maxime fugit id enim! Eligendi nihil tempore eaque voluptate?</p>
    </div>

    <x-slot name="footer">
        <x-filament::button wire:click="closeModal" color="gray">
            {{ __('Close') }}
        </x-filament::button>
    </x-slot>
</x-filament::modal>
