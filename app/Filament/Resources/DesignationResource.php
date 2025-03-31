<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DesignationResource\Pages;
use App\Filament\Resources\DesignationResource\RelationManagers;
use App\Models\Designation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DesignationResource extends Resource
{
    protected static ?string $model = Designation::class;

    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-employee-management'));
    }
    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.designations'));
    }
    protected static ?string $navigationIcon = 'heroicon-m-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Textarea::make('name')
                        ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                        ->required(),
                    Select::make('department_id')
                        ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.department_id")))
                        ->relationship('department', fn () => "name"),
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
                TextColumn::make('department.name')
                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.department_id")))
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
            'index' => Pages\ListDesignations::route('/'),
            'create' => Pages\CreateDesignation::route('/create'),
            'edit' => Pages\EditDesignation::route('/{record}/edit'),
            'view' => Pages\ViewDesignation::route('/{record}'),

        ];
    }
}
