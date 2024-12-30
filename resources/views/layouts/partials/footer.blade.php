<footer class="text-center print:hidden text-neutral-600 dark:text-neutral-200 lg:text-left">
  

    <!-- Middle Section: Main Content -->
    <div class="px-2 py-5 text-center md:text-left bg-neutral-100 dark:bg-neutral-600">
        <div class="grid gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
            <!-- Logo Section -->
            <div class="flex justify-center md:justify-start">
                <a href="{{ route('home') }}" aria-label="Go to Home" title="Home">
                    <x-svg.genealogy class="size-48 fill-dark dark:fill-neutral-400 hover:fill-primary-300 dark:hover:fill-primary-300" alt="Genealogy Logo" />
                </a>
            </div>

            <!-- Useful Links Section -->
            <div>
                <h6 class="flex justify-center mb-4 font-semibold uppercase md:justify-start">{{ __('app.useful_links') }}</h6>
                <x-hr.narrow class="w-48 h-1 my-4 bg-gray-100 border-0 rounded max-md:mx-auto dark:bg-gray-700" />
                <p class="mb-4">
                    <x-nav-link-footer href="{{ route('about') }}" :active="request()->routeIs('about')">
                        {{ __('app.about') }}
                    </x-nav-link-footer>
                </p>
                <p class="mb-4">
                    <x-nav-link-footer href="{{ route('help') }}" :active="request()->routeIs('help')">
                        {{ __('app.help') }}
                    </x-nav-link-footer>
                </p>
            </div>

            <!-- Impressum Section -->
            <div>
                <h6 class="flex justify-center mb-4 font-semibold uppercase md:justify-start">{{ __('app.impressum') }}</h6>
                <x-hr.narrow class="w-48 h-1 my-4 bg-gray-100 border-0 rounded max-md:mx-auto dark:bg-gray-700" />
                <p class="mb-4">
                    <x-nav-link-footer href="{{ url('terms-of-service') }}" :active="request()->is('terms-of-service')">
                        {{ __('app.terms_of_service') }}
                    </x-nav-link-footer>
                </p>
                <p class="mb-4">
                    <x-nav-link-footer href="{{ url('privacy-policy') }}" :active="request()->is('privacy-policy')">
                        {{ __('app.privacy_policy') }}
                    </x-nav-link-footer>
                </p>
            </div>

            <!-- Contact Section -->
            <div>
                <h6 class="flex justify-center mb-4 font-semibold uppercase md:justify-start">{{ __('app.contact') }}</h6>
                <x-hr.narrow class="w-48 h-1 my-4 bg-gray-100 border-0 rounded max-md:mx-auto dark:bg-gray-700" />

                <p class="flex items-center justify-center mb-4 md:justify-start">
                    <x-ts-icon icon="mail" class="mr-3 inline-block size-5" />
                    tom@littledatacompany.com
                </p>

            </div>
        </div>
    </div>

    <!-- Bottom Section: Copyright -->
    @include('layouts.partials.copyright')
</footer>
