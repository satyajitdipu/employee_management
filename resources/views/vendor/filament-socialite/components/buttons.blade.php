@if (count($providers))
    <div class="grid login-page-button lg:w-full ">
        @foreach ($providers as $key => $provider)
                <x-filament::button tag="a" :href="route('socialite.app.oauth.redirect', $key)" class="bg-transparent shadow-none" :color="$provider['backgroundColor'] ?? 'transparent'"
                    style=" box-shadow: none;">
                    
                    <div class="flex flex-row justify-center cursor-pointer items-center min-h-0 hover-effect  ">
    
                        <span class="xl:whitespace-nowrap xl:text-lg lg:whitespace-normal mr-2 xxs:text-base xs:text-xl sm:text-xl text-{{ $provider['color'] ?? 'transparent' }} font-semibold email-login ">
                            {{ $provider['label'] ?? null }}
                        </span>
                        @isset($provider['icon'])
                            <x-filament::icon-button :icon="$provider['icon']" :color="$provider['iconColor'] ?? null" size='lg'
                                style="box-shadow: none !important;"
                                class="{{`bg-{$provider['iconBackgroundColor']}` ?? 'bg-core-750'}} btn-cmn" />
                        @endisset
                    </div>
            </x-filament::button>
        @endforeach
    </div>
    <span class="text-center lg:mt-2 lg:mb-6 sm:mt-1 sm:mb-5 mb-5 text-gray-750 font-[500]">or </span>
@else
    <span></span>
@endif
