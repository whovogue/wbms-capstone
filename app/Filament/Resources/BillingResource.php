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
                TextColumn::make('created_at')
                ->label('Reading Date')
                ->date('F d, Y h:i A')
                ->timezone('Asia/Manila')
                ->toggleable(),
                Tables\Columns\TextColumn::make('waterConnection.reference_id')
                ->weight(FontWeight::Bold)
                ->label('Water Connection Ref. ID')
                ->searchable()
                ->toggleable(),
                Tables\Columns\TextColumn::make('waterConnection.name')
                ->label('Owner Name')
                ->searchable()
                ->toggleable(),
                Tables\Columns\TextColumn::make('reading.total_consumption')
                ->label('Water Consumption ')
                ->formatStateUsing(fn (string $state): string => $state.' m³')
                ->toggleable(),
                Tables\Columns\TextColumn::make('billing_amount')
                ->money('PHP')
                ->weight(FontWeight::Bold)
                ->toggleable(),
                // Tables\Columns\TextColumn::make('partial_payment')
                // ->money('PHP')
                // ->weight(FontWeight::Bold)
                // ->toggleable(),
                Tables\Columns\TextColumn::make('payment_amount')
                ->money('PHP')
                ->weight(FontWeight::Bold)
                ->toggleable(),
                Tables\Columns\TextColumn::make('is_discounted')
                ->label('Discounted?')
                ->weight(FontWeight::Bold)
                // ->extraAttributes(['class' => 'italic'])
                ->searchable()
                ->toggleable()
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                ->color(fn ($state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'pending' => 'gray',
                    'partial' => 'warning',
                    'unpaid' => 'danger',
                })
                ->formatStateUsing(fn (string $state): string => __(ucfirst($state)))
                ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'unpaid' => 'Unpaid',
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
                ->label('Pay')
                ->icon('heroicon-o-banknotes')
                ->visible(auth()->user()->isAdmin())
                // ->hidden(fn (Model $record): bool => $record->status === 'paid' || $record->status === 'partial')
                ->hidden(fn (Model $record): bool => in_array($record->status, ['paid', 'partial', 'unpaid']))

                ->form(function (Model $record) {
                    // Get all previous bills
                    $previousBills = Bill::where('water_connection_id', $record->water_connection_id)
                        ->where('id', '<', $record->id)
                        ->get();
                
                    $totalPreviousBalance = 0;
                    $totalAdvancePayment = 0;
                
                    foreach ($previousBills as $bill) {
                        $totalPartialPayments = Payment::where('bill_id', $bill->id)->sum('partial_payment');
                        $billBalance = $bill->billing_amount - $totalPartialPayments;
                
                        if (($bill->status === 'partial' || $bill->status === 'unpaid') && $billBalance > 0) {
                            $totalPreviousBalance += $billBalance;
                        }
                
                        $totalAdvancePayment += $bill->advance_payment ?? 0;
                    }
                
                    // Determine billing amount
                    $billingAmount = BillingResource::getTotal($record);
                
                    // Adjusted amount to pay
                    $adjustedBillingAmount = max(($totalPreviousBalance + $billingAmount) - $totalAdvancePayment, 0);
                
                    return [
                        TextInput::make('billing_amount')
                            ->label('Bill')
                            ->default('PHP ' . number_format($billingAmount, 2))
                            ->disabled(),
                
                        TextInput::make('advance_payment')
                            ->label('Advance Payment')
                            ->default('PHP ' . number_format($totalAdvancePayment, 2))
                            ->disabled(),
                
                        TextInput::make('previous_balance')
                            ->label('Previous Balance')
                            ->default('PHP ' . number_format($totalPreviousBalance, 2))
                            ->disabled(),
                
                        TextInput::make('amount_to_pay')
                            ->label('Amount to Pay')
                            ->default('PHP ' . number_format($adjustedBillingAmount, 2))
                            ->disabled(),
                
                        TextInput::make('amount')
                            ->label('Enter Payment Amount')
                            ->numeric()
                            ->required(),
                    ];
                })
                
                ->modalDescription(function (Model $record) {
                    return "Please enter the payment amount. This will cover any previous balances and your current bill. Any extra payment will be credited as advance payment.";
                })
                ->requiresConfirmation()
                ->action(function ($data, Model $record) {
                    $paymentAmount = $data['amount'];
                    $totalAvailable = $paymentAmount;

                    // Step 0: Get all previous unpaid/partial bills
                    $previousBills = Bill::where('water_connection_id', $record->water_connection_id)
                        ->where('id', '<', $record->id)
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->orderBy('id') // oldest first
                        ->get();

                    $previousBalance = 0;
                    $previousAdvancePayment = 0;

                    foreach ($previousBills as $previousBill) {
                        $partialPaid = Payment::where('bill_id', $previousBill->id)->sum('partial_payment');
                        $advance = $previousBill->advance_payment ?? 0;

                        $due = $previousBill->billing_amount - $partialPaid - $advance;

                        if ($totalAvailable >= $due) {
                            $previousBill->update([
                                'status' => 'paid',
                                'advance_payment' => 0,
                                'is_discounted' => BillingResource::isDiscounted(
                                    $record->created_at,
                                    now()->format('Y-m-d'),
                                    $record->water_connection_id,
                                    $record->id
                                ),
                            ]);

                            $totalAvailable -= $due;
                        } else {
                            // Not enough to fully pay this bill, update partials only
                            $previousBill->update([
                                'advance_payment' => $advance,
                            ]);
                            $previousBalance += $due;
                            $previousAdvancePayment += $advance;
                        }
                    }

                    // Step 1: Billing amount
                    $billingAmount = BillingResource::getTotal($record);

                    // Step 2: Total due = previous balance + current bill - previous advance
                    $totalDue = max(($previousBalance + $billingAmount) - $previousAdvancePayment, 0);

                    // Step 3: Determine advance from overpayment
                    $advance = max($totalAvailable - $totalDue, 0);

                    // Step 4: Update current bill
                    if ($totalAvailable >= $totalDue) {
                        $record->update([
                            'status' => 'paid',
                            'advance_payment' => $advance,
                            'payment_amount' => $paymentAmount,
                            'is_discounted' => BillingResource::isDiscounted(
                                $record->created_at,
                                now()->format('Y-m-d'),
                                $record->water_connection_id,
                                $record->id
                            ),
                        ]);
                    } else {
                        $record->update([
                            'status' => 'partial',
                            'advance_payment' => 0,
                            'payment_amount' => $paymentAmount,
                            'partial_payment' => $paymentAmount,
                        ]);
                    }

                    // Step 5: Save payment
                    $payment = Payment::where('bill_id', $record->id)->first();
                    if ($payment) {
                        $payment->update([
                            'partial_payment' => $paymentAmount,
                            'amount' => $record->billing_amount,
                            'bill_id' => $record->id,
                            'water_connection_id' => $record->waterConnection->id,
                        ]);
                    } else {
                        $record->payment()->create([
                            'partial_payment' => $paymentAmount,
                            'amount' => $record->billing_amount,
                            'bill_id' => $record->id,
                            'water_connection_id' => $record->waterConnection->id,
                        ]);
                    }

                    // Step 6: Email notification
                    $amount = ['amount' => $paymentAmount];
                    (new EmailService)->handle($record->waterConnection->users()->get(), $amount, 'paymentFull');

                    Notification::make()
                        ->title('Payment Successful')
                        ->body("TotalDue: ₱{$totalDue}, TotalAvailable: ₱{$totalAvailable}, BillingAmount: ₱{$billingAmount}")
                        ->success()
                        ->send();
                }),
            
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
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

public static function isDiscounted($date, $dateNow, $waterConnectionId, $currentBillId)
{
    if (!$currentBillId) {
        return false;
    }

    $carbonDate = Carbon::parse($date);
    $carbonDateNow = Carbon::parse($dateNow);

    $firstDayOfNextMonth = $carbonDate->copy()->addMonthNoOverflow()->startOfMonth();

    // Cutoff date: 18 days from start of next month
    $cutOffDate = $firstDayOfNextMonth->addDays(17);

    // Move cutoff date to Monday if it falls on a weekend
    if ($cutOffDate->isWeekend()) {
        $cutOffDate->next(Carbon::MONDAY);
    }

    $currentBill = Bill::find($currentBillId);
    if (!$currentBill) {
        return false;
    }

    // Check for older unpaid/partial bills based on date, not ID
    $hasUnpaidOrPartialPreviousBills = Bill::where('water_connection_id', $waterConnectionId)
        ->where('created_at', '<', $currentBill->created_at)
        ->whereIn('status', ['unpaid', 'partial'])
        ->exists();

    return !$hasUnpaidOrPartialPreviousBills && $carbonDateNow->lessThanOrEqualTo($cutOffDate);
}


    public static function getTotal($record)
{
    // Fetch only the bill related to the given record
    $bills = Bill::whereIn('status', ['partial', 'unpaid'])
        ->where('id', $record->id) // Only fetch the relevant bill
        ->get();
        
    $partialValue = 0;
    $isDiscount = BillingResource::isDiscounted($record->created_at,now()->format('Y-m-d'),$record->water_connection_id,$record->id);

    $withoutcharges = max($record->billing_amount - 40, 0); // Ensure it does not go negative
    $discountwithoutcharges = $withoutcharges * 0.05;
    $totaldiscountedprice = $record->billing_amount - $discountwithoutcharges;

    foreach ($bills as $bill) {
        $difference = max($bill->billing_amount - $bill->partial_payment, 0); // Prevent negative values
        $partialValue += $difference;
    }

    return $isDiscount 
        ? ($record->billing_amount - $discountwithoutcharges) + $partialValue 
        : $partialValue + $record->billing_amount;
}

}
