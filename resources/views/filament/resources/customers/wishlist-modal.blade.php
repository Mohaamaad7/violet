<div class="space-y-4">
    @if($wishlists->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ trans_db('admin.customers.wishlist.empty') }}
            </p>
        </div>
    @else
        <div class="mb-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ trans_db('admin.customers.wishlist.total_items', ['count' => $wishlists->count()]) }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @foreach($wishlists as $wishlist)
                <div class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    {{-- Product Image --}}
                    <div class="flex-shrink-0">
                        @if($wishlist->product->getFirstMediaUrl('product-images', 'thumbnail'))
                            <img src="{{ $wishlist->product->getFirstMediaUrl('product-images', 'thumbnail') }}" 
                                 alt="{{ $wishlist->product->name }}"
                                 class="h-20 w-20 object-cover rounded-lg">
                        @else
                            <div class="h-20 w-20 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ $wishlist->product->name }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ trans_db('admin.products.fields.sku') }}: {{ $wishlist->product->sku }}
                        </p>
                        <div class="mt-1">
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                {{ number_format($wishlist->product->price, 2) }} EGP
                            </span>
                            @if($wishlist->product->sale_price)
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400 line-through">
                                    {{ number_format($wishlist->product->sale_price, 2) }} EGP
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Stock Status --}}
                    <div class="flex-shrink-0">
                        @if($wishlist->product->stock > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ trans_db('messages.in_stock') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ trans_db('messages.out_of_stock') }}
                            </span>
                        @endif
                    </div>

                    {{-- Added Date --}}
                    <div class="flex-shrink-0 text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ trans_db('messages.added_on') }}
                        </p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $wishlist->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
