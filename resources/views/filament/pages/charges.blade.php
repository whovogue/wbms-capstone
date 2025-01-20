<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            {{ $this->getResedintialFormSchema }}
        </div>
        <div>
            {{ $this->getCommercialFormSchema }}
        </div>
    </div>
    <div class="text-left" style="margin-top:-10px;">
        <x-filament::button wire:click="submit" class="align-right">
            Submit
        </x-filament::button>
    </div>
</x-filament-panels::page>
