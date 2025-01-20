<?php

namespace App\Filament\Pages;

use App\Models\RequestDocument as ModelsRequestDocument;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class RequestDocument extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static string $view = 'filament.pages.request-document';

    protected static ?string $navigationGroup = 'Management';

    public $data;

    public static function canAccess(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->whereNot('id', auth()->user()->id)->where('role', 'consumers'))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('document')
                    ->label('Generate')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($data, User $user) {

                        $created = ModelsRequestDocument::create([
                            'user_id' => $user->id,
                            'type' => $data['option'],
                            'status' => 'pending',
                            'file_path' => $this->generatePDF($user->id),
                        ]);

                        Notification::make()
                            ->title('Request Document Submitted')
                            ->send()
                            ->success();

                        Notification::make()
                            ->title('New Request Document from '.$user->name)
                            ->success()
                            ->actions([
                                ActionsAction::make('view')
                                    ->button()
                                    ->markAsRead()
                                    ->url('/app/request-documents/'.$created->id.'/edit'),
                            ])
                            ->sendToDatabase(User::where('role', 'admin')->get());
                    })
                    ->form([
                        Select::make('option')
                            ->required()
                            ->label('Type')
                            ->options([
                                'barangay_id' => 'Barangay ID',
                                'barangay_clearance' => 'Barangay Clearance',
                            ]),
                    ])
                    ->modalWidth(MaxWidth::Small),
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function generateDocument() {}

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
