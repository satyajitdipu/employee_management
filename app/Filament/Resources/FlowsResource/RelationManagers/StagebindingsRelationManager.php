<?php

namespace App\Filament\Resources\FlowsResource\RelationManagers;

use App\Filament\Resources\StagesResource;
use App\Models\Flows;
use App\Models\Stages;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\Concerns\HasRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StagebindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'Stagebindings';

    protected static ?string $recordTitleAttribute = 'flows_id';

    public function form(Form $form): Form
    {
        return
        $form
            ->schema([
                Wizard::make([


                    Step::make('type')

                    ->description('Give the category a clear and unique name')
                    ->schema([
                    Radio::make('type')

                    ->options([
                        'Authenticator Validation Stage' => 'Authenticator Validation Stage',
                        'Captcha Stage' => 'Captcha Stage',
                        'Consent Stage' => 'Consent Stage',
                        'Deny Stage' => 'Deny Stage',
                        'Duo Authenticator Setup Stage' => 'Duo Authenticator Setup Stage',
                        'Email Stage' => 'Email Stage',
                        'Identification Stage' => 'Identification Stage',
                        'Invitation Stage' => 'Invitation Stage',
                        'Password Stage' => 'Password Stage',
                        'Prompt Stage' => 'Prompt Stage',
                        'SMS Authenticator Setup Stage' => 'SMS Authenticator Setup Stage',
                        'Static Authenticator Stage' => 'Static Authenticator Stage',
                        'TOTP Authenticator Setup Stage' => 'TOTP Authenticator Setup Stage',
                        'User Delete Stage' => 'User Delete Stage',
                        'User Login Stage' => 'User Login Stage',
                        'User Logout Stage' => 'User Logout Stage',
                        'User Write Stage' => 'User Write Stage',
                        'WebAuthn Authenticator Setup Stage' => 'WebAuthn Authenticator Setup Stage',
                    ])
                    ->descriptions([
                        'Authenticator Validation Stage' => "Validate user's configured OTP Device.",
                        'Captcha Stage' => "Verify the user is human using Google's reCaptcha.",
                        'Consent Stage' => 'Prompt the user for confirmation.',
                        'Deny Stage' => 'Cancels the current flow.',
                        'Duo Authenticator Setup Stage' => 'Setup Duo authenticator devices',
                        'Email Stage' => 'Sends an Email to the user with a token to confirm their Email address.',
                        'Identification Stage' => 'Allows the user to identify themselves for authentication.',
                        'Invitation Stage' => 'Simplify enrollment; allow users to use a single link to create their user with pre-defined parameters.',
                        'Password Stage' => 'Prompts the user for their password, and validates it against the configured backends.',
                        'Prompt Stage' => 'Define arbitrary prompts for the user.',
                        'SMS Authenticator Setup Stage' => 'Use SMS-based TOTP instead of authenticator-based.',
                        'Static Authenticator Stage' => 'Generate static tokens for the user as a backup.',
                        'TOTP Authenticator Setup Stage' => "Enroll a user's device into Time-based OTP.",
                        'User Delete Stage' => 'Deletes the currently pending user without confirmation. Use with caution.',
                        'User Login Stage' => 'Attaches the currently pending user to the current session.',
                        'User Logout Stage' => 'Resets the users current session.',
                        'User Write Stage' => 'Writes currently pending data into the pending user, or if no user exists, creates a new user with the data.',
                        'WebAuthn Authenticator Setup Stage' => 'WebAuthn stage',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                    $set('state', $state);

                    // dd($state);
                    })



                ]),
                    Step::make('Authenticator Validation Stage')
                    ->label('Create Authenticator Validation Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');

                    return $radioValue === 'Authenticator Validation Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                    TextInput::make('type')->default('Authenticator Validation Stage')->disabled(),
                    TextInput::make('Name')->required(),
                    Select::make('Device_classes')
                        ->multiple()
                        ->options([
                            'st' => 'Static Tokens',
                            'ta' => 'TOTP Authenticators',
                            'wa' => 'WebAuthn Authenticators',
                            'da' => 'Duo Authenticators',
                            'sba' => 'SMS-based Authenticators',
                        ])
                        ->hint('Device classes which can be used to authenticate.<br>Hold control/command to select multiple items.'),

                    TextInput::make('Last_validation_threshold')
                    ->default('Seconds=0')
                    ->required()
                    ->hint('If any of the devices user of the types selected above have been used within this duration, this stage will be skipped.<br>(Format: hours=1;minutes=2;seconds=3).'),
                    Select::make('Not_configured_action')
                            ->options([
                                'ftutcaa' => 'Force the User to configure an authenticator',
                                'dtua' => 'Deny the user access',
                                'c' => 'Continue',
                            ])
                            ->default('ftutcaa'),
                    Radio::make('WebAuthn_User_verification ')
                        ->options([
                                'uvmo' => 'User verification must occur.',
                                'uvp' => 'User verification is preferred if available, but not required.',
                                'uvs' => 'User verification should not occur.'
                        ]),
                    Select::make('Configuration_stages')
                    ->multiple()
                    ->options(function () {
                        return Stages::all()->pluck('Name', 'id');
                        })
                    ->hint("Stages used to configure Authenticator when user doesn't have any compatible devices. After this configuration Stage passes, the user is not prompted again.<br>When multiple stages are selected, the user can choose which one they want to enroll."),
                    // ->relationship('Name','Stages'),
                        // ->options([
                            // 'dai' => 'default-authentication-identification(Identification Stage)',
                            // 'dal' => 'default-authentication login(User Login Stage)',
                            // 'damv' => 'default-authentication-mfa-validation(Authenticator Validation Stage)',
                            // 'dap' => 'default-authentication-password(Password Stage)',
                            // 'Duo Authenticator Setup Stage' => 'default-authentication-static-setup(Static Authenticator Stage)',
                            // 'dats' => 'default-authentication-totp-setup(TOTP Authenticator Setup Stage)',
                            // 'daws' => 'default-authentication-webauthn-setup(WebAuthn Authenticator Setup Stage)',
                            // 'dil' => 'default-invalidation-logout(User Logout Stage)',
                            // 'dpcp' => 'default-password-change-prompt(Prompt Stage)',
                            // 'dpcw' => 'default-password-change-write(User Write Stage)',
                            // 'dpac' => 'default-provider-authorization-consent(Consent Stage)',
                            // 'dsal' => 'default-source-authentication-login(User Login Stage)',
                            // 'dsel' => 'default-source-enrollment-login(User Login Stage)',
                            // 'dsep' => 'default-source-enrollment-prompt(Prompt Stage)',
                            // 'dsew' => 'default-source-enrollment-write(User Write Stage)',
                            // 'dus' => 'default-user-settings(Prompt Stage)',
                            // 'dusw' => 'default-user-settings-write(User Write Stage)',
                            // 'dsep' => 'default-source-enrollment-prompt(Prompt Stage)',
                        // ]),

                    // ->options(function () {
                    // return Certificate::all()->pluck('common_name', 'id');
                    // }),
                    // Select::make('tlsa')
                    // ->label('TLS Authentication Certificate/SSH Keypair')
                    // ->searchable()
                    // ->options(function () {
                    // return Certificate::all()->pluck('common_name', 'id');
                    // }),

                    ])->relationship('Stages')]),
                    Step::make('Captcha Stage')
                    ->label('Create Captcha Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Captcha Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('Name')
                        ->required(),
                        TextInput::make('Public_Key')
                        ->hint('Public key, acquired from https://www.google.com/recaptcha/intro/v3.html.')
                        ->required(),
                        TextInput::make('Private_Key')
                        ->hint('Private key, acquired from https://www.google.com/recaptcha/intro/v3.html.')
                        ->required(),
                        TextInput::make('Js_url')
                        ->hint('URL to fetch JavaScript from, defaults to recaptcha. Can be replaced with any compatible alternative.')
                        ->default('https://www.recaptcha.net/recaptcha/api.js')
                        ->required(),
                        TextInput::make('Api_url')
                        ->hint('URL used to validate captcha response, defaults to recaptcha. Can be replaced with any compatible alternative.')
                        ->default('https://www.recaptcha.net/recaptcha/api/siteverify')
                        ->required(),

                    // Toggle::make('local'),
                    // \InvadersXX\FilamentJsoneditor\Forms\JSONEditor::make('kubeconfig')
                    // ->height(10),
                    // Toggle::make('vkube')->label('Verify Kubernetes API SSL Certificate'),

                    ])->relationship('Stages')]),
                    Step::make('Consent Stage')
                    ->label('Create_Consent_Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                        $radioValue = $get('type');
                        return $radioValue === 'Consent Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                    TextInput::make('type')->default('Consent Stage')->disabled(),
                    TextInput::make('Name')
                    ->required(),
                    Select::make('Mode')
                    ->options([
                        'arc' => 'Always require consent',
                        'cgli' => 'Consent given last indefinitely',
                        'ce' => 'Consent expires'
                    ])
                    ->default('arc')
                    ->required(),
                    ])->relationship('Stages')]),

                    Step::make('Deny Stage')
                    ->label('Create_Deny_Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Deny Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                    TextInput::make('type')->default('Deny Stage')->disabled(),
                    TextInput::make('Name')
                    ->required(),
                    ])->relationship('Stages')]),

                    Step::make('Duo Authenticator Setup Stage')
                    ->label('Create_Duo_Authenticator_Setup_Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Duo Authenticator Setup Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                    TextInput::make('type')->default('Duo Authenticator Setup Stage')->disabled(),
                    TextInput::make('Name')
                    ->required(),
                    TextInput::make('Authenticator_type_name'),
                    TextInput::make('Api_Hostname')
                    ->required(),
                    TextInput::make('Integration_key')
                    ->required(),
                    TextInput::make('Secret_key')
                    ->required(),
                    TextInput::make('Integration_key'),
                    TextInput::make('Secret_key'),
                    Select::make('Configuration_flow')
                    ->options([
                        'd' => 'dd'
                    ]),

                    ])->relationship('Stages')]),

                    Step::make('Email Stage')
                    ->label('Create Email Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Email Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                    TextInput::make('type')->default('Email Stage')->disabled(),
                    TextInput::make('Name')
                    ->required(),
                    Toggle::make('Activate_pending_user_on_success')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-s-user')
                    ->hint('When a user returns from the email successfully, their account will be activated.'),
                    Toggle::make('Use_global_settings')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-s-user')
                    ->hint('When enabled, global Email connection settings will be used and connection settings below will be ignored.'),
                    TextInput::make('Token_expiry')
                    ->hint('Time in minutes the token sent is valid.')
                    ->default('30')
                    ->required(),
                    TextInput::make('Subject')
                    ->default('authentik')
                    ->required(),
                    Select::make('Template')
                    ->options([
                        'pr' => 'Password Reset',
                        'ac' => 'Account Confirmation',

                    ])
                    ->default('pr')
                    ->required(),
                    ])->relationship('Stages')]),

                    Step::make('Identification Stage')
                    ->label('Create Identification Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Identification Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('Identification Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        Select::make('User_fields')
                        ->multiple()
                        ->options([
                            '1' => 'Username',
                            '2' => 'Email',
                            '3' => 'UPN'
                        ])
                        ->hint('Fields a user can identify themselves with. If no fields are selected, the user will only be able to use sources.<br>Hold control/command to select multiple items.'),
                        Select::make('Password_stage')
                        ->options([
                            '1' => 'Username',
                            '2' => 'Email',
                            '3' => 'UPN'
                        ])
                        ->hint('When selected, a password field is shown on the same page instead of a separate page. This prevents username enumeration attacks.'),
                        Toggle::make('Case_insensitive_matching')
                        ->onIcon('heroicon-m-bolt')
                        ->offIcon('heroicon-s-user')
                        ->hint('When enabled, user fields are matched regardless of their casing.'),
                        Toggle::make('Show_matched_user')
                        ->onIcon('heroicon-m-bolt')
                        ->offIcon('heroicon-s-user')
                        ->hint("When a valid username/email has been entered, and this option is enabled, the user's username and avatar will be shown. Otherwise, the text that the user entered will be shown."),
                        Select::make('Sources')
                        ->multiple()
                        ->options([
                            '1' => 'Username',
                            '2' => 'Email',
                            '3' => 'UPN'
                        ])
                        ->hint("Select sources should be shown for users to authenticate with. This only affects web-based sources, not LDAP.<br>Hold control/command to select multiple items.")
                        ->required(),

                        Toggle::make("Show_sources_labels")
                        ->onIcon('heroicon-m-bolt')
                        ->offIcon('heroicon-s-user')
                        ->hint("By default, only icons are shown for sources. Enable this to show their full names."),
                        Select::make('Passwordless_flow')
                        ->options([
                            '1' => 'Username',
                            '2' => 'Email',
                            '3' => 'UPN'
                        ])
                        ->hint("Optional passwordless flow, which is linked at the bottom of the page. When configured, users can use this flow to authenticate with a WebAuthn authenticator, without entering any details."),
                        Select::make('Enrollment_flow')
                        ->options([
                            '1' => 'Username',
                            '2' => 'Email',
                            '3' => 'UPN'
                        ])
                        ->hint("Optional enrollment flow, which is linked at the bottom of the page."),
                        Select::make('Recovery_flow')
                        ->options([
                            '1' => 'Username',
                            '2' => 'Email',
                            '3' => 'UPN'
                        ])
                        ->hint("Optional recovery flow, which is linked at the bottom of the page."),
                        ])->relationship('Stages')]),

                    Step::make('Invitation Stage')
                    ->label('Create Invitation Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Invitation Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('Invitation Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        Toggle::make('Continue_flow_without_invitation')
                        ->onIcon('heroicon-m-bolt')
                        ->offIcon('heroicon-s-user')
                        ->hint('If this flag is set, this Stage will jump to the next Stage when no Invitation is given. By default this Stage will cancel the Flow when no invitation is given.'),
                        ])->relationship('Stages')]),

                    Step::make('Password Stage')
                    ->label('Create Password Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Password Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('Password Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        Select::make('Backends')
                        ->required()
                        ->multiple()
                        ->options([
                            '1' => 'User database + standard password',
                            '2' => 'User database + app passwords',
                            '3' => 'User database + LDAP passwords'
                        ])
                        ->default('1')
                        ->hint('Selection of backends to test the password against.<br>Hold control/command to select multiple items.'),
                        Select::make('Configuration_flow ')
                        ->required()
                        ->options([
                            'dpc' => 'default-password-change (Change Password)',
                            'ua' => 'User database + app passwords',
                            'ul' => 'User database + LDAP passwords'
                        ])
                        ->default('dpc')
                        ->hint('Flow used by an authenticated user to configure their password. If empty, user will not be able to configure change their password.'),
                        TextInput::make('Failed_attempts_before_cancel ')
                        ->required()
                        ->default('5')
                        ->hint('How many attempts a user has before the flow is canceled. To lock the user out, use a reputation policy and a user_write stage.'),
                        ])->relationship('Stages')]),

                    Step::make('Prompt Stage')
                    ->label('Create Prompt Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Prompt Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('Prompt Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        Select::make('Fields')
                        ->required()
                        ->multiple()
                        ->options([
                            '1' => '1',
                            '2' => '2',
                            '3' => '3'
                        ])
                        ->default('1')
                        ->hint('Hold control/command to select multiple items.'),
                        Select::make('Validation_Policies')
                        ->multiple()
                        ->options([
                            '1' => '1',
                            '2' => '2',
                            '3' => '3'
                        ])
                        ->default('1')
                        ->hint('Selected policies are executed when the stage is submitted to validate the data.<br>Hold control/command to select multiple items.'),


                        ])->relationship('Stages')]),

                    Step::make('SMS Authenticator Setup Stage')
                    ->label('Create SMS Authenticator Setup Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'SMS Authenticator Setup Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('SMS Authenticator Setup Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        TextInput::make('Authenticator_type_name')
                        ->hint('Display name of this authenticator, used by users when they enroll an authenticator.'),
                        Select::make('Provider')
                        ->required()
                        ->options([
                            '1' => 'Twillo',
                            '2' => 'Generic'
                        ])
                        ->default('1'),
                        TextInput::make('From_number')
                        ->required()
                        ->hint('Number the SMS will be sent from.'),
                        TextInput::make('Twilio_Account_Sid')
                        ->required()
                        ->hint('Get this value from https://console.twilio.com'),
                        TextInput::make('Twilio_Auth_Token')
                        ->required()
                        ->hint('Get this value from https://console.twilio.com'),
                        Toggle::make('Hash_phone_number')
                        ->hint('If enabled, only a hash of the phone number will be saved. This can be done for data-protection reasons. Devices created from a stage with this enabled cannot be used with the authenticator validation stage.'),
                        Select::make('Configuration_flow')
                        ->required()
                        ->options([
                            'Duo Authenticator Setup Stage' => 'default-authenticator-static-setup (default-authenticator-static-setup)',
                            '2' => 'User database + app passwords',
                            '3' => 'User database + LDAP passwords'
                        ])
                        ->default('Duo Authenticator Setup Stage')
                        ->hint('Flow used by an authenticated user to configure this Stage. If empty, user will not be able to configure this stage.'),
                        ])->relationship('Stages')]),

                    Step::make('Static Authenticator Stage')
                    ->label('Create Static Authenticator Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'Static Authenticator Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('Static Authenticator Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        TextInput::make('Authenticator_type_name')
                        ->hint('Display name of this authenticator, used by users when they enroll an authenticator.'),
                        TextInput::make('Token_count')
                        ->required()
                        ->default('6'),
                        Select::make('Configuration_flow')
                        ->options([
                            '4' => '1',
                            '5' => 'User database + app passwords',
                            '6' => 'User database + LDAP passwords'
                        ])
                        ->hint('Flow used by an authenticated user to configure this Stage. If empty, user will not be able to configure this stage.'),
                        ])->relationship('Stages')]),



                    Step::make('TOTP Authenticator Setup Stage')
                    ->label('Create TOTP Authenticator Setup Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'TOTP Authenticator Setup Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('TOTP Authenticator Setup Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        TextInput::make('Authenticator_type_name')
                        ->hint('Display name of this authenticator, used by users when they enroll an authenticator.'),
                        Select::make('Digits')
                        ->required()
                        ->options([
                            '1' => '6 digits,widely compatible',
                            '2' => '8 digits,not compatible with apps like Google Authenticator'
                        ])
                        ->default('1'),
                        Select::make('Configuration_flow')
                        ->options([
                            '7' => '1',
                            '8' => 'User database + app passwords',
                            '9' => 'User database + LDAP passwords'
                        ])
                        ->hint('Flow used by an authenticated user to configure this Stage. If empty, user will not be able to configure this stage.'),
                        ])->relationship('Stages')]),




                    Step::make('User Delete Stage')
                    ->label('Create User Delete Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'User Delete Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('User Delete Stage')->disabled(),
                        TextInput::make('Name')
                        ->required()
                        ])->relationship('Stages')]),

                    Step::make('User Login Stage')
                    ->label('Create User Login Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'User Login Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('User Login Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        TextInput::make('Session_duration')
                        ->required()
                        ->default('seconds=0')
                        ->hint('Determines how long a session lasts. Default of 0 seconds means that the sessions lasts until the browser is closed.<br>(Format: hours=1;minutes=2;seconds=3).'),
                        TextInput::make('Stay_signed_in_offset')
                        ->required()
                        ->default('seconds=0')
                        ->hint('If set to a duration above 0, the user will have the option to choose to "stay signed in", which will extend their session by the time specified here.<br>(Format: hours=1;minutes=2;seconds=3).'),
                        Toggle::make('Terminate_other_sessions')
                        ->hint('When enabled, all previous sessions of the user will be terminated.'),
                        ])->relationship('Stages')]),

                    Step::make( 'User Logout Stage')
                    ->label('Create User Logout Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue ===  'User Logout Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('User Logout Stage')->disabled(),
                        TextInput::make('Name')
                        ->required()
                        ])->relationship('Stages')]),

                    Step::make('User Write Stage')
                    ->label('Create User Write Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'User Write Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('User Write Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        Radio::make('user_status')
                        ->options([
                            'ncu' => 'Never create users',
                            'cuwr' => 'Create users when required',
                            'acnu' => 'Always create new users'

                        ])
                        ->descriptions([
                            'ncu' => 'When no user is present in the flow context, the stage will fail.',
                            'cuwr' => 'When no user is present in the the flow context, a new user is created.',
                            'acnu' => 'Create a new user even if a user is in the flow context.'
                        ]),
                        Toggle::make('Create_users_as_inactive')
                        ->hint('Mark newly created users as inactive.'),
                        TextInput::make('User_path_template')
                        ->hint('Path new users will be created under. If left blank, the default path will be used.'),
                        Select::make('Group')
                        ->hint('Newly created users are added to this group, if a group is selected.')
                    //make relationship with group
                    ])->relationship('Stages')]),

                    Step::make('WebAuthn Authenticator Setup Stage')
                    ->label('Create WebAuthn Authenticator Setup Stage')
                    ->when(function (\Filament\Forms\Get $get) {
                    $radioValue = $get('type');
                    return $radioValue === 'WebAuthn Authenticator Setup Stage';})
                    ->schema([
                        Section::make('')
                        ->schema([
                        TextInput::make('type')->default('WebAuthn Authenticator Setup Stage')->disabled(),
                        TextInput::make('Name')
                        ->required(),
                        TextInput::make('Authenticator_type_name')
                        ->hint('Display name of this authenticator, used by users when they enroll an authenticator.'),
                        Radio::make('User_verification')
                        ->options([
                            'uvmo' => 'User verification must occur.',
                            'uvar' => 'User verification is preferred if available, but not required.',
                            'uvso' => 'User verification should not occur.'

                        ])
                        ->required(),
                        Radio::make('Resident_key_requirement')
                        ->options([
                            'tascdc' => 'The authenticator should not create a dedicated credential',
                            'tacsdc' => "The authenticator can create and store a dedicated credential, but if it doesn't that's alright too",
                            'tacdc' => 'The authenticator MUST create a dedicated credential. If it cannot, the RP is prepared for an error to occur'

                        ])
                        ->required(),
                        Radio::make('Authenticator_Attachment')
                        ->options([
                            'nps' => 'No preference is sent',
                            'nra' => 'A non-removable authenticator, like TouchID or Windows Hello',
                            'ra' => 'A "roaming" authenticator, like a YubiKey'

                        ])
                        ->required(),
                        Select::make('Configuration_flow')
                        //foreignkey
                        ->hint('Flow used by an authenticated user to configure this Stage. If empty, user will not be able to configure this stage.'),
                        ])->relationship('Stages')]),
                    Step::make('cb')
                    ->label('Create binding')

                    ->schema([
                        TextInput::make('stage')
                        ->required(),
                        TextInput::make('order'),
                        Toggle::make('Evaluate_when_flow_is_planned'),
                        Toggle::make('Evaluate_when_stage_is_run'),
                        Radio::make('Invalid_response_behavior')
                        ->options([
                            'RETRY' => 'User verification must occur.',
                            'RESTART' => 'User verification is preferred if available, but not required.',
                            'RESTART_WITH_CONTEXT' => 'User verification should not occur.'

                        ])
                        ->required(),
                        Radio::make('Policy_engine_mode')
                        ->options([
                            'any' => 'any',
                            'all' => 'all'
                        ])
                        ->required(),


                    ])


                    ])
                ])->columns(1)

                    ;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Stages.Name'),
                Tables\Columns\TextColumn::make('Stages.type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
