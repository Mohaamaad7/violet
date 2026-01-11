<div>
    {{-- Page Header --}}
    <section class="bg-gradient-to-br from-violet-600 to-purple-700 py-12 md:py-16">
        <div class="container mx-auto px-4">
            <div class="text-center text-white">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $page->title }}</h1>
            </div>
        </div>
    </section>

    {{-- Page Content --}}
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg prose-violet max-w-none
                    prose-headings:text-gray-900 prose-headings:font-bold
                    prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4 prose-h2:border-b prose-h2:border-gray-200 prose-h2:pb-2
                    prose-h3:text-xl prose-h3:mt-6 prose-h3:mb-3
                    prose-p:text-gray-700 prose-p:leading-relaxed
                    prose-li:text-gray-700
                    prose-strong:text-gray-900
                    prose-a:text-violet-600 prose-a:no-underline hover:prose-a:underline
                    {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                    {!! $page->content !!}
                </div>

                {{-- Last Updated --}}
                <div class="mt-12 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">
                        {{ __('messages.last_updated') }}: {{ $page->updated_at->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>