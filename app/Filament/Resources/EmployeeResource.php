<?php

namespace App\Filament\Resources;

use App\Support\Utility;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Employee;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Monarobase\CountryList\CountryListFacade;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use App\Rules\NumericallyDifferent;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\EmployeeFormStatus;
use App\Enums\EmployeeStatus;
use App\Models\EmployeeType;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section as Card;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-employee-management'));
    }

    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.employees'));
    }

    public static function generateSlugKeys($array)
    {
        $newArray = [];
        foreach ($array as $value) {
            $key = str::slug($value, '_');
            $newArray[$key] = $value;
        }

        return $newArray;
    }

    public static function getAssetFieldsFormSchema($asset_type_id, Form $form)
    {
        if (empty($asset_type_id)) {
            return [];
        }

        $fieldHandlers = [
            'text' => function ($field, $fieldResponseKey) {
                $text_input = TextInput::make($fieldResponseKey)
                    ->label($field['field_label']);
                // ->default($field['field_default_value']);
                if ($field['field_is_required'])
                    $text_input->required(true);
                if (isset($field['field_description']))
                    $text_input->helperText($field['field_description']);
                return $text_input;
            },
            'text_area' => function ($field, $fieldResponseKey) {
                $text_area_input = Textarea::make($fieldResponseKey)
                    ->label($field['field_label'])
                    ->default($field['field_default_value']);
                if ($field['field_is_required'])
                    $text_area_input->required(true);
                if (isset($field['field_description']))
                    $text_area_input->helperText($field['field_description']);
                return $text_area_input;
            },
            'number' => function ($field, $fieldResponseKey) {
                $text_input = TextInput::make($fieldResponseKey)
                    ->label($field['field_label'])
                    ->numeric()
                    ->default($field['field_default_value']);
                if ($field['field_is_required'])
                    $text_input->required(true);
                if (isset($field['field_description']))
                    $text_input->helperText($field['field_description']);
                return $text_input;
            },
            'select' => function ($field, $fieldResponseKey) {
                $select_input = Select::make($fieldResponseKey)
                    ->label($field['field_label'])
                    ->searchable(array_key_exists('pick_list', $field) && $field['pick_list'] === "time")
                    ->options(function () use ($field) {
                        if (array_key_exists('pick_list', $field) && $field['pick_list'] === "country_list") {
                            return CountryListFacade::getList('en');
                        } elseif (array_key_exists('pick_list', $field) && $field['pick_list'] === "blood_group") {
                            return [
                                "A+" => "A+",
                                "A−" => "A−",
                                "B+" => "B+",
                                "B−" => "B−",
                                "O+" => "O+",
                                "O−" => "O−",
                                "AB+" => "AB+",
                                "AB−" => "AB−",
                            ];
                        }
                        elseif (array_key_exists('pick_list', $field) && $field['pick_list'] === "time") {
                            return Utility::allowed_time_options();
                        }

                        return self::generateSlugKeys($field['field_options']);
                    });
                if ($field['field_is_required'])
                    $select_input->required(true);
                return $select_input;
            },
            'radio' => function ($field, $fieldResponseKey) {
                $radio_input = Radio::make($fieldResponseKey)
                    ->label($field['field_label'])
                    ->inline()
                    ->options(function () use ($field) {
                        return self::generateSlugKeys($field['field_options']);
                    });
                if ($field['field_is_required'])
                    $radio_input->required(true);
                return $radio_input;
            },
            'date' => function ($field, $fieldResponseKey) {
                $date_input = DatePicker::make($fieldResponseKey)
                    ->label($field['field_label'])
                    ->format('Y-m-d');
                if ($field['field_is_required'])
                    $date_input->required(true);
                if (isset($field['field_description']))
                    $date_input->helperText($field['field_description']);
                return $date_input;
            },
            'toggle' => function ($field, $fieldResponseKey) {
                $toggle_input = Toggle::make($fieldResponseKey)
                    ->label($field['field_label']);
                return $toggle_input;
            },

        ];

        $asset_type_fields = EmployeeType::find($asset_type_id);
        $schema = [];

        if ($asset_type_fields) {
            foreach ($asset_type_fields->fields as $section) {
                $layoutType = $section['layout_type'];
                $sectionName = $section['layout_name'];
                $noOfColumnString = $section['no_of_columns'];
                $noOfColumn = intval($noOfColumnString);

                $sectionFields = [];
                foreach ($section['field_details'] as $field) {
                    $asset_field_key = "field.{$field['field_key']}";
                    $fieldType = $field['field_type'];

                    if (isset($fieldHandlers[$fieldType])) {
                        $handler = $fieldHandlers[$fieldType];
                        $sectionFields[] = $handler($field, $asset_field_key);
                    }
                }
                if ($layoutType === 'section') {
                    $schema[] = Section::make($sectionName)
                        ->heading($sectionName)
                        ->schema($sectionFields)
                        ->columns($noOfColumn)
                        ->collapsed();
                } elseif ($layoutType === 'card') {
                    $schema[] = Card::make()
                        ->schema($sectionFields)
                        ->columns($noOfColumn);
                } elseif ($layoutType === 'grid') {
                    $schema[] = Grid::make()
                        ->schema($sectionFields)
                        ->columns($noOfColumn);
                } elseif ($layoutType === 'fieldset') {
                    $schema[] = Fieldset::make($sectionName)
                        ->schema($sectionFields)
                        ->columns($noOfColumn);
                }
            }
        }
        return $schema;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('cranberry-cookie::cranberry-cookie.section.employee_details'))
                ->schema([
                    TextInput::make('employee_code')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.employee_code'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.employee_code'))
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('user_id')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.employee.user'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.employee.user'))
                        ->searchable()
                        ->relationship('user', fn () => "email")
                        ->unique(ignoreRecord: true),
                    Select::make('manager_id')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.manager'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.manager'))
                        ->searchable()
                        ->relationship('manager', fn () => "employee_code_with_full_name")
                        ->rules([
                            new NumericallyDifferent(['data.id'], __('cranberry-cookie::cranberry-cookie.validation.employee_different_from_manager')),
                        ])
                        ->dehydrateStateUsing(fn ($state) => is_numeric($state) ? (int)$state : null),
                    Select::make('department_id')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.department'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.department'))
                        ->searchable()
                        ->relationship('department', fn () => "name"),
                    Select::make('designation_id')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.designation'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.designation'))
                        ->searchable()
                        ->relationship('Designation', fn () => "name"),
                    Select::make('status')
                        ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.status")))
                        ->default(EmployeeStatus::ACTIVE)
                        ->options(EmployeeStatus::getStatuses())
                        ->required()
                        ->reactive(),
                    TextInput::make('first_name')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.first_name'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.first_name'))
                        ->required(),
                    TextInput::make('middle_name')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.middle_name'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.middle_name')),
                    TextInput::make('last_name')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.last_name'))
                        ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.last_name'))
                        ->required(),
                    SpatieMediaLibraryFileUpload::make('employee_photo')->collection('employee_photo')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.employee_photo'))
                        ->getUploadedFileNameForStorageUsing(
                            fn (BaseFileUpload $component, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string =>
                            strtolower(Str::slug(pathinfo($component->shouldPreserveFilenames() ? $file->getClientOriginalName() : $file->getFilename(), PATHINFO_FILENAME))) . "." .
                                strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION))
                        )
                        ->preserveFilenames()
                        ->image()
                        ->required()
                        ->imagePreviewHeight('250')
                        ->maxSize(10000),
                    Radio::make('gender')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.gender'))
                        ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                        ->required(),


                    // DatePicker::make('date_of_birth')
                    //     ->label(__('cranberry-cookie::cranberry-cookie.employee.input.date_of_birth'))
                    //     ->placeholder(__('cranberry-cookie::cranberry-cookie.employee.input.placeholder.date_of_birth'))
                    //     ->live()
                    //     ->afterStateUpdated(function (Set $set, ?string $state) {
                    //         $set('birthday', Str::slug($state));
                    //     })
                    //     ->required()
                    //     ->before('today'),
                    // DatePicker::make('birthday')
                    //     ->native(false)
                    //     ->label(__('cranberry-cookie::cranberry-cookie.employee.input.birthday'))
                    //     ->required(),
                    // Select::make('blood_group')
                    //     ->label(__('cranberry-cookie::cranberry-cookie.employee.input.blood_group'))
                    //     ->options(array_combine(['A+', 'A−', 'B+', 'B−', 'AB+', 'AB−', 'O+', 'O−'], ['A+', 'A−', 'B+', 'B−', 'AB+', 'AB−', 'O+', 'O−']))
                    //     ->required(),
                    // Select::make('nationality')
                    //     ->label(__('cranberry-cookie::cranberry-cookie.employee.input.nationality'))
                    //     ->options(CountryListFacade::getList('en'))
                    //     ->searchable()
                    //     ->required(),
                    // Select::make('country_of_birth')
                    //     ->searchable()
                    //     ->options(CountryListFacade::getList('en'))
                    //     ->label(__('cranberry-cookie::cranberry-cookie.employee.input.country_of_birth'))
                    //     ->required(),
                    // Select::make('marital_status')
                    //     ->label(__('cranberry-cookie::cranberry-cookie.employee.input.marital_status'))
                    //     ->required()
                    //     ->options([
                    //         'married' => 'Married',
                    //         'unmarried' => 'Unmarried'
                    //     ]),

                    TextInput::make('head_to_face_ratio')
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.head_to_face_ratio'))
                        ->numeric()
                        ->default(1.5)
                        ->minValue(1.0)
                        ->maxValue(2.5)
                        ->step(0.05)
                        ->extraInputAttributes([
                            'style' => 'text-align: center;'
                        ]),
                    Select::make('emptype_id')
                        ->required()
                        ->label(__('cranberry-cookie::cranberry-cookie.employee.input.emptype_id'))
                        ->relationship('employeeType', 'title', function (Builder $query) {
                            return $query->all();
                        })
                        ->getSearchResultsUsing(function ($search) {
                            return EmployeeType::where('title', 'like', "%{$search}%")->where('status', EmployeeFormStatus::PUBLISHED()->value)
                                ->limit(5)->pluck('title', 'id');
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => EmployeeType::find($value)?->name)
                        ->options(EmployeeType::orderBy("title")->where('status', EmployeeFormStatus::PUBLISHED()->value)->limit(5)->pluck('title', 'id'))
                        ->reactive()
                        ->searchable()
                        ->afterStateUpdated(function ($set, $state, \Filament\Forms\Get $get) {
                            $emp_type = EmployeeType::find($state);
                            if (!$emp_type) return;
                            $fields = $emp_type->fields;
                            foreach ($fields as $section) {
                                foreach ($section['field_details'] ?? [] as $field) {
                                    $emp_field_key = "field.{$field['field_key']}";
                                    $set($emp_field_key, ($field['field_type'] === 'toggle') ? false : '');
                                }
                            }

                            $employeeCode = $get('employee_code');

                            $employee = Employee::where('employee_code', $employeeCode)->first();

                            if ($employee && $employee->field !== null) {
                                $tableName = "employee_type_{$employee->employee_type_id}";

                                if (Schema::hasTable($tableName)) {
                                    DB::table($tableName)->where('employee_id', $employee->id)->delete();
                                }

                                $employee->field = null;
                                $employee->saveQuietly();
                            }
                        }),


                ])
                ->columns(3),

            Grid::make()
                ->id("field")
                ->schema(function (\Filament\Forms\Get $get) use ($form) {
                    $asset_type_id = $get('emptype_id');
                    $asset_fields_form = [];
                    $asset_fields_form =  self::getAssetFieldsFormSchema($asset_type_id, $form);
                    return $asset_fields_form;
                })
                ->columns(2)
                ->columns(2)
                ->hidden(fn (\Filament\Forms\Get $get) => empty($get('emptype_id')))
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('passport_photo')
                    ->collection('passport_photo')
                    ->conversion('thumb')
                    ->visibility('private'),
                TextColumn::make('employee_code')
                    ->label(__('cranberry-cookie::cranberry-cookie.table.employee_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label(__('cranberry-cookie::cranberry-cookie.table.full_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label(__('cranberry-cookie::cranberry-cookie.table.department.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('designation.name')
                    ->label(__('cranberry-cookie::cranberry-cookie.table.designation.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return (__("cranberry-cookie::cranberry-cookie.employee.status.{$state}"));
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'view' => Pages\ViewEmployee::route('/{record}'),

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
