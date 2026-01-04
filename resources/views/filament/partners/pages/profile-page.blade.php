<div class="max-w-7xl mx-auto space-y-6" x-data="{ showDebug: false }">
    
    <!-- Debug Panel (for testing) -->
    <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">وضع الاختبار - للتحقق من عمل الإشعارات</span>
            </div>
            <button @click="showDebug = !showDebug" class="text-yellow-600 hover:text-yellow-800">
                <span x-text="showDebug ? 'إخفاء' : 'عرض'"></span>
            </button>
        </div>
        <div x-show="showDebug" x-cloak class="mt-3 space-y-3 text-sm text-yellow-700 dark:text-yellow-300">
            <p>✓ Livewire: <span x-text="typeof Livewire !== 'undefined' ? 'محمل' : 'غير محمل'"></span></p>
            <p>✓ Alpine.js: <span x-text="typeof Alpine !== 'undefined' ? 'محمل' : 'غير محمل'"></span></p>
            <div class="pt-2 border-t border-yellow-300 dark:border-yellow-700">
                <button type="button" 
                        wire:click="testNotification"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 active:bg-yellow-800 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                    🔔 اختبار الإشعارات
                </button>
            </div>
        </div>
    </div>
    
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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        صورة الملف الشخصي
                    </h3>
                    
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
                                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">رقم الهاتف:</span>
                                        <span>{{ $influencer->phone ?? 'غير محدد' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">تاريخ الانضمام:</span>
                                        <span>{{ $influencer->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Info & Social Media -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Personal Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        المعلومات الشخصية
                    </h3>
                    
                    <form class="space-y-4" wire:submit.prevent="updateProfile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    الاسم الكامل
                                </label>
                                <input type="text" 
                                       value="{{ auth()->user()->name }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500 focus:ring-opacity-20 transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    البريد الإلكتروني
                                </label>
                                <input type="email" 
                                       value="{{ auth()->user()->email }}"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white bg-gray-50 dark:bg-gray-900 cursor-not-allowed"
                                       disabled>
                            </div>
                            
                            @if($influencer)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        رقم الهاتف
                                    </label>
                                    <input type="tel" 
                                           value="{{ $influencer->phone }}"
                                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500 focus:ring-opacity-20 transition">
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" 
                                    class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition shadow-lg shadow-violet-500/20">
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        تغيير كلمة المرور
                    </h3>
                    
                    <form class="space-y-4" wire:submit.prevent="updatePassword">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Current Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    كلمة المرور الحالية
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           wire:model="currentPassword"
                                           placeholder="أدخل كلمة المرور الحالية"
                                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500 focus:ring-opacity-20 transition">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- New Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    كلمة المرور الجديدة
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           wire:model="newPassword"
                                           placeholder="أدخل كلمة المرور الجديدة (8 أحرف على الأقل)"
                                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500 focus:ring-opacity-20 transition">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    تأكيد كلمة المرور الجديدة
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           wire:model="newPasswordConfirmation"
                                           placeholder="أعد إدخال كلمة المرور الجديدة"
                                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500 focus:ring-opacity-20 transition">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">متطلبات كلمة المرور</h4>
                                    <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
                                        <li>• يجب أن تكون 8 أحرف على الأقل</li>
                                        <li>• يُفضل استخدام مزيج من الأحرف والأرقام والرموز</li>
                                        <li>• تجنب استخدام معلومات شخصية واضحة</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" 
                                    class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition shadow-lg shadow-violet-500/20">
                                تحديث كلمة المرور
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Social Media Accounts -->
                @if($influencer)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        حسابات التواصل الاجتماعي
                    </h3>
                    
                    <form class="space-y-5" wire:submit.prevent="updateSocialMedia">
                        
                        <!-- Instagram -->
                        <div class="flex items-start gap-4">
                            <div class="mt-2 p-2.5 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 rounded-xl flex-shrink-0">
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
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500 focus:ring-opacity-20 transition">
                            </div>
                        </div>

                        <!-- TikTok -->
                        <div class="flex items-start gap-4">
                            <div class="mt-2 p-2.5 bg-black rounded-xl flex-shrink-0">
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
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-gray-900 focus:ring-2 focus:ring-gray-900 focus:ring-opacity-20 transition">
                            </div>
                        </div>

                        <!-- Twitter/X -->
                        <div class="flex items-start gap-4">
                            <div class="mt-2 p-2.5 bg-black rounded-xl flex-shrink-0">
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
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-gray-900 focus:ring-2 focus:ring-gray-900 focus:ring-opacity-20 transition">
                            </div>
                        </div>

                        <!-- YouTube -->
                        <div class="flex items-start gap-4">
                            <div class="mt-2 p-2.5 bg-red-600 rounded-xl flex-shrink-0">
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
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-600 focus:ring-2 focus:ring-red-600 focus:ring-opacity-20 transition">
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" 
                                    class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition shadow-lg shadow-violet-500/20">
                                حفظ حسابات التواصل
                            </button>
                        </div>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Listen for password change event and logout/redirect
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('password-changed', () => {
            // Wait 3 seconds then logout (using form POST)
            setTimeout(() => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('filament.partners.auth.logout') }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }, 3000);
        });
    });
</script>
@endpush
