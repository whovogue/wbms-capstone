<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class PaymentHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static string $view = 'filament.pages.payment-history';

    protected static ?string $navigationGroup = 'Management';

    public static function canAccess(): bool
    {
        return auth()->user()->isConsumer();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = auth()->user();

                $firstConnection = $user->waterConnections()->first();

                if ($firstConnection) {
                    $pivotStatus = $firstConnection['pivot']['status'];

                    if ($pivotStatus === 'pending') {

                        return Payment::query()->where('id', 0);
                    } elseif ($pivotStatus === 'disconnected') {

                        $disconnectedAt = $user->disconnectedUsers()->first()?->disconnected_at;

                        return Payment::query()
                            ->where('water_connection_id', $firstConnection?->id)
                            ->where('created_at', '<', $disconnectedAt)->latest();
                    } else {
                        return Payment::query()->where('water_connection_id', $firstConnection->id)->latest();
                    }
                }

                return Payment::query()->where('id', 0);
            })
            ->columns([
                TextColumn::make('bill.reading.total_consumption')
                    ->label('Total Consumption')
                    ->formatStateUsing(fn (string $state): string => $state.' m³')->weight(FontWeight::Bold),
                TextColumn::make('bill.status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'pending' => 'gray',
                    'partial' => 'warning',
                })->label('Status'),
                TextColumn::make('amount')
                    ->money('PHP')
                    ->label('Billing Amount')
                    ->weight(FontWeight::Bold),
                TextColumn::make('partial_payment')
                    ->money('PHP')
                    ->label('Paid Amount')
                    ->weight(FontWeight::Bold),
                TextColumn::make('created_at')->label('Payment Date')->date('F d, Y h:i A')->timezone('Asia/Manila'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('generate')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->label('Generate PDF')
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false)
                    ->modalHeading('PDF')
                    ->modalContent(function ($record): View {
                        return view('filament.pages.display-bill', [
                            'file' => $record->bill,
                        ]);
                    })
                    ->visible(function ($record) {

                        if ($record->bill->status === 'paid' || $record->bill->status === 'partial') {
                            return true;
                        }

                        return false;
                    })
                    ->modalWidth('full'),
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
