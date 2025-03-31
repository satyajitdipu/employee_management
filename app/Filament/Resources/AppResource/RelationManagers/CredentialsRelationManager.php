<?php

namespace App\Filament\Resources\AppResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CredentialsRelationManager extends RelationManager
{

    protected static string $relationship = 'Credentials';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->required()
                        ->label('Name'),
                    TextInput::make('authorized_origin')
                        ->required()
                        ->label('Authorized Origin'),
                    TextInput::make('redirect_uri')
                        ->required()
                        ->label('Redirect URI')
                ]),
                Grid::make()
                    ->schema([
                        Section::make([
                            TextInput::make('client_api_key')
                                ->label('Client API Key')
                                ->readOnly()
                                ->default(fn () => bin2hex(random_bytes(32))),
                            TextInput::make('client_api_secret')
                                ->label('Client API Secret')
                                ->readOnly()
                                ->default(function () {
                                    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_'; // Allowed characters
                                    $patternLength = strlen($characters);
                                    $length = 24;
                                    $clientSecret = '';
                                    for ($i = 0; $i < $length; $i++) {
                                        $clientSecret .= $characters[rand(0, $patternLength - 1)];
                                    }
                                    $clientSecret = substr_replace($clientSecret, '_', 15, 1);
                                    return $clientSecret;
                                })
                        ])
                            ->columns(2)
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name'),
                TextColumn::make('authorized_origin')
                    ->label('Authorized origin'),
                TextColumn::make('redirect_uri')
                    ->label('Redirect URI'),
                TextColumn::make('client_api_key')
                    ->label('Client API Key'),
                TextColumn::make('client_api_secret')
                    ->label('Client API Secret')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->createAnother(false),
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
                //
            ]);
    }
}
