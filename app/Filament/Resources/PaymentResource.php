<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\Widgets\TotalAmountAndTotalCubicMeter;
use App\Models\Payment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Histories';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bill.waterConnection.reference_id')->weight(FontWeight::Bold)->searchable(),
                TextColumn::make('bill.waterConnection.name')->label('Owner Name')->searchable(),
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
                //
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
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //          Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->groups([
                Tables\Grouping\Group::make('bill.waterConnection.name')
                    ->label('Owner Name')
                    ->collapsible(),
            ])
            ->headerActions([
                Action::make('generateReport')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->label('Generate Report')
                    ->modalWidth('sm')
                    ->form([
                        DatePicker::make('from')
                            ->required()
                            ->default(now()->startOfMonth()->startOfDay()->format('Y-m-d'))
                            ->label('From'),
                        DatePicker::make('to')
                            ->default(now()->endOfMonth()->endOfDay()->format('Y-m-d 00:00:00'))
                            ->required()
                            ->label('To'),
                    ])
                    ->action(function (array $data) {

                        $payments = Payment::with('waterConnection', 'bill')->whereHas('bill', function ($query) {
                            $query->where('status', '!=', 'pending');
                        })->whereBetween('created_at', [$data['from'], $data['to']])->get();

                        $pdf = \PDF::loadView('filament.pages.display-generated-report', [
                            'payments' => $payments,
                            'from' => $data['from'],
                            'to' => $data['to'],
                            'total' => $payments->sum('partial_payment'),
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, now()->format('Y-m-d h:i:s').'.pdf');
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->latest();
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TotalAmountAndTotalCubicMeter::class,
        ];
    }
}
