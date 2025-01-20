<x-filament-panels::page>

    <style>
        /* Fullscreen overlay */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Spinner animation */
        .spinner {
            width: 60px;
            height: 60px;
            position: relative;
        }

        .double-bounce1,
        .double-bounce2 {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: #3498db;
            opacity: 0.6;
            position: absolute;
            top: 0;
            left: 0;
            animation: bounce 2s infinite ease-in-out;
        }

        .double-bounce2 {
            animation-delay: -1s;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: scale(0);
            }

            50% {
                transform: scale(1);
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @if (auth()->user()->isConsumer())
        @if (auth()->user()->waterConnections()->exists())
            <div id="loading-screen" style="display: none;">
                <div class="spinner">
                    <div class="double-bounce1"></div>
                    <div class="double-bounce2"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div style="--col-span-default: 1 / -1;"
                    class="col-[--col-span-default] fi-wi-widget fi-wi-stats-overview">
                    <div wire:poll.5s="" class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-3">
                        <div
                            class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                            <div>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-black font-bold"
                                        style="margin-right: 10px;">
                                        <span class="text-xl">
                                            <img src="{{ asset('profile-photos/' . auth()->user()->profile_photo_path) }}"
                                                style="height: 40px;">
                                        </span>
                                    </div>
                                    <div>Hello <span class="font-semibold">{{ auth()->user()->name }}</span></div>

                                    @if (auth()->user()->waterConnections()->first()['pivot']['status'] === 'active')
                                        <div style="margin-left: auto">
                                            <x-filament::modal>
                                                <x-slot name="trigger">
                                                    <x-filament::button color="danger" size="xs"
                                                        class="absolute top-2 right-2">
                                                        X
                                                    </x-filament::button>
                                                </x-slot>

                                                <!-- MODAL CONTENT -->
                                                <x-slot name="heading">
                                                    Pease select below which action you want to perform
                                                </x-slot>
                                                <x-filament::input.wrapper>
                                                    <x-filament::input.select wire:model="disconnectType">
                                                        <option value="remove">Remove the water Connection</option>
                                                        <option value="disconnect">Disconnect from water Connection
                                                        </option>
                                                    </x-filament::input.select>
                                                </x-filament::input.wrapper>
                                                <!-- /MODAL CONTENT -->

                                                <x-slot name="footerActions">
                                                    <x-filament::button wire:click.prevent="disconnectRequest">
                                                        Submit
                                                    </x-filament::button>
                                                </x-slot>

                                            </x-filament::modal>

                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-green-100 p-4 rounded-lg shadow-inner">
                                <div class="flex items-center text-green-600 font-semibold">
                                    <div
                                        class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white mr-2">
                                        💧
                                    </div>
                                    <span>{{ ucfirst(auth()->user()->waterConnections()->first()['pivot']['status']) }}</span>
                                </div>
                                <div class="mt-2 text-gray-700 text-sm">
                                    <span>Account No:
                                        {{ auth()->user()->waterConnections()->first()->reference_id }}</span>
                                </div>
                                <div class="mt-1 text-2xl font-bold text-gray-800">
                                    ₱{{ auth()->user()->waterConnections()->first()['pivot']['status'] != 'pending' ? $this->bill : 0 }}

                                </div>
                                <div class="text-gray-500 text-xs">
                                    Connection Date:
                                    {{ auth()->user()->waterConnections()->first()->created_at->format('F d, Y') }}
                                </div>
                                <div class="flex justify-between items-center">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div>
                <x-filament::section collapsible collapsed>
                    <x-slot name="heading">
                        <div wire:loading.remove wire:target="getNewExtensions"
                            class=" rounded-lg font-semibold flex items-center">
                            <img src="{{ asset('/icon/notification-bell.png') }}" style="height: auto; width: 30px">
                            <div style="margin-left: 13px;">
                                <p>Announcement (10)</p>
                            </div>
                        </div>
                    </x-slot>
                    t
                </x-filament::section>
            </div> --}}

            @if (auth()->user()->waterConnections()->first()['pivot']['status'] != 'pending')
                <div>
                    @livewire(\App\Livewire\ConsumerStatsOverview::class)
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        @livewire(\App\Livewire\ConsumerWaterConsumption::class)
                    </div>
                    <div>
                        @livewire(\App\Livewire\ConsumerSpendingChart::class)
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        {{ $this->table }}
                    </div>

                    <div id="chart" style="visibility: hidden;"></div>

                    <div id="spending" style="visibility: hidden;"></div>
                </div>
            @endif
        @else
            <div class="flex space-x-4 gap-3">
                <!-- First Modal Button -->
                <x-filament::modal>
                    <x-slot name="trigger">
                        <x-filament::button>
                            Request Water Connection
                        </x-filament::button>
                    </x-slot>

                    <!-- MODAL CONTENT -->
                    {{ $this->requesWaterConnectionForm }}
                    <!-- /MODAL CONTENT -->

                    <x-slot name="footerActions">
                        <x-filament::button wire:click.prevent="requestsubmitConnection">
                            Submit
                        </x-filament::button>
                    </x-slot>

                </x-filament::modal>

                <!-- Second Modal Button -->
                <x-filament::modal>
                    <x-slot name="trigger">
                        <x-filament::button>
                            Join Water Connection
                        </x-filament::button>
                    </x-slot>

                    <!-- MODAL CONTENT -->
                    <div class="mb-2 text-lg">Provide Reference ID to Join : </div>
                    {{ $this->joinRequestForm }}
                    <!-- /MODAL CONTENT -->

                    <x-slot name="footerActions">
                        <x-filament::button wire:click.prevent="submitConnection">
                            Submit
                        </x-filament::button>
                    </x-slot>

                </x-filament::modal>
            </div>
        @endif
    @endif

    <script>
        var options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                data: @json($data),
            }],
            xaxis: {
                categories: @json($labels),
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);

        chart.render().then(() => {
            window.setTimeout(function() {
                chart.dataURI().then(({
                    imgURI,
                }) => {
                    fetch("{{ route('chart.save') }}", {
                            method: "POST",
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                imgURI: imgURI,
                                waterConnectionId: @json($userWaterConnection)
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Image saved successfully", data);
                        })
                        .catch(error => {
                            console.error("Error saving image:", error);
                        });
                })
            }, 1000)
        });


        var spendingoptions = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                data: @json($spending),
            }],
            xaxis: {
                categories: @json($labels),
            }
        };

        var spendingchart = new ApexCharts(document.querySelector("#spending"), spendingoptions);

        spendingchart.render().then(() => {
            window.setTimeout(function() {
                spendingchart.dataURI().then(({
                    imgURI,
                }) => {
                    fetch("{{ route('spending-chart.save') }}", {
                            method: "POST",
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                imgURI: imgURI,
                                waterConnectionId: @json($userWaterConnection)
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Image saved successfully", data);
                        })
                        .catch(error => {
                            console.error("Error saving image:", error);
                        });
                })
            }, 1000)
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loadingScreen = document.getElementById('loading-screen');

            loadingScreen.style.display = 'flex';

            window.addEventListener('load', () => {
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                }, 5000);
            });
        });
    </script>

</x-filament-panels::page>
