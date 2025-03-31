<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionsResource\Pages;
use App\Filament\Resources\PermissionsResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\Section as Card;
use Filament\Forms\Components\BelongsToManyMultiSelect;
use Phpsa\FilamentAuthentication\Resources\PermissionResource\Pages\EditPermission;
use Phpsa\FilamentAuthentication\Resources\PermissionResource\Pages\ViewPermission;
use Phpsa\FilamentAuthentication\Resources\PermissionResource\Pages\ListPermissions;
use Phpsa\FilamentAuthentication\Resources\PermissionResource\Pages\CreatePermission;
use Phpsa\FilamentAuthentication\Resources\PermissionResource\RelationManager\RoleRelationManager;

class PermissionsResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationGroup = 'Authentication';
    protected static ?string $navigationIcon = 'heroicon-m-lock-closed';

    public function __construct()
    {
        static::$model = config('filament-authentication.models.Permission');
    }

    public static function getLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.permission'));
    }

    public static function getNavigationGroup(): ?string
    {
        return strval(__(config('filament-authentication.section.group') ?? 'filament-authentication::filament-authentication.section.group'));
    }

    public static function getPluralLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.permissions'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->label(strval(__('filament-authentication::filament-authentication.field.name'))),
                            TextInput::make('guard_name')
                                ->required()
                                ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                                ->default(config('auth.defaults.guard')),
                            // BelongsToManyMultiSelect::make('roles')
                            //     ->label(strval(__('filament-authentication::filament-authentication.field.roles')))
                            //     ->relationship('roles', 'name')
                            //     ->preload(config('filament-spatie-roles-permissions.preload_roles'))
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.id")))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name')))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RoleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermissions::route('/create'),
            'edit' => Pages\EditPermissions::route('/{record}/edit'),
        ];
    }
}
