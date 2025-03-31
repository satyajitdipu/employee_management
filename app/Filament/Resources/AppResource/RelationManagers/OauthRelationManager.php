<?php

namespace App\Filament\Resources\AppResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OauthRelationManager extends RelationManager
{
    protected static string $relationship = 'oauths';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Client Name'),
                TextInput::make('redirect_uri')
                    ->label('Redirect URI')
                    ->rules([
                        function () {
                            return function (string $attribute, $state, Closure $fail) {
                                if (!filter_var($state, FILTER_VALIDATE_URL) !== false) {
                                    $fail('Not a valid URL.');
                                }
                            };
                        },
                    ]),
                TextInput::make('id')
                    ->label('Client ID'),
                TextInput::make('client_secret')
                    ->label('Client Secret'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('redirect_uri'),
                Tables\Columns\TextColumn::make('id')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
