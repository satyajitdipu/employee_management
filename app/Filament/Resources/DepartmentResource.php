<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Rules\NumericallyDifferent;



class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-employee-management'));
    }

    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.departments'));
    }
    protected static ?string $navigationIcon = 'heroicon-m-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    TextInput::make('name')
                        ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                        ->required(),
                    Textarea::make('description')
                        ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.description")))
                        ->required(),
                    Select::make('parent_id')
                        ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.parent_id")))
                        ->relationship('parent_department', fn () => "name")
                        ->rules([
                            new NumericallyDifferent(['data.id'], __('cranberry-muffin::cranberry-muffin.form.input.department.parent_department_cannot_be_same')),
                        ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                    ->sortable(),
                TextColumn::make('description')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.description")))
                    ->sortable(),
            ])
            ->striped()
            ->deferLoading()
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
            'view' => pages\ViewDepartments::route('/{record}'),
        ];
    }
}
