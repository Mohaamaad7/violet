<div>
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.partners.nav.profile') }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">إدارة معلوماتك الشخصية وحساباتك على وسائل التواصل الاجتماعي</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <x-filament::section>
                    <x-slot name="heading">
                        صورة الملف الشخصي
                    </x-slot>
                    
                    <div class="text-center">
                        <div class="inline-block relative">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ asset('storage/' . auth()->user()->avatar_url) }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-violet-500">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center border-4 border-white dark:border-gray-800">
                                    <span class="text-4xl font-bold text-white">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Edit Button -->
                            <button type="button" class="absolute bottom-0 right-0 bg-violet-600 hover:bg-violet-700 text-white rounded-full p-2 shadow-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        
                        @php
                            $influencer = auth()->user()->influencer;
                        @endphp
                        
                        @if($influencer)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <p><span class="font-medium">رقم الهاتف:</span> {{ $influencer->phone ?? 'غير محدد' }}</p>
                                    <p class="mt-1"><span class="font-medium">تاريخ الانضمام:</span> {{ $influencer->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-filament::section>
            </div>

            <!-- Main Info & Social Media -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Personal Information -->
                <x-filament::section>
                    <x-slot name="heading">
                        المعلومات الشخصية
                    </x-slot>
                    
                    <form class="space-y-4" wire:submit.prevent="updateProfile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    الاسم الكامل
                                </label>
                                <input type="text" 
                                       value="{{ auth()->user()->name }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    البريد الإلكتروني
                                </label>
                                <input type="email" 
                                       value="{{ auth()->user()->email }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                                       disabled>
                            </div>
                            
                            @if($influencer)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        رقم الهاتف
                                    </label>
                                    <input type="tel" 
                                           value="{{ $influencer->phone }}"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button type="submit" 
                                    class="px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition">
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </x-filament::section>

                <!-- Social Media Accounts -->
                @if($influencer)
                <x-filament::section>
                    <x-slot name="heading">
                        حسابات التواصل الاجتماعي
                    </x-slot>
                    
                    <form class="space-y-4" wire:submit.prevent="updateSocialMedia">
                        
                        <!-- Instagram -->
                        <div class="flex items-start gap-3">
                            <div class="mt-2 p-2 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Instagram
                                </label>
                                <input type="text" 
                                       value="{{ $influencer->instagram_handle }}"
                                       placeholder="@username"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                            </div>
                        </div>

                        <!-- TikTok -->
                        <div class="flex items-start gap-3">
                            <div class="mt-2 p-2 bg-black rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    TikTok
                                </label>
                                <input type="text" 
                                       value="{{ $influencer->tiktok_handle }}"
                                       placeholder="@username"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                            </div>
                        </div>

                        <!-- Twitter/X -->
                        <div class="flex items-start gap-3">
                            <div class="mt-2 p-2 bg-black rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    X (Twitter)
                                </label>
                                <input type="text" 
                                       value="{{ $influencer->twitter_handle }}"
                                       placeholder="@username"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                            </div>
                        </div>

                        <!-- YouTube -->
                        <div class="flex items-start gap-3">
                            <div class="mt-2 p-2 bg-red-600 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    YouTube
                                </label>
                                <input type="text" 
                                       value="{{ $influencer->youtube_handle }}"
                                       placeholder="@username"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button type="submit" 
                                    class="px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition">
                                حفظ حسابات التواصل
                            </button>
                        </div>
                    </form>
                </x-filament::section>
                @endif

            </div>
        </div>
    </div>
</div>
