<?php

namespace App\Filament\Resources;

use App\Enums\FieldTypeEnums;
use App\Filament\Resources\EmployeeTypeResource\Pages;
use App\Filament\Resources\EmployeeTypeResource\RelationManagers;
use App\Models\EmployeeType;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\EmployeeFormStatus;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use App\Support\Str;
use Monarobase\CountryList\CountryListFacade;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Ramsey\Uuid\Rfc4122\UuidV2;
use Illuminate\Database\Eloquent\Model;

class EmployeeTypeResource extends Resource
{
    protected static ?string $model = EmployeeType::class;
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-employee-management'));
    }

    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.employees_types'));
    }

    public static function canEdit(Model $record): bool
    {
        if ($record->status->value !==  EmployeeFormStatus::PUBLISHED()->value) {
            return static::can('update', $record);
        }
        return false;
    }

    public static function getOptionalFieldsFormSchema($field_type, $form)
    {
        if (empty($field_type)) {
            return [];
        }

        $createTextInput = function ($field) {
            return TextInput::make('field_default_value')
                ->id($field)
                ->numeric($field === 'number');
        };


        $fieldHandlers = [
            'text' =>  $createTextInput,
            'text_area' => $createTextInput,
            'number' => $createTextInput,

        ];

        $schema = [];

        if (isset($fieldHandlers[$field_type])) {
            $handler = $fieldHandlers[$field_type];
            $schema[] = $handler($field_type);
        }
        return $schema;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('title')
                        ->label(strval(__('cranberry-cookie::cranberry-cookie.form.title')))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, \Filament\Forms\Get $get, \Filament\Forms\Set $set) {
                            $set('slug', Str::slug($state) . '-' . UuidV2::uuid4()->toString());
                        })
                        ->required(),
                    TextInput::make('slug')
                        ->label(strval(__('cranberry-cookie::cranberry-cookie.form.slug')))
                        ->required()
                        ->readOnly(),
                    TextInput::make('short_code')
                        ->label(strval(__('cranberry-cookie::cranberry-cookie.form.short-code')))
                        ->default("-- Generated Once Saved --")
                        ->disabled(true),
                    Textarea::make('description')
                        ->label(strval(__('cranberry-cookie::cranberry-cookie.form.description'))),
                    Section::make(strval(__('cranberry-cookie::cranberry-cookie.form.custom_field')))
                        ->schema([
                            Repeater::make('fields')
                                ->reorderable(false)
                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.custom_fields')))
                                ->minItems(1)
                                ->required()
                                ->schema([
                                    Card::make([
                                        TextInput::make('layout_name')
                                            ->label(strval(__('cranberry-cookie::cranberry-cookie.form.Section_name')))
                                            ->required()
                                            ->dehydrateStateUsing(fn (string $state): string => ucwords($state)),
                                        Select::make('layout_type')
                                            ->label(strval(__('cranberry-cookie::cranberry-cookie.form.Layout_type')))
                                            ->required()
                                            ->options([
                                                'section' => 'Section',
                                                'card' => 'Card',
                                                'grid' => 'Grid',
                                                'fieldset' => 'Fieldset',
                                            ]),
                                        Radio::make('no_of_columns')
                                            ->label(strval(__('cranberry-cookie::cranberry-cookie.form.No_of_column')))
                                            ->required()
                                            ->options([
                                                1 =>  'One',
                                                2  =>  'Two',
                                                3 => 'Three',
                                                4 => 'Four',
                                            ])->inline(),
                                    ])->columns(2),

                                    Repeater::make('field_details')
                                        ->label(strval(__('cranberry-cookie::cranberry-cookie.form.field_details')))
                                        ->minItems(1)
                                        ->required()
                                        ->schema([
                                            TextInput::make('field_label')
                                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.field_label')))
                                                ->dehydrateStateUsing(fn (string $state): string => ucwords($state))
                                                ->required()
                                                ->reactive()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (\Filament\Forms\Set $set) {
                                                    $set("field_key", "");
                                                })
                                                ->afterStateUpdated(function (\Filament\Forms\Set $set, $state, $context) {
                                                    $set('field_key', Str::slug($state, '_'));
                                                })
                                                ->maxLength(60)
                                                ->columnSpan(2),
                                            TextInput::make('field_key')
                                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.field_key')))
                                                ->required()
                                                ->readOnly()
                                                ->maxLength(64)
                                                ->columnSpan(2),
                                            Toggle::make('field_is_required')
                                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.field_is_required')))
                                                ->inline(false)
                                                ->onIcon('heroicon-s-check')
                                                ->required()->columnSpan(3),
                                            Select::make('field_type')
                                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.field_type')))
                                                ->options(FieldTypeEnums::getTypes())
                                                ->reactive()
                                                ->required()
                                                ->columnSpan(2)
                                                ->columnSpan(2),
                                            Grid::make()
                                                ->schema(function (\Filament\Forms\Get $get) use ($form) {
                                                    $field_type = $get('field_type');
                                                    $optional_fields = self::getOptionalFieldsFormSchema($field_type, $form);
                                                    return $optional_fields;
                                                })
                                                ->hidden(function (\Filament\Forms\Get $get) use ($form) {
                                                    $field_type = $get('field_type');
                                                    $optional_fields = self::getOptionalFieldsFormSchema($field_type, $form);
                                                    return count($optional_fields) < 1;
                                                })
                                                ->columns(1)
                                                ->columnSpan(2),
                                            TagsInput::make('field_options')
                                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.field_options')))
                                                ->placeholder(strval(__('cranberry-cookie::cranberry-cookie.form.field_options_hint')))
                                                ->hidden(function ($get) {
                                                    // dd($get('pick_list'));
                                                    if ($get('pick_list')!="none") {
                                                        $allowedType = ['radio', 'select'];
                                                        return !in_array($get('field_type'), $allowedType) || $get('pick_list');
                                                    }
                                                })
                                                ->required(function ($get) {
                                                    $allowedType = ['radio', 'select'];
                                                    return in_array($get('field_type'), $allowedType);
                                                })
                                                ->columnSpan(2),
                                            Radio::make('pick_list')
                                                ->label(strval(__('cranberry-cookie::cranberry-cookie.form.pick_list')))
                                                ->options([
                                                    'country_list' => "Country List",
                                                    'blood_group' => "Blood Group",
                                                    'time'=>"Time",
                                                    'none'=>"Others"
                                                ])
                                                ->required()
                                                ->reactive()
                                                ->inline(false)
                                                ->disabled(function($get){
                                                    $allowedType = ['radio', 'select'];
                                                    return !in_array($get('field_type'), $allowedType) ||  $get('blood_group');
                                                })
                                                ->hidden(function ($get) {
                                                    return $get('field_type') !== 'select';
                                                }),
                                          
                                        ])
                                        ->columns(4)
                                        ->addActionLabel(strval(__('cranberry-cookie::cranberry-cookie.employee-type.form.repeater.field.action.label')))
                                ])
                                ->addActionLabel(strval(__('cranberry-cookie::cranberry-cookie.employee-type.form.repeater.layout.action.label'))),
                        ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(strval(__('cranberry-cookie::cranberry-cookie.form.title')))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(strval(__('cranberry-cookie::cranberry-cookie.form.created_at')))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(strval(__('cranberry-cookie::cranberry-cookie.form.updated_at')))
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(strval(__('cranberry-cookie::cranberry-cookie.form.status')))
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return (__("employee_form.status.{$state}"));
                    })
                    ->colors(EmployeeFormStatus::getStatusColors()),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_status')
                        ->label(function (EmployeeType $record): string {
                            return ($record->status->value === EmployeeFormStatus::DRAFT()->value)
                                ? __('cranberry-cookie::cranberry-cookie.form.status.set-to-accepting-responses')
                                : __('cranberry-cookie::cranberry-cookie.form.status.set-to-draft');
                        })
                        ->icon(function (EmployeeType $record): string {
                            return ($record->status->value === EmployeeFormStatus::DRAFT()->value)
                                ? 'heroicon-o-clipboard-document-check'
                                : 'heroicon-o-pencil-square';
                        })
                        ->color(function (EmployeeType $record): string {
                            return ($record->status->value === EmployeeFormStatus::DRAFT()->value)
                                ? 'success'
                                : 'warning';
                        })
                        ->action(function (EmployeeType $record): void {
                            $record->setAttribute('status', $record->status->value === EmployeeFormStatus::DRAFT()->value ? EmployeeFormStatus::PUBLISHED()->value : EmployeeFormStatus::DRAFT()->value)->save();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                ExportBulkAction::make(),
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
            'index' => Pages\ListEmployeeTypes::route('/'),
            'create' => Pages\CreateEmployeeType::route('/create'),
            'view' => Pages\ViewEmployeeType::route('/{record}'),
            'edit' => Pages\EditEmployeeType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
