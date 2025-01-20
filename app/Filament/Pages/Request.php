<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\WaterConnection;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Request extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.request';

    public ?array $documentData = [];

    public ?array $waterConnectionData = [];

    public ?array $requestDocumentData = [];

    public $checker = null;

    public $data;

    public static function canAccess(): bool
    {
        return false;
    }

    public function getDocumentFormSchema(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('address'),
                TextInput::make('relationship'),
                TextInput::make('phone_number')->tel()->telRegex('/^(0|63)\d{10}$/'),
            ])
            ->statePath('documentData');
    }

    public function getwaterConnectionFormSchema(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('reference_id')
                    ->label('Reference ID')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {

                            $data = WaterConnection::where('status', 'active')
                                ->pluck('reference_id');

                            $matchFound = false;

                            foreach ($data as $ref) {
                                if ($ref === $value) {
                                    $matchFound = true;
                                    break;
                                }
                            }

                            if (! $matchFound) {
                                $fail('The :attribute is invalid!');
                            }
                        },
                    ]),
            ])
            ->statePath('waterConnectionData');
    }

    public function getRequestDocumentSchema(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('option')
                    ->required()
                    ->label('Type')
                    ->options([
                        'barangay_id' => 'Barangay ID',
                        'barangay_clearance' => 'Barangay Clearance',
                    ]),
            ])
            ->statePath('requestDocumentData');
    }

    protected function getForms(): array
    {
        return [
            'getDocumentFormSchema',
            'getwaterConnectionFormSchema',
            'getRequestDocumentSchema',
        ];
    }

    public function submitDocument()
    {
        $data = $this->getDocumentFormSchema->getState();

        dd($data);
    }

    public function submitConnection()
    {
        $data = $this->getwaterConnectionFormSchema->getState();

        $connection = WaterConnection::where('reference_id', $data['reference_id'])->first();

        $connection->users()->attach(auth()->user()->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Notification::make()
            ->title('Request Submitted')
            ->success()
            ->send();

        Notification::make()
            ->title(auth()->user()->name.' wants to join '.$data['reference_id'].' water connection')
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->markAsRead()
                    ->url('/app/water-connections/'.$connection->id.'/edit?activeRelationManager=2'),
            ])
            ->sendToDatabase(User::where('role', 'admin')->whereNot('id', auth()->user()->id)->get());

        redirect('/app/request');
    }

    public function getRequestDocument()
    {
        $option = $this->getRequestDocumentSchema->getState();

        $this->data = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ];

        $this->dispatch('open-modal', id: $option['option']);
    }

    public function generateDocument() {}

    public function disconnectRequest()
    {
        $connection = WaterConnection::where('reference_id', auth()->user()->waterConnections()->first()?->id->reference_id)->first();

        Notification::make()
            ->title('Disconnection Request Submitted')
            ->success()
            ->send();

        Notification::make()
            ->title(auth()->user()->name.' wants to disconnect from '.$connection['reference_id'].' water connection')
            ->success()
            ->actions([
                Action::make('view')
                    ->button()
                    ->markAsRead()
                    ->url('/app/water-connections/'.$connection->id.'/edit?activeRelationManager=2'),
            ])
            ->sendToDatabase(User::where('role', 'admin')->whereNot('id', auth()->user()->id)->get());

        redirect('/app/request');
    }
}
