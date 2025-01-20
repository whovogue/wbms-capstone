<div class="justify-center items-center flex flex-col" style="margin-top: 130px;">
    <main
        class="fi-simple-main bg-white my-16 w-full px-6 py-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:rounded-xl sm:px-12 sm:max-w-lg">
        <section class="grid auto-cols-fr gap-y-6">
            <header class="fi-simple-header flex flex-col items-center">
                <div>
                    <img src="{{ asset('/images/logoupdate.png') }}" alt="Logo" style="height: 90px;">
                </div>
                <h1
                    class="mt-1 fi-simple-header-heading text-center text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Two Factor Authentication
                </h1>
                <p class="fi-simple-header-subheading mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                    Please check your email account and enter the code below.
                </p>
            </header>
            <div>
                {{ $this->form }}
            </div>

            <div class="fi-form-actions">
                <div class="fi-ac gap-3 grid grid-cols-[repeat(auto-fit,minmax(0,1fr))]">
                    <x-filament::button wire:click.prevent="submit">
                        Confirm
                    </x-filament::button>
                </div>
                <div class="mt-3 fi-ac gap-3 grid grid-cols-[repeat(auto-fit,minmax(0,1fr))]">
                    <x-filament::button wire:click.prevent="resend" color="gray">
                        Resend Code
                    </x-filament::button>
                </div>
            </div>
        </section>
    </main>
</div>
