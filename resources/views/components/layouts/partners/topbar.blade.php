{{-- Partners Topbar --}}
<div class="fi-topbar-ctn">
    <nav class="fi-topbar">
        {{-- Mobile Menu Buttons --}}
        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-bars-3"
            icon-size="lg"
            label="فتح القائمة"
            x-cloak
            x-on:click="sidebarOpen = true"
            x-show="! sidebarOpen"
            class="fi-topbar-open-sidebar-btn lg:hidden"
        />

        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-x-mark"
            icon-size="lg"
            label="إغلاق القائمة"
            x-cloak
            x-on:click="sidebarOpen = false"
            x-show="sidebarOpen"
            class="fi-topbar-close-sidebar-btn lg:hidden"
        />

        {{-- Topbar Start (Heading) --}}
        <div class="fi-topbar-start">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $heading ?? __('messages.partners.dashboard.title') }}
            </h1>
        </div>

        {{-- Topbar End (User Menu) --}}
        <div class="fi-topbar-end">
            <x-filament::dropdown
                placement="bottom-start"
                width="xs"
                teleport
            >
                <x-slot name="trigger">
                    <button
                        type="button"
                        class="shrink-0"
                        aria-label="قائمة المستخدم"
                    >
                        <x-filament::avatar
                            :src="filament()->getUserAvatarUrl(auth()->user())"
                            :alt="filament()->getUserName(auth()->user())"
                            size="md"
                        />
                    </button>
                </x-slot>

                {{-- User Info Header --}}
                <x-filament::dropdown.header class="!p-4">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ filament()->getUserName(auth()->user()) }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ auth()->user()->email }}
                        </span>
                    </div>
                </x-filament::dropdown.header>

                {{-- Menu Items --}}
                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item
                        :href="route('filament.partners.pages.profile-page')"
                        tag="a"
                        icon="heroicon-o-user"
                    >
                        {{ __('messages.partners.nav.profile') }}
                    </x-filament::dropdown.list.item>

                    <x-filament::dropdown.list.item
                        tag="form"
                        :action="route('filament.partners.auth.logout')"
                        method="post"
                        icon="heroicon-o-arrow-right-on-rectangle"
                        color="danger"
                    >
                        {{ __('messages.partners.nav.logout') }}
                    </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>
    </nav>
</div>
