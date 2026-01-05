<x-filament-panels::page>
    {{-- Search Bar --}}
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-4 pointer-events-none">
                <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="{{ __('admin.help_center.search_placeholder') }}"
                class="block w-full ps-12 pe-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            @if($search)
                <button wire:click="$set('search', '')"
                    class="absolute inset-y-0 end-0 flex items-center pe-4 text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            @endif
        </div>
    </div>

    {{-- Subtitle --}}
    <div class="mb-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">{{ __('admin.help_center.subtitle') }}</p>
    </div>

    @php
        $groupedEntries = $this->getGroupedEntries();
    @endphp

    {{-- No Results State --}}
    @if($groupedEntries->isEmpty())
        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-xl">
            <x-heroicon-o-question-mark-circle class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                @if($search)
                    {{ __('admin.help_center.no_results') }}
                @else
                    {{ __('admin.help_center.no_entries') }}
                @endif
            </h3>
        </div>
    @else
        {{-- Categories with Accordions --}}
        <div class="space-y-6">
            @foreach($groupedEntries as $category => $entries)
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Category Header --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            @php
                                $icon = $this->getCategoryIcon($category);
                                $color = $this->getCategoryColor($category);
                            @endphp
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 flex items-center justify-center">
                                <x-dynamic-component :component="$icon"
                                    class="w-5 h-5 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $this->getCategoryName($category) }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $entries->count() }}
                                    {{ trans_choice('admin.help_center.questions_count', $entries->count()) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Questions Accordion --}}
                    <div x-data="{ openEntry: null }" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($entries as $entry)
                            <div class="group">
                                {{-- Question (Accordion Header) --}}
                                <button @click="openEntry = openEntry === {{ $entry->id }} ? null : {{ $entry->id }}"
                                    class="w-full px-6 py-4 flex items-center justify-between text-start hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <span class="font-medium text-gray-900 dark:text-white pe-4">
                                        {{ $entry->question }}
                                    </span>
                                    <span class="flex-shrink-0 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': openEntry === {{ $entry->id }} }">
                                        <x-heroicon-o-chevron-down class="w-5 h-5" />
                                    </span>
                                </button>

                                {{-- Answer (Accordion Content) --}}
                                <div x-show="openEntry === {{ $entry->id }}" x-collapse class="px-6 pb-4">
                                    <div
                                        class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                        {!! $entry->answer !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>