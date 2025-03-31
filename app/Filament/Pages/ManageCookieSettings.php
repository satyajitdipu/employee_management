<?php

namespace App\Filament\Pages;

use App\Enums\ProjectNames;
use App\Settings\CookieSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageCookieSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?int $navigationSort = 50;

    protected static string $settings = CookieSettings::class;

    public function mount(): void
    {
        if (!auth()->user()->can("manage settings")) {
            abort(403);
            return;
        }
        parent::mount();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can("manage settings");
    }

    public static function getNavigationGroup(): ?string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.group-cranberry-cookie-settings'));
    }

    public static function getLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.cranberry-cookie-webhook-settings'));
    }

    public static function getNavigationLabel(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.cranberry-cookie-webhook-settings'));
    }

    public function getTitle(): string
    {
        return strval(__('cranberry-cookie::cranberry-cookie.section.cranberry-cookie-webhook-settings'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)
                    ->schema([
                        Tabs::make('cookie-settings')
                            ->tabs([
                                Tab::make(strval(__('cranberry-cookie::cranberry-cookie.tab.webhook-settings')))
                                    ->schema([
                                        Repeater::make('webhooks')
                                            ->label('')
                                            ->schema([
                                                Select::make('name')
                                                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.name")))
                                                    ->options(ProjectNames::getProjectNames())
                                                    ->required(),
                                                TextInput::make('url')
                                                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.url")))
                                                    ->url()
                                                    ->required(),
                                                TextInput::make('secret-key')
                                                    ->password()
                                                    ->revealable()
                                                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.secret-key")))
                                                    ->required(),
                                                TextInput::make('secret-header')
                                                    ->password()
                                                    ->revealable()
                                                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.secret-header")))
                                                    ->required(),
                                                Toggle::make('status')
                                                    ->label(strval(__("cranberry-cookie::cranberry-cookie.form.input.status")))
                                                    ->inline(false),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->maxItems(25)
                                            ->addActionLabel('Add new webhook'),
                                    ]),
                            ])
                    ])
            ]);
    }
}
