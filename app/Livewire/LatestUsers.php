<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUsers extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->latest()->take(10)->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('created_at')->label('Date Created')->date('F d, Y h:i A')->timezone('Asia/Manila'),
            ])
            ->paginated(false);
    }
}
