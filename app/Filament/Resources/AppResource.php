<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\App;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Validation\Rules;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Validator;
use App\Filament\Resources\AppResource\Pages;
use App\Filament\Resources\AppResource\RelationManagers\OauthRelationManager;

class AppResource extends Resource
{
    protected static ?string $model = App::class;

    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-authorization'));
    }

    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.authorization'));
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.app_name"))),
                        TextInput::make('url')
                            ->required()
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.app_url")))
                            ->rules([
                                function () {
                                    return function (string $attribute, $state, Closure $fail) {
                                        if (!filter_var($state, FILTER_VALIDATE_URL) !== false) {
                                            $fail('Not a valid URL.');
                                        }
                                    };
                                },
                            ]),
                        Select::make('organization_id')
                            ->relationship('organizations', 'name')
                            ->required()
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.organization_id")))
                            ->createOptionForm([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                                            ->required(),
                                        TextInput::make('email')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.email")))
                                            ->unique(ignoreRecord: true)
                                            ->required()
                                            ->email(),
                                        TextInput::make('phone')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.phone")))
                                            ->required(),
                                        TextInput::make('address')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.address")))
                                            ->required(),
                                        Textarea::make('description')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.description")))
                                            ->columnSpanFull()
                                            ->rows(3)
                                    ])
                            ])
                            ->editOptionForm([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                                            ->required(),
                                        TextInput::make('email')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.email")))
                                            ->required()
                                            ->email(),
                                        TextInput::make('phone')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.phone")))
                                            ->required(),
                                        TextInput::make('address')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.address")))
                                            ->required(),
                                        Textarea::make('description')
                                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.description")))
                                            ->columnSpanFull()
                                            ->rows(3)
                                    ])
                            ]),
                        Textarea::make('description')
                            ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.app_description")))
                            ->rows(3)
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.app_name"))),
                TextColumn::make('description')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.app_description"))),
                TextColumn::make('url')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.app_url"))),
                TextColumn::make('organizations.name')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.organization_id")))
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
    public static function getRelations(): array
    {
        return basename(url()->current()) === "edit" ? [] : [OauthRelationManager::class];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApps::route('/'),
            'view' => Pages\ViewApp::route('/{record}/view'),
            'create' => Pages\CreateApp::route('/create'),
            'edit' => Pages\EditApp::route('/{record}/edit'),
        ];
    }
}
