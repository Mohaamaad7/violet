<div class="py-12 bg-white min-h-screen">
    <div class="container mx-auto px-4">
        {{-- Breadcrumb --}}
        <nav class="flex mb-8 text-gray-500 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 {{ app()->getLocale() === 'ar' ? 'space-x-reverse' : '' }}">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center hover:text-violet-600 transition-colors">
                        <svg class="w-4 h-4 mr-2 ml-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        {{ __('store.nav.home') ?? 'Home' }}
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-gray-700 font-medium md:ml-2">{{ $category->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title --}}
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 section-header mb-4">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">{{ $category->description }}</p>
            @endif
        </div>

        {{-- Sub-Categories Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8">
            @foreach($children as $child)
                <a href="{{ route('category.show', $child->slug) }}" class="group block text-center">
                    <div class="relative overflow-hidden rounded-full aspect-square mb-6 shadow-sm group-hover:shadow-lg transition-all duration-300 border border-gray-100 bg-gray-50 transform group-hover:-translate-y-1">
                        @php
                            $imageUrl = $child->getFirstMediaUrl('category-images', 'card');
                            if (!$imageUrl) {
                                $imageUrl = asset('images/default-category.png'); 
                            }
                        @endphp
                        
                        @if($child->hasMedia('category-images'))
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $child->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-violet-50 text-violet-300 group-hover:text-violet-500 transition-colors">
                                @if($child->icon && Str::startsWith($child->icon, 'heroicon'))
                                    @svg($child->icon, 'w-16 h-16')
                                @else
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                @endif
                            </div>
                        @endif
                    </div>
                    
                    <h3 class="font-bold text-xl text-gray-900 group-hover:text-violet-700 transition font-heading">
                        {{ $child->name }} &rarr;
                    </h3>
                    @if($child->products_count > 0 || $child->children_count > 0)
                        <p class="text-sm text-gray-400 mt-1">
                            {{ $child->products_count }} {{ app()->getLocale() === 'ar' ? 'منتج' : 'Products' }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
