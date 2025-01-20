<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use App\Models\User;
use App\Services\EmailService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {

        $user = User::whereNot('id', auth()->user()->id)->get();

        Notification::make()
            ->title('New Announcement from '.auth()->user()->name)
            ->body($data['description'])
            ->success()
            ->sendToDatabase($user);

        // unset($data['role']);
        (new EmailService)->handle(User::whereNot('id', auth()->user()->id)->get(), $data, 'announcement');

        return static::getModel()::create($data);
    }
}
