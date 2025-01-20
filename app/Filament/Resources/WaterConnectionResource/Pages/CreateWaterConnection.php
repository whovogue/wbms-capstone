<?php

namespace App\Filament\Resources\WaterConnectionResource\Pages;

use App\Filament\Resources\WaterConnectionResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateWaterConnection extends CreateRecord
{
    protected static string $resource = WaterConnectionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['reference_id'] = $this->generateReferenceId();
        $data['status'] = 'pending';
        $data['connected_date'] = null;

        return static::getModel()::create($data);
    }

    private function generateReferenceId(): string
    {
        do {
            // Generate a random 10-digit number
            $referenceId = 'WBS'.random_int(1000000, 9999999); // Adjust the range as needed
        } while (DB::table('water_connections')->where('reference_id', $referenceId)->exists());

        return $referenceId;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('New Connection')
            ->success()
            ->sendToDatabase(User::whereNot('id', auth()->user()->id)->get());
    }
}
