<x-filament::page>
    <div class="flex flex-col items-center space-y-6">
        <!-- Profile Image Preview -->
        <div class="relative w-32 h-32">
            <img src="{{ auth()->user()->profile_photo_path ? asset(path: 'storage/profile-photos/' . auth()->user()->profile_photo_path) : asset('images/default-avatar.png') }}" class="rounded-full border shadow-md" />
        </div>

        <form wire:submit.prevent="save" class="w-full max-w-2xl space-y-6">
            {{ $this->form }}

            <div class="text-center">
                <x-filament::button type="submit">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament::page>
