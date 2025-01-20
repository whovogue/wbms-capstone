<?php

namespace App\Filament\Pages;

use App\Models\Charge;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Charges extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.charges';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 11;

    public ?array $residentialData = [];

    public ?array $commercialData = [];

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function mount()
    {
        $residentialCharge = Charge::where('name', 'Residential')->first();
        $commercialCharge = Charge::where('name', 'Commercial')->first();

        $this->getResedintialFormSchema->fill($residentialCharge->toArray());

        $this->getCommercialFormSchema->fill($commercialCharge->toArray());
    }

    protected function getForms(): array
    {
        return [
            'getResedintialFormSchema',
            'getCommercialFormSchema',
        ];
    }

    public function getResedintialFormSchema(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('minimumConsumption')
                            ->label('Minimum Consumption')
                            ->numeric()
                            ->prefix('m³')
                            ->columnSpan(1),
                        TextInput::make('exceedChargePerUnit')
                            ->label('Excess Charge Per Unit')
                            ->numeric()
                            ->prefix('m³')
                            ->columnSpan(1),
                        TextInput::make('minimum')
                            ->label('Minimum Value')
                            ->prefix('₱')
                            ->numeric()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('residentialData');
    }

    public function getCommercialFormSchema(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        TextInput::make('minimumConsumption')
                            ->label('Minimum Consumption')
                            ->numeric()
                            ->prefix('m³')
                            ->columnSpan(1),
                        TextInput::make('exceedChargePerUnit')
                            ->label('Excess Charge Per Unit')
                            ->numeric()
                            ->prefix('m³')
                            ->columnSpan(1),
                        TextInput::make('minimum')
                            ->label('Minimum Value')
                            ->prefix('₱')
                            ->numeric()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('commercialData');
    }

    public function submit()
    {
        $resedintial = $this->getResedintialFormSchema->getState();

        $commercial = $this->getCommercialFormSchema->getState();

        Charge::where('name', 'Residential')->update($resedintial);

        Charge::where('name', 'Commercial')->update($commercial);

        Notification::make()
            ->title('Charges Updated')
            ->success()
            ->send();

        return redirect('/app/charges');
    }
}
