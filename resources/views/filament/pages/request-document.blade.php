<x-filament-panels::page>
    <div>
        {{ $this->table }}
        <div>
            <x-filament::modal id="barangay_id" width="5xl" sticky-header :close-button="false">
                <x-slot name="heading">
                    Barangay ID
                </x-slot>
                <div>
                    <span wire:loading wire:target="generateDocument">Loading...</span>
                    <div x-data x-init="$wire.generateDocument().then(() => {
                        document.getElementById('barangayID').src = '{{ route('pdf.generateBarangayID', ['user' => $data]) }}';
                    })">
                        <iframe id="barangayID" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                    </div>
                </div>
            </x-filament::modal>
        </div>
        <div>
            <x-filament::modal id="barangay_clearance" width="5xl" sticky-header :close-button="false">
                <x-slot name="heading">
                    Barangay Clearance
                </x-slot>
                <div>
                    <span wire:loading wire:target="generateDocument">Loading...</span>
                    <div x-data x-init="$wire.generateDocument().then(() => {
                        document.getElementById('barangayClearance').src = '{{ route('pdf.generateBarangayClearance', ['user' => $data]) }}';
                    })">
                        <iframe id="barangayClearance" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                    </div>
                </div>
            </x-filament::modal>
        </div>
</x-filament-panels::page>
