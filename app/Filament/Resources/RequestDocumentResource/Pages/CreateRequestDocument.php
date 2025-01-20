<?php

namespace App\Filament\Resources\RequestDocumentResource\Pages;

use App\Filament\Resources\RequestDocumentResource;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateRequestDocument extends CreateRecord
{
    protected static string $resource = RequestDocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        isset($data['user_id']) ? $data['user_id'] : $data['user_id'] = auth()->user()->id;

        isset($data['status']) ? $data['status'] : $data['status'] = 'pending';

        // $data['file_path'] = $this->generatePDF($data['user_id']);

        $created = $this->getModel()::create($data);

        $type = $data['type'];

        if ($data['user_id'] == auth()->user()->id) {

            if (auth()->user()->isConsumer()) {
                Notification::make()
                    ->title('Request Document - '.auth()->user()->name.' ('.($data['type'] == 'barangay_id' ? 'Barangay ID' : 'Barangay Clearance').')')

                    ->success()
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->markAsRead()
                            ->url('/app/request-documents/'.$created->id.'/edit'),
                    ])
                    ->sendToDatabase(User::whereIn('role', ['admin', 'clerk'])->get());
            }
        }

        return $created;
    }

    public function generatePDF($userId)
    {

        $user = User::where('id', $userId)->first();

        $data = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $pdfContent = \PDF::loadView('pdf.barangay_id', $data);

        $filePath = 'pdfs/user_'.$user->id.'_'.time().'.pdf';
        Storage::put($filePath, $pdfContent->output());

        return $filePath;
    }
}
