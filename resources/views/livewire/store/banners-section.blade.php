{{-- Single Root Element Required by Livewire v3 --}}
<div>
    @if($banners->count() > 0)
    {{-- Promotional Banners Section --}}
    <div class="py-12 bg-white">
        <div class="container mx-auto px-4">
        {{-- Single Banner Layout --}}
        @if($banners->count() === 1)
        <div class="relative rounded-xl overflow-hidden shadow-lg group">
            <a href="{{ $banners->first()->link_url ?? '#' }}" class="block">
                <img 
                    src="{{ asset('storage/' . $banners->first()->image_path) }}" 
                    alt="{{ $banners->first()->title }}"
                    class="w-full h-64 md:h-96 object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div class="absolute inset-0 bg-gradient-to-r from-black/30 to-transparent"></div>
            </a>
        </div>
        
        {{-- Two Banners Layout --}}
        @elseif($banners->count() === 2)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($banners as $banner)
            <div class="relative rounded-xl overflow-hidden shadow-lg group">
                <a href="{{ $banner->link_url ?? '#' }}" class="block">
                    <img 
                        src="{{ asset('storage/' . $banner->image_path) }}" 
                        alt="{{ $banner->title }}"
                        class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    @if($banner->title)
                    <div class="absolute bottom-4 left-4 right-4">
                        <h3 class="text-white font-bold text-xl">{{ $banner->title }}</h3>
                    </div>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        
        {{-- Three Banners Layout --}}
        @elseif($banners->count() === 3)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($banners as $banner)
            <div class="relative rounded-xl overflow-hidden shadow-lg group">
                <a href="{{ $banner->link_url ?? '#' }}" class="block">
                    <img 
                        src="{{ asset('storage/' . $banner->image_path) }}" 
                        alt="{{ $banner->title }}"
                        class="w-full h-48 md:h-64 object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-violet-900/60 to-transparent"></div>
                    @if($banner->title)
                    <div class="absolute bottom-4 left-4 right-4 text-center">
                        <h3 class="text-white font-semibold text-lg">{{ $banner->title }}</h3>
                    </div>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        
        {{-- Four+ Banners Grid Layout --}}
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($banners as $banner)
            <div class="relative rounded-xl overflow-hidden shadow-lg group">
                <a href="{{ $banner->link_url ?? '#' }}" class="block">
                    <img 
                        src="{{ asset('storage/' . $banner->image_path) }}" 
                        alt="{{ $banner->title }}"
                        class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    @if($banner->title)
                    <div class="absolute bottom-3 left-3 right-3 text-center">
                        <h3 class="text-white font-semibold text-sm">{{ $banner->title }}</h3>
                    </div>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    </div>
    @endif
</div>
