@props(['items' => []])

{{-- 
    Breadcrumbs Component
    
    Usage:
    <x-store.breadcrumbs :items="[
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Products', 'url' => '/products'],
        ['label' => 'Product Name', 'url' => null] // Current page (no URL)
    ]" />
--}}

@if(count($items) > 0)
<nav aria-label="Breadcrumb" class="bg-cream-100 border-b border-cream-200">
    <div class="container mx-auto px-4 py-3">
        <ol class="flex items-center flex-wrap gap-2 text-sm">
            {{-- Home Link --}}
            <li class="flex items-center">
                <a href="/" class="text-gray-600 hover:text-violet-600 transition flex items-center gap-1.5 group">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="group-hover:underline">Home</span>
                </a>
            </li>

            {{-- Breadcrumb Items --}}
            @foreach($items as $index => $item)
                <li class="flex items-center gap-2">
                    {{-- Separator --}}
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                    {{-- Breadcrumb Link or Text --}}
                    @if(isset($item['url']) && $item['url'])
                        <a href="{{ $item['url'] }}" class="text-gray-600 hover:text-violet-600 hover:underline transition">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-gray-900 font-medium" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
@endif
