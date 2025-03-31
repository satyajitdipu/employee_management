<?php

namespace App\Filament\Resources\OutpostintegrationResource\Pages;
use App\Models\Certificate;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Http\Request;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Radio;
use Closure;

use App\Filament\Resources\OutpostintegrationResource;
use InvadersXX\FilamentJsoneditor\Forms\JSONEditor;


class ManageOutpostintegrations extends ManageRecords
{
    protected static string $resource = OutpostintegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()
            // Actions\CreateAction::make('create')
            ->steps([
                Step::make('type')
                    ->label('New outpost integration')
                    ->description('Create a new outpost integration.')
                    ->schema([
                    Radio::make('type')
                        ->options([
                            'Docker-Service-Connection' => 'Docker Service-Connection',
                            'Kubernetes-Service-Connection' => 'Kubernetes Service-Connection',
                        ])
                        ->descriptions([
                            'Docker-Service-Connection' => 'Service Connection to a Docker endpoint',
                            'Kubernetes-Service-Connection' => 'Service Connection to a Kubernetes cluster',
                        ])->reactive()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                            $set('state', $state);

                            // dd($state);
                        })



                    ]),
                Step::make('dsc')
                    ->label('Create Docker Service-Connection')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');

                    return $radioValue === 'Docker-Service-Connection';})
                    ->schema([
                        TextInput::make('name'),
                        Toggle::make('local')
                        ->hint("If enabled, use the local connection. Required Docker socket/Kubernetes Integration"),

                        TextInput::make('dockerurl')
                        ->hint("Can be in the format of 'unix://' when connecting to a local docker daemon, using 'ssh://' to connect via SSH, or 'https://:2376' when connecting to a remote system."),
                        Select::make('tlsv')
                        ->hint("CA which the endpoint's Certificate is verified against. Can be left empty for no validation.")
                        ->label('TLS Verification Certificate')
                        ->searchable()
                        ->options(function () {
                            return Certificate::all()->pluck('common_name', 'id');
                        }),
                        Select::make('tlsa')
                        ->hint("Certificate/Key used for authentication. Can be left empty for no authentication. <br>When connecting via SSH, this keypair is used for authentication.")
                        ->label('TLS Authentication Certificate/SSH Keypair')
                        ->searchable()
                        ->options(function () {
                            return Certificate::all()->pluck('common_name', 'id');
                        }),

                    ]),
                Step::make('ksc')
                ->label('Create Kubernetes Service-Connection')
                ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Kubernetes-Service-Connection';})
                    ->schema([
                        TextInput::make('name'),
                        Toggle::make('local'),
                        JSONEditor::make('kubeconfig')
                        ->height(10),
                        Toggle::make('vkube')->label('Verify Kubernetes API SSL Certificate'),

                    ]),
            ])



        ];
    }
}
