{{-- Single root element required by Livewire --}}
<div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
    {{-- LEFT COLUMN: Amazon-Style Image Gallery --}}
    <div class="space-y-4" 
         x-data="imageGallery()" 
         x-init="init()"
    >
        {{-- Amazon-Style Layout: Thumbnails on Left, Main Image on Right --}}
        <div class="flex gap-4">
            {{-- Thumbnails Column (Amazon-style vertical layout) --}}
            @php
                $allMedia = $product->getMedia('product-images');
            @endphp
            @if($allMedia->count() > 0)
                <div class="flex flex-col gap-2 w-16 sm:w-20">
                    @foreach($allMedia as $index => $media)
                        <button 
                            type="button"
                            @click="changeImage({{ $index }}, '{{ $media->getUrl() }}')"
                            class="aspect-square bg-white rounded border-2 transition-all duration-200 overflow-hidden focus:outline-none"
                            :class="currentIndex === {{ $index }} ? 'border-orange-500 ring-2 ring-orange-200' : 'border-gray-300 hover:border-orange-400'"
                        >
                            <img 
                                src="{{ $media->hasGeneratedConversion('thumbnail') ? $media->getUrl('thumbnail') : $media->getUrl() }}" 
                                alt="Thumbnail {{ $loop->iteration }}"
                                class="w-full h-full object-cover"
                            >
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Main Image Container --}}
            <div class="flex-1 relative">
                {{-- Sale Badge --}}
                @if($product->is_on_sale)
                    <div class="absolute top-4 left-4 z-10">
                        <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow-lg">
                            -{{ $product->discount_percentage }}%
                        </span>
                    </div>
                @endif

                {{-- Main Image with Drift Zoom (Amazon-Style Constrained) --}}
                {{-- RTL Fix: Added dir="ltr" to zoom-container to ensure Drift.js coordinate calculations are consistent --}}
                <div class="relative bg-white rounded-lg border border-gray-300 overflow-visible">
                    <div id="zoom-container" dir="ltr" class="aspect-square flex items-center justify-center p-8 relative overflow-hidden">
                        <img 
                            id="product-main-image"
                            :src="currentImageUrl"
                            :data-zoom="currentImageUrl"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-contain cursor-crosshair"
                            @click="openLightbox()"
                        >
                    </div>
                </div>

                {{-- Hover to Zoom Hint --}}
                <div class="mt-2 text-center text-sm text-gray-500">
                    <i class="fas fa-search-plus mr-1"></i>
                    Hover to zoom, click to open full gallery
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js Component for Gallery Logic --}}
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.imageGallery = function() {
                return {
                    currentIndex: 0,
                    currentImageUrl: '{{ $allMedia->first() ? $allMedia->first()->getUrl() : asset("images/default-product.png") }}',
                    driftInstance: null,
                    gallery: @json($allMedia->map(function($media) {
                        return [
                            'src' => $media->getUrl(),
                            'title' => $media->name ?? ''
                        ];
                    })->values()),
                    hasImages: {{ $allMedia->isEmpty() ? 'false' : 'true' }},

                    init() {
                        console.log('Gallery init - Drift available:', !!window.Drift);
                        // تأخير صغير للتأكد من تحميل كل شيء
                        this.$nextTick(() => {
                            setTimeout(() => this.initDriftZoom(), 300);
                        });
                    },

                    initDriftZoom() {
                        if (!this.hasImages || !window.Drift) {
                            console.log('Skipping Drift - hasImages:', this.hasImages, 'Drift available:', !!window.Drift);
                            return;
                        }
                        
                        const mainImage = document.getElementById('product-main-image');
                        const zoomContainer = document.getElementById('zoom-container');
                        
                        if (!mainImage || !zoomContainer) {
                            console.log('Missing elements - image:', !!mainImage, 'container:', !!zoomContainer);
                            return;
                        }

                        // تدمير النسخة القديمة
                        if (this.driftInstance) {
                            try {
                                this.driftInstance.destroy();
                            } catch (e) {}
                            this.driftInstance = null;
                        }

                        try {
                            console.log('Initializing Drift zoom...');
                            console.log('Page direction:', document.documentElement.dir || document.body.dir || 'ltr');
                            
                            this.driftInstance = new window.Drift(mainImage, {
                                paneContainer: zoomContainer,
                                inlinePane: false,              // ✅ Floating zoom pane (Amazon style)
                                inlineOffsetY: -85,             // Position above the image
                                inlineOffsetX: 10,              // Small offset to the right
                                hoverBoundingBox: true,         // Show gray box on hover
                                hoverDelay: 200,                // Small delay before showing
                                touchDelay: 0,
                                sourceAttribute: 'data-zoom',   // Use high-res from data-zoom attribute
                                zoomFactor: 3,                  // 3x zoom for clarity
                                handleTouch: false,             // Desktop only
                                namespace: 'drift-amazon',
                                injectBaseStyles: true,         // Use default Drift styles
                                boundingBoxContainer: zoomContainer, // ✅ RTL Fix: Ensure bounding box is in same container as image
                            });
                            console.log('Drift initialized successfully!');
                        } catch (e) {
                            console.error('Drift initialization error:', e);
                        }
                    },

                    changeImage(index, url) {
                        this.currentIndex = index;
                        this.currentImageUrl = url;
                        
                        @this.call('changeImage', url);

                        // إعادة تهيئة Drift بعد تحميل الصورة الجديدة
                        this.$nextTick(() => {
                            const img = document.getElementById('product-main-image');
                            if (img.complete) {
                                setTimeout(() => this.initDriftZoom(), 100);
                            } else {
                                img.onload = () => setTimeout(() => this.initDriftZoom(), 100);
                            }
                        });
                    },

                    openLightbox() {
                        if (!this.hasImages || !window.Spotlight || this.gallery.length === 0) {
                            return;
                        }
                        
                        window.Spotlight.show(this.gallery, {
                            index: this.currentIndex + 1,
                            animation: 'fade',
                            control: ['autofit', 'zoom', 'close', 'fullscreen'],
                            infinite: true
                        });
                    }
                }
            }
        });
    </script>
    @endpush

    {{-- RIGHT COLUMN: Product Info --}}
    <div class="space-y-6">
        {{-- Product Title & Basic Info --}}
        <div class="space-y-3">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
                {{ $product->name }}
            </h1>
            
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                @if($product->brand)
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">Brand:</span>
                        <span class="font-semibold text-gray-900">{{ $product->brand }}</span>
                    </div>
                @endif
                
                <div class="flex items-center gap-2">
                    <span class="text-gray-500">SKU:</span>
                    <span class="font-mono text-gray-900">{{ $product->sku }}</span>
                </div>
            </div>

            {{-- Rating & Reviews --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star text-sm {{ $i <= floor($product->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                    @endfor
                </div>
                <span class="text-sm text-gray-600">
                    {{ number_format($product->average_rating, 1) }} 
                    <span class="text-gray-400">({{ $product->reviews_count }} {{ $product->reviews_count === 1 ? 'review' : 'reviews' }})</span>
                </span>
            </div>
        </div>

        {{-- Price --}}
        <div class="border-t border-b border-gray-200 py-4">
            <div class="flex items-baseline gap-3">
                <span class="text-4xl font-bold text-violet-700">
                    ${{ number_format($this->getCurrentPrice(), 2) }}
                </span>
                
                @if($product->is_on_sale && !$selectedVariant)
                    <span class="text-2xl text-gray-400 line-through">
                        ${{ number_format($product->price, 2) }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Stock Status --}}
        <div class="flex items-center gap-2">
            @if($this->isInStock())
                <div class="flex items-center gap-2 text-green-600">
                    <i class="fas fa-check-circle"></i>
                    <span class="font-semibold">In Stock</span>
                </div>
                @if($this->getMaxStock() <= 10)
                    <span class="text-sm text-orange-600">
                        (Only {{ $this->getMaxStock() }} left!)
                    </span>
                @endif
            @else
                <div class="flex items-center gap-2 text-red-600">
                    <i class="fas fa-times-circle"></i>
                    <span class="font-semibold">Out of Stock</span>
                </div>
            @endif
        </div>

        {{-- Short Description --}}
        @if($product->short_description)
            <div class="text-gray-700 leading-relaxed">
                {{ $product->short_description }}
            </div>
        @endif

        {{-- Variants Selection --}}
        @if($product->variants->count() > 0)
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Select Options:</h3>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($product->variants as $variant)
                        <button 
                            type="button"
                            wire:click="selectVariant({{ $variant->id }})"
                            @class([
                                'px-4 py-3 border-2 rounded-lg text-sm font-medium transition-all duration-200',
                                'border-violet-600 bg-violet-50 text-violet-700 ring-2 ring-violet-200' => $selectedVariantId === $variant->id,
                                'border-gray-300 text-gray-700 hover:border-violet-400 hover:bg-gray-50' => $selectedVariantId !== $variant->id && $variant->stock > 0,
                                'border-gray-200 text-gray-400 cursor-not-allowed opacity-50' => $variant->stock <= 0
                            ])
                            {{ $variant->stock <= 0 ? 'disabled' : '' }}
                        >
                            <div class="flex flex-col items-center gap-1">
                                <span>{{ $variant->name }}</span>
                                @if($variant->stock <= 0)
                                    <span class="text-xs text-red-500">Out of stock</span>
                                @elseif($variant->stock <= 5)
                                    <span class="text-xs text-orange-500">{{ $variant->stock }} left</span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Quantity Selector --}}
        @if($this->isInStock())
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-900">Quantity:</label>
                <div class="flex items-center gap-3">
                    <div class="flex items-center border-2 border-gray-300 rounded-lg overflow-hidden">
                        <button 
                            type="button"
                            wire:click="decrementQuantity"
                            class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors duration-200"
                            {{ $quantity <= 1 ? 'disabled' : '' }}
                        >
                            <i class="fas fa-minus"></i>
                        </button>
                        
                        <input 
                            type="number" 
                            wire:model.live="quantity"
                            min="1"
                            max="{{ $this->getMaxStock() }}"
                            class="w-20 px-4 py-3 text-center font-semibold text-gray-900 border-x-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-violet-500"
                        >
                        
                        <button 
                            type="button"
                            wire:click="incrementQuantity"
                            class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors duration-200"
                            {{ $quantity >= $this->getMaxStock() ? 'disabled' : '' }}
                        >
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    
                    <span class="text-sm text-gray-600">
                        {{ $this->getMaxStock() }} available
                    </span>
                </div>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button 
                type="button"
                wire:click="addToCart"
                @class([
                    'flex-1 flex items-center justify-center gap-2 px-6 py-4 rounded-lg font-semibold text-lg transition-all duration-200',
                    'bg-violet-600 hover:bg-violet-700 text-white shadow-lg hover:shadow-xl' => $this->isInStock(),
                    'bg-gray-300 text-gray-500 cursor-not-allowed' => !$this->isInStock()
                ])
                {{ !$this->isInStock() ? 'disabled' : '' }}
            >
                <i class="fas fa-shopping-cart"></i>
                <span>{{ $this->isInStock() ? 'Add to Cart' : 'Out of Stock' }}</span>
            </button>

            {{-- Wishlist Button (Livewire Component) --}}
            <livewire:store.wishlist-button :productId="$product->id" size="lg" :showText="false" :key="'detail-wishlist-'.$product->id" />
        </div>

        {{-- Additional Product Meta --}}
        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
            @if($product->weight)
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-weight text-gray-400"></i>
                    <span>Weight: {{ $product->weight }} kg</span>
                </div>
            @endif
            
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-eye text-gray-400"></i>
                <span>{{ number_format($product->views_count) }} views</span>
            </div>
        </div>
    </div>
</div>

{{-- Product Information Tabs --}}
<div class="mt-16 border-t border-gray-200" x-data="{ activeTab: 'description' }">
    {{-- Tab Headers --}}
    <div class="flex flex-wrap gap-1 border-b border-gray-200">
        <button 
            @click="activeTab = 'description'"
            :class="activeTab === 'description' ? 'border-violet-600 text-violet-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'"
            class="px-6 py-4 font-semibold border-b-2 transition-colors duration-200"
        >
            Description
        </button>
        
        @if($product->specifications)
            <button 
                @click="activeTab = 'specifications'"
                :class="activeTab === 'specifications' ? 'border-violet-600 text-violet-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'"
                class="px-6 py-4 font-semibold border-b-2 transition-colors duration-200"
            >
                Specifications
            </button>
        @endif
        
        @if($product->how_to_use)
            <button 
                @click="activeTab = 'howToUse'"
                :class="activeTab === 'howToUse' ? 'border-violet-600 text-violet-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'"
                class="px-6 py-4 font-semibold border-b-2 transition-colors duration-200"
            >
                How to Use
            </button>
        @endif
        
        <button 
            @click="activeTab = 'reviews'"
            :class="activeTab === 'reviews' ? 'border-violet-600 text-violet-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'"
            class="px-6 py-4 font-semibold border-b-2 transition-colors duration-200"
        >
            Reviews ({{ $product->reviews_count }})
        </button>
    </div>

    {{-- Tab Content --}}
    <div class="py-8">
        {{-- Description Tab --}}
        <div x-show="activeTab === 'description'" x-cloak>
            <div class="prose prose-violet max-w-none text-gray-700 leading-relaxed">
                @if($product->long_description)
                    {!! $product->long_description !!}
                @elseif($product->description)
                    {!! $product->description !!}
                @else
                    <p class="text-gray-500 italic">No description available.</p>
                @endif
            </div>
        </div>

        {{-- Specifications Tab --}}
        @if($product->specifications)
            <div x-show="activeTab === 'specifications'" x-cloak>
                <div class="prose prose-violet max-w-none text-gray-700 leading-relaxed">
                    {!! $product->specifications !!}
                </div>
            </div>
        @endif

        {{-- How to Use Tab --}}
        @if($product->how_to_use)
            <div x-show="activeTab === 'howToUse'" x-cloak>
                <div class="prose prose-violet max-w-none text-gray-700 leading-relaxed">
                    {!! $product->how_to_use !!}
                </div>
            </div>
        @endif

        {{-- Reviews Tab --}}
        <div x-show="activeTab === 'reviews'" x-cloak>
            {{-- Use the ProductReviews Livewire component for full review functionality --}}
            <livewire:store.product-reviews :product="$product" />
        </div>
    </div>
</div>
</div>{{-- End of single root element --}}
