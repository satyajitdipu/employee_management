<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\UsersResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;

class UsersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Authentication';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic User Details')
                    ->schema([
                        TextInput::make('name')
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                            ->required(),
                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->unique(table: static::$model, ignorable: fn ($record) => $record)
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.email"))),
                        TextInput::make('password')
                            ->same('passwordConfirmation')
                            ->password()
                            ->maxLength(255)
                            ->required(fn ($component, $get, $livewire, $model, $record, $set, $state) => $record === null)
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : '')
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.password"))),
                        TextInput::make('passwordConfirmation')
                            ->password()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.passwordConfirmation"))),
                        Select::make('roles')
                            ->multiple()
                            ->relationship('Roles', 'name')
                            ->preload(config('filament-authentication.preload_roles'))
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.roles"))),
                        TimezoneSelect::make('timezone')
                            ->label(strval(__('cranberry-cookie::cranberry-cookie.user.input.timezone')))
                            ->searchable()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.id"))),
                TextColumn::make('name')
                    ->searchable()
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name"))),
                TextColumn::make('email')
                    ->searchable()
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.email"))),
                IconColumn::make('email_verified_at')
                    ->default(false)
                    ->boolean()
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.email_verified_at"))),
                TextColumn::make('roles.name')->badge()
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.roles"))),
                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.join_at")))
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.email_verified_at")))
                    ->nullable(),
            ])
            ->actions([
                Impersonate::make(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),

        ];
    }
}
