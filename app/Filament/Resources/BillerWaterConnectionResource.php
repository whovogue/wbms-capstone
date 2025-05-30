<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillerWaterConnectionResource\Pages;
use App\Models\Payment;
use App\Models\WaterConnection;
use App\Services\EmailService;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BillerWaterConnectionResource extends Resource
{
    protected static ?string $model = WaterConnection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'active');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isMeterReader();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_id')->label('Reference ID'),
                Tables\Columns\TextColumn::make('name'),
                // Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('purok'),
                Tables\Columns\TextColumn::make('charge.name')->label('Type'),
                // Tables\Columns\TextColumn::make('phone_number')->label('Phone Number'),
                TextColumn::make('readings.previous_reading')->label('Previous Reading')
                    ->formatStateUsing(function ($record) {
                        return $record->readings()->orderBy('created_at', 'desc')->first()?->present_reading.' m³';
                    }),
                    TextColumn::make('updated_at')
                    ->label('Last Read Date')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => optional($record->readings()->latest('updated_at')->first())->updated_at
                        ? Carbon::parse($record->readings()->latest('updated_at')->value('updated_at'))->format('M d, Y')
                        : null),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('read')->icon('heroicon-o-pencil')
                    ->form([
                        TextInput::make('reading')->label('Present Reading')
                            ->numeric()
                            ->minValue(function (Model $record) {

                                $lastReading = $record->readings()->orderBy('created_at', 'desc')->first()?->present_reading;

                                // return $lastReading + 1 ?? 1;
                                return $lastReading;
                            })
                            ->required()
                            ->suffix('m³'),
                    ])
                    ->action(function ($data, Model $record) {
                        // charge values
                        $minimumValue = $record->charge->minimum; // 125
                        $minimumConsumption = $record->charge->minimumConsumption; // 10
                        $exceedChargePerUnit = $record->charge->exceedChargePerUnit; // 13
                    
                        $previousReading = $record->readings()->orderBy('created_at', 'desc')->first()?->present_reading ?? 0;
                        $totalReading = $data['reading'] - $previousReading;
                        $billsPartial = $record->bills()->where('status', 'partial')->get();
                        $billsPending = $record->bills()->where('status', 'pending')->get();
                        $partialValue = 0;
                    
                        foreach ($billsPending as $pending) {
                            $pending->status = 'unpaid';
                            $pending->save();
                        }
                    
                        foreach ($billsPartial as $bill) {
                            $difference = $bill->billing_amount - $bill->partial_payment;
                            $partialValue += $difference;
                        }

                        $advance = 0;

                        $lastBill = $record->bills()->latest()->first();
                        $secondToLastBill = $record->bills()->orderBy('created_at', 'desc')->skip(2)->first();
                        $paidBill = $record->bills()
                        ->where('status', 'paid')
                        ->orderByDesc('created_at')
                        ->first();
                        $unpaidBill = $record->bills()
                        ->where('status', 'unpaid')
                        ->orderByDesc('created_at')
                        ->skip(1);
                        $advanceFromAnyBill = $record->bills()
                        ->where('advance_payment', '>=', 1)
                        ->orderByDesc('created_at')
                        ->first();

                        if ($lastBill && $lastBill->status === 'unpaid') {
                        // Mark last bill as unpaid
                        $unpaidBill->update([
                            'advance_payment' => 0,
                        ]);
                        $lastBill->update([
                            'advance_payment' => $advanceFromAnyBill?->advance_payment ?? 0,
                        ]);

                        $paidBill->update([
                            'advance_payment' => 0,
                        ]);



                        // $unpaidBill->update([

                        //     'advance_payment' => $advanceFromAnyBill,

                        // ]);

                        // $advanceFromAnyBill->update([

                        //     'advance_payment' => 0,

                        // ]);

                        // Check if the second-to-last bill is paid and has advance_payment
                        // if ($secondToLastBill && $secondToLastBill->status === 'paid' && $secondToLastBill->advance_payment > 0) {
                        //     $advance = $secondToLastBill->advance_payment;

                            // Transfer advance_payment to the newly marked unpaid bill
                        //     $lastBill->update([
                        //         'advance_payment' => $advance,
                        //     ]);

                            // Reset the old bill's advance_payment
                        //     $secondToLastBill->update([
                        //         'advance_payment' => 0,
                        //     ]);
                        // }
                        }

                        // $allBill->update([
                        // 'advance_payment' => 0,
                        // ]);
                    
                        // Create the new reading
                        $reading = $record->readings()->create([
                            'present_reading' => $data['reading'],
                            'biller_user_id' => auth()->user()->id,
                            'total_consumption' => $totalReading,
                            'previous_reading' => $previousReading,
                        ]);
                    
                        $amount = ($totalReading > 0)
                            ? ($totalReading > $minimumConsumption 
                                ? (($totalReading - $minimumConsumption) * $exceedChargePerUnit) + $minimumValue 
                                : $minimumValue) 
                            : 0; // Set billing amount to 0 if total consumption is 0
                    
                        $bills = $record->bills()->create([
                            'billing_amount' => $amount > 0 ? $amount + 40 : 0,
                            'reading_id' => $reading->id,
                            'status' => 'pending',
                            'minimum' => $minimumValue,
                            'minimumConsumption' => $minimumConsumption,
                            'exceedChargePerUnit' => $exceedChargePerUnit,
                        ]);

                        // If there was an advance from previous bill, apply it here
                        if ($advance !== null && $advance > 0) {
                            $bills->update([
                                'advance_payment' => $advance,
                            ]);
                        }
                    
                        if ($amount > 0 && $record->bills()->orderBy('created_at', 'desc')->first()?->status === 'pending') {
                            Payment::create([
                                'amount' => $amount + 40,
                                'bill_id' => $bills->id,
                                'partial_payment' => 0,
                                'water_connection_id' => $record->id,
                            ]);
                        }
                    
                        $data = [
                            'reference_number' => $record->reference_id,
                            'amount' => $amount + 40,
                            'startEndDate' => BillerWaterConnectionResource::getLastMonthPeriod(now()),
                            'dueDate' => BillerWaterConnectionResource::getDiscountCutOffDate(now()),
                        ];
                    
                        (new EmailService)->handle($record->users, $data, 'reading');
                    
                        Notification::make()
                            ->title('Reading Connection')
                            ->body('Connection has been read successfully.')
                            ->success()
                            ->send();
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([]);
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
            'index' => Pages\ListBillerWaterConnections::route('/'),
        ];
    }

    //  CURRENT MONTH PERIOD NA NI KAY EVERY LAST WEEK OF THE MONTH MAN ANG READING
    public static function getLastMonthPeriod($date)
    {
        $currentDate = Carbon::parse($date);

        // $startOfLastMonth = $currentDate->copy()->subMonth()->startOfMonth()->format('F j, Y');
        // $endOfLastMonth = $currentDate->copy()->subMonth()->endOfMonth()->format('F j, Y');
        $startOfMonth = $currentDate->copy()->startOfMonth()->format('F j, Y');
        $endOfMonth = $currentDate->copy()->endOfMonth()->format('F j, Y');

        return [
            // 'start' => $startOfLastMonth,
            // 'end' => $endOfLastMonth,
            'start' => $startOfMonth,
            'end' => $endOfMonth,
        ];
    }

    // public static function getDiscountCutOffDate($date)
    // {
    //     $carbonDate = Carbon::parse($date);

    //     $firstDayOfMonth = $carbonDate->copy()->startOfMonth();

    //     $cutOffDate = $firstDayOfMonth->addDays(14)->format('F j, Y');

    //     return $cutOffDate;
    // }

    public static function getDiscountCutOffDate($date)
{
    $carbonDate = Carbon::parse($date);

    $firstDayOfNextMonth = $carbonDate->copy()->addMonthNoOverflow()->startOfMonth();

    $cutOffDate = $firstDayOfNextMonth->addDays(17);

    // If the cutoff date falls on a weekend, move to the next Monday
    if ($cutOffDate->isWeekend()) {
        $cutOffDate->next(Carbon::MONDAY);
    }

    return $cutOffDate->format('F j, Y');
}

}
