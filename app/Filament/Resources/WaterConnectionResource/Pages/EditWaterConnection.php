<?php

namespace App\Filament\Resources\WaterConnectionResource\Pages;

use App\Filament\Resources\WaterConnectionResource;
use App\Models\Installation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWaterConnection extends EditRecord
{
    protected static string $resource = WaterConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        $data['amount'] = $data['status'] == 'pending' ? 0.00 : $data['amount'];

        if ($data['status'] != 'pending') {
            $installation = Installation::where('water_connection_id', $record->id)
                ->where('amount', $data['amount'])
                ->first();

            if ($installation) {
                $installation->update(['amount' => $data['amount']]);
            } else {
                $installation = Installation::create([
                    'water_connection_id' => $record->id,
                    'amount' => $data['amount'],
                    'type' => $data['status'],
                ]);
            }
        }

        $record->update($data);

        return $record;
    }
}
