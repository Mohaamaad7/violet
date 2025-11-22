<x-store-layout title="Cart Debug Test">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">🔍 Violet Cart Debug Panel</h1>
        
        <div class="space-y-6">
            {{-- Panel 1: Livewire Detection --}}
            <div class="bg-white border-2 border-purple-600 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">1️⃣ Livewire Component Detection</h2>
                <div id="livewire-check" class="text-lg">Checking...</div>
            </div>

            {{-- Panel 2: CartManager Detection --}}
            <div class="bg-white border-2 border-purple-600 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">2️⃣ CartManager Component Detection</h2>
                <div id="cart-manager-check" class="text-lg">Checking...</div>
            </div>

            {{-- Panel 3: Alpine Detection --}}
            <div class="bg-white border-2 border-purple-600 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">3️⃣ Alpine.js Detection</h2>
                <div id="alpine-check" class="text-lg">Checking...</div>
            </div>

            {{-- Panel 4: Manual Event Tests --}}
            <div class="bg-white border-2 border-purple-600 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">4️⃣ Manual Event Test</h2>
                <div class="flex gap-4 mb-4">
                    <button 
                        onclick="testOpenCart()" 
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                    >
                        🛒 Test open-cart Event
                    </button>
                    <button 
                        onclick="testAddToCart()" 
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    >
                        ➕ Test add-to-cart Event
                    </button>
                </div>
                <div id="event-result" class="text-lg"></div>
            </div>

            {{-- Panel 5: DOM Inspection --}}
            <div class="bg-white border-2 border-purple-600 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">5️⃣ DOM Inspection</h2>
                <button 
                    onclick="inspectDOM()" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition mb-4"
                >
                    🔎 Inspect DOM
                </button>
                <pre id="dom-output" class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto hidden"></pre>
            </div>

            {{-- Panel 6: Navigation --}}
            <div class="bg-white border-2 border-purple-600 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">6️⃣ Browser Console Output</h2>
                <p class="mb-4">Open DevTools Console (F12) and check for errors</p>
                <div class="flex gap-4">
                    <a 
                        href="{{ route('products.index') }}" 
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                    >
                        🔙 Go to Products Page
                    </a>
                    <a 
                        href="{{ route('home') }}" 
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                    >
                        🏠 Go to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Debug JavaScript --}}
    @push('scripts')
    <script>
        // Wait for page load
        window.addEventListener('load', function() {
            checkLivewire();
            checkCartManager();
            checkAlpine();
        });

        function checkLivewire() {
            const result = document.getElementById('livewire-check');
            if (typeof window.Livewire !== 'undefined') {
                result.innerHTML = '<span class="text-green-600 font-bold">✅ Livewire is loaded</span>';
                console.log('✅ Livewire detected:', window.Livewire);
            } else {
                result.innerHTML = '<span class="text-red-600 font-bold">❌ Livewire NOT loaded</span>';
                console.error('❌ Livewire is undefined');
            }
        }

        function checkCartManager() {
            const result = document.getElementById('cart-manager-check');
            setTimeout(() => {
                const components = document.querySelectorAll('[wire\\:id]');
                if (components.length > 0) {
                    result.innerHTML = `<span class="text-green-600 font-bold">✅ Found ${components.length} Livewire component(s)</span>`;
                    components.forEach(comp => {
                        const id = comp.getAttribute('wire:id');
                        const name = comp.getAttribute('wire:name') || 'Unknown';
                        console.log(`✅ Component: ${name} (ID: ${id})`);
                        result.innerHTML += `<br>- ${name} (ID: ${id})`;
                    });
                } else {
                    result.innerHTML = '<span class="text-red-600 font-bold">❌ No Livewire components found in DOM!</span>';
                    console.error('❌ No [wire:id] elements found');
                }
            }, 1000);
        }

        function checkAlpine() {
            const result = document.getElementById('alpine-check');
            if (typeof window.Alpine !== 'undefined') {
                result.innerHTML = '<span class="text-green-600 font-bold">✅ Alpine.js is loaded</span>';
                console.log('✅ Alpine detected:', window.Alpine);
            } else {
                result.innerHTML = '<span class="text-red-600 font-bold">❌ Alpine.js NOT loaded</span>';
                console.error('❌ Alpine is undefined');
            }
        }

        function testOpenCart() {
            const result = document.getElementById('event-result');
            try {
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.dispatch('open-cart');
                    result.innerHTML = '<span class="text-green-600 font-bold">✅ open-cart event dispatched!</span>';
                    console.log('✅ Dispatched open-cart event');
                } else {
                    result.innerHTML = '<span class="text-red-600 font-bold">❌ Livewire not available</span>';
                }
            } catch (error) {
                result.innerHTML = `<span class="text-red-600 font-bold">❌ Error: ${error.message}</span>`;
                console.error('Error dispatching event:', error);
            }
        }

        function testAddToCart() {
            const result = document.getElementById('event-result');
            try {
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.dispatch('add-to-cart', { productId: 1, quantity: 1 });
                    result.innerHTML = '<span class="text-green-600 font-bold">✅ add-to-cart event dispatched!</span>';
                    console.log('✅ Dispatched add-to-cart event');
                } else {
                    result.innerHTML = '<span class="text-red-600 font-bold">❌ Livewire not available</span>';
                }
            } catch (error) {
                result.innerHTML = `<span class="text-red-600 font-bold">❌ Error: ${error.message}</span>`;
                console.error('Error dispatching event:', error);
            }
        }

        function inspectDOM() {
            const output = document.getElementById('dom-output');
            const cartManager = document.querySelector('[wire\\:id]');
            
            if (cartManager) {
                output.textContent = cartManager.outerHTML.substring(0, 1000) + '\n\n... (truncated)';
                output.classList.remove('hidden');
            } else {
                output.textContent = '❌ No Livewire component found in DOM!';
                output.classList.remove('hidden');
            }
        }
    </script>
    @endpush
</x-store-layout>
