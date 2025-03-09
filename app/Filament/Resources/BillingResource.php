<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingResource\Pages;
use App\Models\Bill;
use App\Models\Payment;
use App\Services\EmailService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BillingResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Water Connection';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        // Only show the badge to admins
        if (auth()->user() && auth()->user()->isAdmin()) {
            $pendingCount = Bill::where('status', 'pending')->count();
            
            // Return the count of pending bills as the badge
            return $pendingCount > 0 ? (string) $pendingCount : null;
        }
        
        // If the user is not an admin, return null to hide the badge
        return null;
    }
    
    protected static ?string $navigationBadgeTooltip = 'The number of Unpaid/Pending Billings';
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isConsumer();
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
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        $groups = [
            Tables\Grouping\Group::make('waterConnection.name')
                ->label('Owner Name')
                ->collapsible(),
            Group::make('status'),
        ];

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Date Created')->date('F d, Y h:i A')->timezone('Asia/Manila')->toggleable(),
                Tables\Columns\TextColumn::make('waterConnection.reference_id')->weight(FontWeight::Bold)->label('Water Connection Ref. ID')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('waterConnection.name')->label('Owner Name')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('reading.total_consumption')->label('Water Consumption ')
                    ->formatStateUsing(fn (string $state): string => $state.' m³')->toggleable(),
                Tables\Columns\TextColumn::make('billing_amount')->money('PHP')->weight(FontWeight::Bold)->toggleable(),
                Tables\Columns\TextColumn::make('partial_payment')->money('PHP')->weight(FontWeight::Bold)->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'pending' => 'gray',
                    'partial' => 'warning',
                })->formatStateUsing(fn (string $state): string => __(ucfirst($state)))->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                    ]),
                SelectFilter::make('Water Connection')
                    ->relationship('waterConnection', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('Pay')
                    ->label('Pay Full')
                    ->icon('heroicon-o-banknotes')
                    ->visible(auth()->user()->isAdmin())
                    ->hidden(fn (Model $record): bool => $record->status === 'paid' || $record->status === 'partial')
                    ->requiresConfirmation()
                    ->action(function ($data, Model $record) {

                        $bills = Bill::where('status', 'partial')->get();

                        $total = BillingResource::getTotal($record);

                        $payment = Payment::where('bill_id', $record->id)->first();
                        // dd($payment, $total);
                        foreach ($bills as $bill) {
                            $bill->status = 'paid';
                            $bill->save();
                        }

                        if ($payment) {
                            $payment->update([
                                'partial_payment' => $total,
                            ]);
                        } else {
                            $record->payment()->create([
                                'amount' => $record->billing_amount,
                                'bill_id' => $record->id,
                                'partial_payment' => $total,
                                'water_connection_id' => $record->waterConnection->id,
                            ]);
                        }

                        $record->update([
                            'status' => 'paid',
                            'is_discounted' => BillingResource::isDiscounted($record->created_at, now()->format('Y-m-d')),
                        ]);

                        $amount = [
                            'amount' => $total,
                        ];

                        (new EmailService)->handle($record->waterConnection->users()->get(), $amount, 'paymentFull');

                        Notification::make()
                            ->title('Payment Successful')
                            ->success()
                            ->send();
                    })
                    ->modalDescription(function ($record) {

                        $value = BillingResource::getTotal($record);

                        return 'Total Amount to be paid: PHP '.$value;
                    }),
                Action::make('partial')
                    ->label('Partial Payment')
                    ->icon('heroicon-o-credit-card')
                    ->visible(auth()->user()->isAdmin())
                    ->hidden(fn (Model $record): bool => $record->status === 'paid' || $record->status === 'partial')
                    ->requiresConfirmation()
                    ->action(function ($data, Model $record) {

                        $record->update([
                            'status' => 'partial',
                            'partial_payment' => $data['amount'],
                        ]);

                        $payment = Payment::where('bill_id', $record->id)->first();

                        if ($payment) {
                            $payment->update([
                                'partial_payment' => $data['amount'],
                                'amount' => $record->billing_amount,
                                'bill_id' => $record->id,
                                'water_connection_id' => $record->waterConnection->id,
                            ]);
                        } else {
                            $record->payment()->create([
                                'partial_payment' => $data['amount'],
                                'amount' => $record->billing_amount,
                                'bill_id' => $record->id,
                                'water_connection_id' => $record->waterConnection->id,
                            ]);
                        }
                    })
                    ->form([
                        TextInput::make('amount')
                            ->numeric(),
                    ]),
                // Action::make('generate')
                //     ->icon('heroicon-o-archive-box-arrow-down')
                //     ->label('Generate PDF')
                //     ->modalCancelAction(false)
                //     ->modalSubmitAction(false)
                //     ->modalHeading('PDF')
                //     ->modalContent(function ($record): View {
                //         return view('filament.pages.display-bill', [
                //             'file' => $record,
                //         ]);
                //     })
                //     ->visible(function ($record) {

                //         if ($record->status === 'paid' || $record->status === 'partial') {
                //             return true;
                //         }

                //         return false;
                //     })
                //     ->modalWidth('full'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])->groups(auth()->user()->isAdmin() ? $groups : [])
            ->modifyQueryUsing(function (Builder $query) {

                $user = auth()->user();

                $firstConnection = $user->waterConnections()->first();

                if ($user->isAdmin()) {

                    $query->latest();
                } else {
                    if ($firstConnection) {
                        $pivotStatus = $firstConnection['pivot']['status'];

                        if ($pivotStatus === 'pending') {

                            $query->where('id', 0);
                        } elseif ($pivotStatus === 'disconnected') {
                            $disconnectedAt = $user->disconnectedUsers()->first()?->disconnected_at;

                            $query->where('created_at', '<', $disconnectedAt)
                                ->where('water_connection_id', $firstConnection?->id)->latest();
                        } else {

                            $query->where('water_connection_id', $firstConnection->id)->latest();
                        }
                    } else {
                        $query->where('id', 0);
                    }
                }

                $query->get();
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
            'index' => Pages\ListBillings::route('/'),
            'create' => Pages\CreateBilling::route('/create'),
            'edit' => Pages\EditBilling::route('/{record}/edit'),
        ];
    }

    // public static function isDiscounted($date, $dateNow)
    // {
    //     $carbonDate = Carbon::parse($date);
    //     $carbonDateNow = Carbon::parse($dateNow);

    //     // Need Update sa cutoff date: dapat start sa reading date then 18 days from that and if the 18th day is on weekends extend it to teh next monday

    //     $firstDayOfMonth = $carbonDate->copy()->startOfMonth();

    //     $readingdate = $carbonDate->copy()->$dateNow;

    //     $cutOffDate = $firstDayOfMonth->addDays(14)->format('Y-m-d');
    //     $cutOffDate2 = $readingdate->addDays(14)->format('Y-m-d');

    //     return $carbonDateNow->lessThanOrEqualTo($cutOffDate);
    // }

    public static function isDiscounted($date, $dateNow)
    {
        $carbonDate = Carbon::parse($date);
        $carbonDateNow = Carbon::parse($dateNow);
    
        $firstDayOfNextMonth = $carbonDate->copy()->addMonthNoOverflow()->startOfMonth();
    
        // Calculate cutoff date: 18 days from the first day of the next month
        $cutOffDate = $firstDayOfNextMonth->addDays(17);
    
        // If the cutoff date falls on a weekend, move to the next Monday
        if ($cutOffDate->isWeekend()) {
            $cutOffDate->next(Carbon::MONDAY);
        }
        return $carbonDateNow->lessThanOrEqualTo($cutOffDate);
    }

    public static function getTotal($record)
    {
        $bills = Bill::where('status', 'partial')->get();
        $partialValue = 0;
        $isDiscount = BillingResource::isDiscounted($record->created_at, now()->format('Y-m-d'));

        $withoutcharges = $record->billing_amount - 40;
        $discountwithoutcharges = $withoutcharges * 0.05;
        $totaldiscountedprice = $record->billing_amount - $discountwithoutcharges;

        foreach ($bills as $bill) {
            $difference = $bill->billing_amount - $bill->partial_payment;
            $partialValue += $difference;
        }

        return $isDiscount ? ($record->billing_amount - $discountwithoutcharges) + $partialValue : $partialValue + $record->billing_amount;
        // return $isDiscount ? ($record->billing_amount * 0.95) + $partialValue : $partialValue + $record->billing_amount;
        // return $isDiscount ? ($partialValue + $record->billing_amount) * 0.95 : $partialValue + $record->billing_amount;
    }
}
