<div class="bg-white w-[100vw] h-[100vh] flex-wrap absolute top-0 left-0 ">
    <div class="xxs:h-[16%] md:h-[8%] flex align-middle w-screen justify-between items-center bg-[#FCFCFC] lg:bg-white">
        <div class="basis-[50%] !xs:basis-[100%] lg:bg-[#FCFCFC] pl-[10%] h-full items-center flex">
            <!-- Centered image -->
            <img src="{{ url('assets/CookieLogo.svg') }}" class="md:block xxs:hidden" alt="image" />
        </div>
        <div class="">
            <!-- Centered image -->
            <img src="{{ url('assets/loginBannerGroup.svg') }}" class="xxs:hidden md:block h-14" alt="image" />
        </div>

    </div>

    <div class="lg:flex justify-center align-middle xxs:h-[95%] md:h-[100%] lg:h-[90%] xl:h-[90%] xxl:h-[90%] overflow-hidden relative">
        <div class="flex basis-[130%] bg-[#FCFCFC] items-center sm:justify-center justify-center lg:w-[60%] lg:pl-[0%] box-border">
            <img src="{{ url('assets/login_svg.svg') }}" class="relative lg:left-[16%] w-[30%] sm:items-start sm:pl-[5%] lg:w-[48%]  md:pr-[2%] md:pb-[5%]  md:w-[50%] sm:w-[40%]" alt="image" />
        </div>
        <div class="flex items-center flex-col justify-center basis-[130%] sm:justify-center ">
            <div class="component sm:w-[50%] w-[70%] lg:mr-[25%]">
                <div class="flex items-center justify-center ">
                    <div>
                        <h2 class="text-4xl text-center font-semibold">
                            Welcome to
                        </h2>
                        <h2 class="text-3xl text-center mb-4 font-semibold">
                            Cranberry Cookie!
                        </h2>
                    </div>
                </div>
                <div class="flex flex-col items-center justify-center ">
                    <div class="flex flex-col w-full socialite-button ">
                        <x-filament-socialite::buttons />
                    </div>
                </div>
                <x-filament-panels::form wire:submit="authenticate" class="star-hidden">
                    {{ $this->form }}
                    <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
                </x-filament-panels::form>

            </div>
        </div>
        <div class="absolute flex justify-end bottom-0 right-0">
            <img src="{{ url('assets/loginBannerGroup.svg') }}" class="xxs:hidden md:block h-14" alt="image" />
        </div>
    </div>
</div>