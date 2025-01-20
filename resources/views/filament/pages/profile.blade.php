<x-filament-panels::page>

    <div class="flex justify-center items-center">
        {{ $this->getProfilePhotoFormSchema }}
    </div>

    {{ $this->getProfileDataFormSchema }}
    <div class="text-left">
        <x-filament::button wire:click="submit" class="align-right">
            Submit
        </x-filament::button>
    </div>
</x-filament-panels::page>
