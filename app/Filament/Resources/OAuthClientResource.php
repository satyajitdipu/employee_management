<?php

namespace App\Filament\Resources;

use App\Enums\ProjectNames;
use App\Filament\Resources\OAuthClientResource\Pages;
use App\Models\OAuthClient;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class OAuthClientResource extends Resource
{
    protected static ?string $model = OAuthClient::class;

    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-authorization'));
    }

    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.oauth-clients'));
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('name')
                            ->options(ProjectNames::getProjectNames())
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.client_name"))),
                        TextInput::make('redirect_uri')
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.redirect_uri"))),
                        TextInput::make('id')
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.client_id"))),
                        TextInput::make('client_secret')
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.client_secret")))
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.client_name")))
                    ->formatStateUsing(fn ($state) => __('cranberry-cookie::cranberry-cookie.project-name.' . $state)),
                TextColumn::make('redirect_uri')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.redirect_uri"))),
                TextColumn::make('allowed_grant_types')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.allowed_grant_types")))
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOAuthClients::route('/'),
            'create' => Pages\CreateOAuthClient::route('/create'),
            'edit' => Pages\EditOAuthClient::route('/{record}/edit'),
        ];
    }
}
