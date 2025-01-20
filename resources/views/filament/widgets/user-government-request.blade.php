<x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::modal>
            <x-slot name="trigger">
                <x-filament::button>
                    Documents
                </x-filament::button>
            </x-slot>

            <!-- MODAL CONTENT -->

            <div class="mb-2 text-lg">Person to be notified incase of Emergency:</div>
            {{ $this->getDocumentFormSchema }}

            <!-- /MODAL CONTENT -->

            <x-slot name="footerActions">
                <x-filament::button wire:click.prevent="submitDocument">
                    Submit
                </x-filament::button>
            </x-slot>

        </x-filament::modal>

        {{-- @if (!auth()->user()->waterConnections()->where('water_connections_users.status', 'active')->exists()) --}}
        @if (!auth()->user()->waterConnections()->exists())
            <x-filament::modal>
                <x-slot name="trigger">
                    <x-filament::button>
                        Water Connection
                    </x-filament::button>
                </x-slot>

                <!-- MODAL CONTENT -->

                <div class="mb-2 text-lg">Provide Reference ID to Join : </div>
                {{ $this->getwaterConnectionFormSchema }}

                <!-- /MODAL CONTENT -->

                <x-slot name="footerActions">
                    <x-filament::button wire:click.prevent="submitConnection">
                        Submit
                    </x-filament::button>
                </x-slot>

            </x-filament::modal>
        @else
            {{-- <x-filament::button color="danger" wire:click.prevent="disconnectRequest">
                Disconnection Request
            </x-filament::button> --}}

            <x-filament::modal>
                <x-slot name="trigger">
                    <x-filament::button color="danger">
                        Disconnection Request
                    </x-filament::button>
                </x-slot>

                <!-- MODAL CONTENT -->
                <x-slot name="heading">
                    Are you sure you want to disconnect from the water connection?
                </x-slot>
                <!-- /MODAL CONTENT -->

                <x-slot name="footerActions">
                    <x-filament::button wire:click.prevent="disconnectRequest">
                        Submit
                    </x-filament::button>
                </x-slot>

            </x-filament::modal>
        @endif

        <x-filament::modal>
            <x-slot name="trigger">
                <x-filament::button>
                    Request Document
                </x-filament::button>
            </x-slot>

            <!-- MODAL CONTENT -->
            {{ $this->getRequestDocumentSchema }}
            <!-- /MODAL CONTENT -->

            <x-slot name="footerActions">
                <x-filament::button wire:click.prevent="getRequestDocument">
                    Submit
                </x-filament::button>
            </x-slot>

        </x-filament::modal>
    </x-filament::section>


    <div>
        <x-filament::modal id="barangay_id" width="5xl" sticky-header :close-button="false">
            <x-slot name="heading">
                Barangay ID
            </x-slot>

            <div class="mt-4">
                <iframe src="{{ route('pdf.generateBarangayID', ['user' => $data]) }}"
                    style="width: 100%; height: 500px;" frameborder="0">
                </iframe>
            </div>
        </x-filament::modal>
    </div>

    <div>
        <x-filament::modal id="barangay_clearance" width="5xl" sticky-header :close-button="false">
            <x-slot name="heading">
                Barangay Clearance
            </x-slot>

            <div class="mt-4">
                <iframe src="{{ route('pdf.generateBarangayClearance', ['user' => $data]) }}"
                    style="width: 100%; height: 500px;" frameborder="0">
                </iframe>
            </div>
        </x-filament::modal>
    </div>
</x-filament-widgets::widget>
