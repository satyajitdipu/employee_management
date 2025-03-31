<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RolesResource\Pages;
use App\Filament\Resources\RolesResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\Section as Card;
use Phpsa\FilamentAuthentication\Resources\RoleResource\Pages\EditRole;
use Phpsa\FilamentAuthentication\Resources\RoleResource\Pages\ViewRole;
use Phpsa\FilamentAuthentication\Resources\RoleResource\Pages\ListRoles;
use Phpsa\FilamentAuthentication\Resources\RoleResource\Pages\CreateRole;
use Phpsa\FilamentAuthentication\Resources\RoleResource\RelationManager\UserRelationManager;
use Phpsa\FilamentAuthentication\Resources\RoleResource\RelationManager\PermissionRelationManager;

class RolesResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationGroup = 'Authentication';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';


    public function __construct()
    {
        static::$model = config('filament-authentication.models.Role');
    }

    public static function getLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.role'));
    }

    public static function getNavigationGroup(): ?string
    {
        return strval(__(config('filament-authentication.section.group') ?? 'filament-authentication::filament-authentication.section.group'));
    }

    public static function getPluralLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.roles'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(strval(__('filament-authentication::filament-authentication.field.name')))
                                    ->required(),
                                TextInput::make('guard_name')
                                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                                    ->required()
                                    ->default(config('auth.defaults.guard')),
                                // BelongsToManyMultiSelect::make('permissions')
                                //     ->label(strval(__('filament-authentication::filament-authentication.field.permissions')))
                                //     ->relationship('permissions', 'name')
                                //     ->hidden()
                                //     ->preload(config('filament-spatie-roles-permissions.preload_permissions'))
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
                SelectFilter::make('guard_name')
                    ->multiple()
                    ->options(fn () => collect(config('auth.guards'))->keys()->mapWithKeys(fn ($g) => [$g => $g])->toArray())
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
            PermissionRelationManager::class,
            UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit'   => EditRole::route('/{record}/edit'),
            'view'   => ViewRole::route('/{record}'),
        ];
    }
}
