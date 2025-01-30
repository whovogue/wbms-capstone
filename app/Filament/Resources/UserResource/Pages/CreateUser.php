<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;


class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'User successfully created';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['consumer_number'] = $this->generateConsumerNumber();

        return static::getModel()::create($data);
    }

    public function generateConsumerNumber(): string
    {
        do {
            $consumerNumber = random_int(1000, 9999).'-'.random_int(100000, 999999); // Adjusted range for the first part
        } while (User::where('consumer_number', $consumerNumber)->exists());

        return $consumerNumber;
    }
}
