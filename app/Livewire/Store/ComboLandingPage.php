<?php

namespace App\Livewire\Store;

use App\Models\ComboRule;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ComboDiscountService;
use Illuminate\Support\Str;
use Livewire\Component;

class ComboLandingPage extends Component
{
    /**
     * The combo rule for this landing page.
     */
    public ComboRule $combo;

    /**
     * Resolved condition data for the view.
     */
    public array $conditionData = [];

    /**
     * Customer selections — keyed by condition ID.
     */
    public array $selections = [];

    /**
     * Error messages keyed by "conditionId" or "conditionId.slot".
     */
    public array $errors = [];

    /**
     * Global error message.
     */
    public string $globalError = '';

    /**
     * Processing state for the CTA button.
     */
    public bool $processing = false;

    /**
     * Total combo price after discount.
     */
    public float $comboPrice = 0;

    /**
     * Total original price before discount.
     */
    public float $originalPrice = 0;

    /**
     * Extracted tiers from the combo rule.
     */
    public array $tiers = [];

    /**
     * Currently selected tier index.
     */
    public int $selectedTierIndex = 0;

    // ─────────────────────────────────────────────────────────────────────────
    // Mount
    // ─────────────────────────────────────────────────────────────────────────

    public function mount(string $slug): void
    {
        $combo = ComboRule::with(['conditions.product.variants', 'conditions.category'])
            ->active()
            ->where('slug', $slug)
            ->first();

        abort_if(!$combo, 404);

        $this->combo = $combo;
        
        $this->tiers = is_string($combo->tiers) ? json_decode($combo->tiers, true) : ($combo->tiers ?? []);
        
        // Sort tiers by quantity DESC (largest savings/quantity first)
        usort($this->tiers, fn($a, $b) => $b['quantity'] <=> $a['quantity']);
        $this->selectedTierIndex = 0;

        $this->resolveConditions();
        $this->calculateComboPrice();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Condition Resolution
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveConditions(): void
    {
        $currentQuantity = $this->tiers[$this->selectedTierIndex]['quantity'] ?? 1;

        foreach ($this->combo->conditions as $condition) {
            $conditionId = $condition->id;

            if ($condition->condition_type === 'product') {
                $product = $condition->product;
                if (!$product || $product->status !== 'active') {
                    continue;
                }
                $product->load(['variants', 'media']);

                $this->conditionData[$conditionId] = [
                    'type'              => 'product',
                    'product_id'        => $product->id,
                    'product_name'      => $product->name,
                    'product_image'     => $product->primary_image,
                    'product_price'     => (float) $product->final_price,
                    'regular_price'     => (float) $product->price,
                    'is_on_sale'        => $product->is_on_sale,
                    'has_variants'      => $product->variants->count() > 0,
                    'variants'          => $product->variants->map(fn ($v) => [
                        'id'    => $v->id,
                        'name'  => $v->name,
                        'price' => (float) $v->price,
                        'stock' => $v->stock,
                    ])->toArray(),
                    'required_quantity' => $currentQuantity,
                ];

                // Product-type: single flat selection
                $this->selections[$conditionId] = [
                    'product_id' => $product->id,
                    'variant_id' => null,
                ];

                if ($product->variants->count() > 0) {
                    $firstInStock = $product->variants->first(fn ($v) => $v->stock > 0);
                    if ($firstInStock) {
                        $this->selections[$conditionId]['variant_id'] = $firstInStock->id;
                    }
                }

            } elseif ($condition->condition_type === 'category') {
                $category = $condition->category;
                if (!$category) {
                    continue;
                }

                $products = $category->products()
                    ->active()
                    ->with(['variants', 'media'])
                    ->get();

                $this->conditionData[$conditionId] = [
                    'type'              => 'category',
                    'category_id'       => $category->id,
                    'category_name'     => $category->name,
                    'products'          => $products->map(fn ($p) => [
                        'id'           => $p->id,
                        'name'         => $p->name,
                        'image'        => $p->primary_image,
                        'price'        => (float) $p->final_price,
                        'regular_price'=> (float) $p->price,
                        'is_on_sale'   => $p->is_on_sale,
                        'has_variants' => $p->variants->count() > 0,
                        'variants'     => $p->variants->map(fn ($v) => [
                            'id'    => $v->id,
                            'name'  => $v->name,
                            'price' => (float) $v->price,
                            'stock' => $v->stock,
                        ])->toArray(),
                    ])->toArray(),
                    'required_quantity' => $currentQuantity,
                ];

                $slots = [];
                for ($i = 0; $i < $currentQuantity; $i++) {
                    $slots[$i] = ['product_id' => null, 'variant_id' => null];
                }
                $this->selections[$conditionId] = $slots;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Selection Handlers
    // ─────────────────────────────────────────────────────────────────────────

    public function selectTier(int $index): void
    {
        if (!isset($this->tiers[$index])) return;
        
        $this->selectedTierIndex = $index;
        $newQuantity = $this->tiers[$index]['quantity'];
        
        foreach ($this->conditionData as $conditionId => &$data) {
            $data['required_quantity'] = $newQuantity;
            
            if ($data['type'] === 'category') {
                $slots = $this->selections[$conditionId] ?? [];
                
                // If shrinking, pop elements off the end
                if (count($slots) > $newQuantity) {
                    $this->selections[$conditionId] = array_slice($slots, 0, $newQuantity);
                } 
                // If growing, add empty slots
                elseif (count($slots) < $newQuantity) {
                    for ($i = count($slots); $i < $newQuantity; $i++) {
                        $this->selections[$conditionId][$i] = ['product_id' => null, 'variant_id' => null];
                    }
                }
            }
        }
        
        // Reset errors
        $this->errors = [];
        $this->calculateComboPrice();
    }

    public function selectProductForSlot(int $conditionId, int $slot, int $productId): void
    {
        if (!isset($this->conditionData[$conditionId])) {
            return;
        }

        $this->selections[$conditionId][$slot]['product_id'] = $productId;
        $this->selections[$conditionId][$slot]['variant_id'] = null;

        $data = $this->conditionData[$conditionId];
        if ($data['type'] === 'category') {
            $productData = collect($data['products'])->firstWhere('id', $productId);
            if ($productData && $productData['has_variants']) {
                $firstInStock = collect($productData['variants'])->first(fn ($v) => $v['stock'] > 0);
                if ($firstInStock) {
                    $this->selections[$conditionId][$slot]['variant_id'] = $firstInStock['id'];
                }
            }
        }

        unset($this->errors["{$conditionId}.{$slot}"]);
        $this->calculateComboPrice();
    }

    public function selectVariantForSlot(int $conditionId, int $slot, int $variantId): void
    {
        if (!isset($this->selections[$conditionId][$slot])) {
            return;
        }
        $this->selections[$conditionId][$slot]['variant_id'] = $variantId;
        unset($this->errors["{$conditionId}.{$slot}"]);
        $this->calculateComboPrice();
    }

    public function selectVariant(int $conditionId, int $variantId): void
    {
        if (!isset($this->selections[$conditionId])) {
            return;
        }
        $this->selections[$conditionId]['variant_id'] = $variantId;
        unset($this->errors[$conditionId]);
        $this->calculateComboPrice();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Price Calculation
    // ─────────────────────────────────────────────────────────────────────────

    public function calculateComboPrice(): void
    {
        $totalOriginal = 0;

        foreach ($this->conditionData as $conditionId => $data) {
            if ($data['type'] === 'product') {
                $selection = $this->selections[$conditionId] ?? null;
                $unitPrice = (float) $data['product_price'];

                if ($selection && $selection['variant_id']) {
                    $variant = collect($data['variants'])->firstWhere('id', $selection['variant_id']);
                    if ($variant) {
                        $unitPrice = (float) $variant['price'];
                    }
                }

                $totalOriginal += $unitPrice * $data['required_quantity'];

            } elseif ($data['type'] === 'category') {
                $slots = $this->selections[$conditionId] ?? [];

                if (empty($slots)) {
                    if (!empty($data['products'])) {
                        $totalOriginal += (float) $data['products'][0]['price'] * $data['required_quantity'];
                    }
                    continue;
                }

                foreach ($slots as $slotIndex => $slot) {
                    if (!$slot['product_id']) {
                        $placeholder = !empty($data['products']) ? (float) $data['products'][0]['price'] : 0;
                        $totalOriginal += $placeholder;
                        continue;
                    }

                    $productData = collect($data['products'])->firstWhere('id', $slot['product_id']);
                    if (!$productData) {
                        continue;
                    }

                    $unitPrice = (float) $productData['price'];

                    if ($slot['variant_id']) {
                        $variant = collect($productData['variants'])->firstWhere('id', $slot['variant_id']);
                        if ($variant) {
                            $unitPrice = (float) $variant['price'];
                        }
                    }

                    $totalOriginal += $unitPrice;
                }
            }
        }

        $this->originalPrice = round($totalOriginal, 2);

        $selectedTier = $this->tiers[$this->selectedTierIndex] ?? null;
        
        if ($selectedTier) {
            if ($selectedTier['discount_type'] === 'fixed_price') {
                $this->comboPrice = round((float) $selectedTier['fixed_price'], 2);
            } elseif ($selectedTier['discount_type'] === 'percentage') {
                $discount = $totalOriginal * ($selectedTier['discount_percentage'] / 100);
                $this->comboPrice = round($totalOriginal - $discount, 2);
            } else {
                $this->comboPrice = $this->originalPrice;
            }
        } else {
            $this->comboPrice = $this->originalPrice;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Proportional Price Distribution (DP-aware)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the flat list of items with their base prices from the current selection.
     * Each item is expanded to quantity=1 for proportional distribution.
     *
     * @return array Array of ['product_id', 'variant_id', 'base_price']
     */
    private function buildFlatItemList(): array
    {
        $items = [];

        foreach ($this->conditionData as $conditionId => $data) {
            if ($data['type'] === 'product') {
                $selection = $this->selections[$conditionId] ?? null;
                $unitPrice = (float) $data['product_price'];

                if ($selection && $selection['variant_id']) {
                    $variant = collect($data['variants'])->firstWhere('id', $selection['variant_id']);
                    if ($variant) {
                        $unitPrice = (float) $variant['price'];
                    }
                }

                for ($i = 0; $i < $data['required_quantity']; $i++) {
                    $items[] = [
                        'product_id' => $selection['product_id'],
                        'variant_id' => $selection['variant_id'],
                        'base_price' => $unitPrice,
                    ];
                }

            } elseif ($data['type'] === 'category') {
                $slots = $this->selections[$conditionId] ?? [];

                foreach ($slots as $slot) {
                    if (!$slot['product_id']) continue;

                    $productData = collect($data['products'])->firstWhere('id', $slot['product_id']);
                    if (!$productData) continue;

                    $unitPrice = (float) $productData['price'];

                    if ($slot['variant_id']) {
                        $variant = collect($productData['variants'])->firstWhere('id', $slot['variant_id']);
                        if ($variant) {
                            $unitPrice = (float) $variant['price'];
                        }
                    }

                    $items[] = [
                        'product_id' => $slot['product_id'],
                        'variant_id' => $slot['variant_id'],
                        'base_price' => $unitPrice,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Distribute the combo tier price proportionally across items using the
     * Largest Remainder Method for ETA/ERP-compliant rounding.
     *
     * @param array $flatItems Array of ['product_id', 'variant_id', 'base_price']
     * @param float $tierPrice The total discounted price for this tier
     * @return array Array of ['product_id', 'variant_id', 'price', 'original_price']
     */
    private function distributeProportionally(array $flatItems, float $tierPrice): array
    {
        $totalBase = array_sum(array_column($flatItems, 'base_price'));

        if ($totalBase <= 0) {
            // Fallback: equal split
            $equalPrice = round($tierPrice / max(count($flatItems), 1), 2);
            return array_map(fn($item) => [
                'product_id'     => $item['product_id'],
                'variant_id'     => $item['variant_id'],
                'price'          => $equalPrice,
                'original_price' => $item['base_price'],
            ], $flatItems);
        }

        // Step 1: Calculate exact proportional amounts
        $exactAmounts = [];
        foreach ($flatItems as $idx => $item) {
            $exactAmounts[$idx] = $tierPrice * ($item['base_price'] / $totalBase);
        }

        // Step 2: Floor to 2 decimal places
        $floored = array_map(fn($v) => floor($v * 100) / 100, $exactAmounts);
        $distributed = array_sum($floored);

        // Step 3: Largest Remainder Correction
        $remainder = round($tierPrice - $distributed, 2);
        $remainderPiastres = (int) round($remainder * 100);

        // Build remainder list sorted by fractional part descending
        $remainders = [];
        foreach ($exactAmounts as $idx => $exact) {
            $remainders[$idx] = ($exact * 100) - floor($exact * 100);
        }
        arsort($remainders);

        // Add 0.01 to each item with the largest remainder until budget is spent
        foreach ($remainders as $idx => $fracPart) {
            if ($remainderPiastres <= 0) break;
            $floored[$idx] += 0.01;
            $remainderPiastres--;
        }

        // Build output
        $result = [];
        foreach ($flatItems as $idx => $item) {
            $result[] = [
                'product_id'     => $item['product_id'],
                'variant_id'     => $item['variant_id'],
                'price'          => round($floored[$idx], 2),
                'original_price' => $item['base_price'],
            ];
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cart Addition
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate all selections before cart insertion.
     * Returns true if valid, false if errors were set.
     */
    private function validateSelections(): bool
    {
        $this->errors = [];
        $this->globalError = '';

        foreach ($this->conditionData as $conditionId => $data) {
            if ($data['type'] === 'product') {
                $selection = $this->selections[$conditionId] ?? null;
                if ($data['has_variants'] && (!$selection || !$selection['variant_id'])) {
                    $this->errors[$conditionId] = "يرجى اختيار النوع لـ: {$data['product_name']}";
                }
            } elseif ($data['type'] === 'category') {
                $slots = $this->selections[$conditionId] ?? [];
                $slotCount = $data['required_quantity'];

                for ($slot = 0; $slot < $slotCount; $slot++) {
                    $slotData = $slots[$slot] ?? null;

                    if (!$slotData || !$slotData['product_id']) {
                        $slotLabel = $slotCount > 1 ? " (القطعة " . ($slot + 1) . ")" : "";
                        $this->errors["{$conditionId}.{$slot}"] = "يرجى اختيار المنتج من: {$data['category_name']}{$slotLabel}";
                        continue;
                    }

                    $productData = collect($data['products'])->firstWhere('id', $slotData['product_id']);
                    if ($productData && $productData['has_variants'] && !$slotData['variant_id']) {
                        $slotLabel = $slotCount > 1 ? " (القطعة " . ($slot + 1) . ")" : "";
                        $this->errors["{$conditionId}.{$slot}"] = "يرجى اختيار النوع لـ: {$productData['name']}{$slotLabel}";
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Build pixel event data from selections.
     */
    private function buildPixelData(): array
    {
        $contentIds = [];
        $contentsMap = []; // product_id => quantity

        foreach ($this->selections as $conditionId => $selectionData) {
            $data = $this->conditionData[$conditionId] ?? null;
            if (!$data) continue;

            if ($data['type'] === 'product') {
                $pid = $selectionData['product_id'];
                $contentIds[] = $pid;
                $contentsMap[$pid] = ($contentsMap[$pid] ?? 0) + $data['required_quantity'];
            } elseif ($data['type'] === 'category') {
                foreach ($selectionData as $slot) {
                    if ($slot['product_id']) {
                        $pid = $slot['product_id'];
                        $contentIds[] = $pid;
                        $contentsMap[$pid] = ($contentsMap[$pid] ?? 0) + 1;
                    }
                }
            }
        }

        $contents = [];
        foreach ($contentsMap as $id => $qty) {
            $contents[] = ['id' => (string) $id, 'quantity' => $qty];
        }

        return [
            'combo_name'   => $this->combo->name,
            'content_ids'  => array_values(array_unique($contentIds)),
            'contents'     => $contents,
            'value'        => $this->comboPrice,
            'currency'     => 'EGP',
            'num_items'    => array_sum($contentsMap),
        ];
    }

    /**
     * Execute the actual combo → cart insertion with proportional pricing.
     * Returns true on success, false on failure (sets globalError).
     */
    private function executeComboToCart(): bool
    {
        $flatItems = $this->buildFlatItemList();

        if (empty($flatItems)) {
            $this->globalError = 'لا توجد منتجات مختارة';
            return false;
        }

        // Distribute the tier price proportionally across items
        $distributedItems = $this->distributeProportionally($flatItems, $this->comboPrice);

        // Group by product_id + variant_id to consolidate duplicate selections
        $grouped = [];
        foreach ($distributedItems as $item) {
            $key = $item['product_id'] . '_' . ($item['variant_id'] ?? 'null');
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'product_id'     => $item['product_id'],
                    'variant_id'     => $item['variant_id'],
                    'quantity'       => 0,
                    'price'          => $item['price'],
                    'original_price' => $item['original_price'],
                    // Track individual prices for accurate aggregation
                    '_prices'        => [],
                    '_originals'     => [],
                ];
            }
            $grouped[$key]['quantity']++;
            $grouped[$key]['_prices'][] = $item['price'];
            $grouped[$key]['_originals'][] = $item['original_price'];
        }

        // Build final cart items — for grouped items with multiple units,
        // we insert each unit separately to preserve per-unit proportional pricing
        $cartItems = [];
        foreach ($grouped as $group) {
            for ($i = 0; $i < $group['quantity']; $i++) {
                $cartItems[] = [
                    'product_id'     => $group['product_id'],
                    'variant_id'     => $group['variant_id'],
                    'quantity'       => 1,
                    'price'          => $group['_prices'][$i],
                    'original_price' => $group['_originals'][$i],
                ];
            }
        }

        $comboInstanceUuid = Str::uuid()->toString();
        $cartService = app(CartService::class);

        $result = $cartService->addComboToCart($cartItems, $comboInstanceUuid);

        if (!$result['success']) {
            $this->globalError = $result['message'];
            return false;
        }

        return true;
    }

    /**
     * Add combo selection to cart (existing behavior).
     */
    public function addAllToCart(): void
    {
        $this->processing = true;

        if (!$this->validateSelections()) {
            $this->processing = false;
            return;
        }

        if (!$this->executeComboToCart()) {
            $this->processing = false;
            return;
        }

        $pixelData = $this->buildPixelData();
        $pixelData['redirect_url'] = route('cart');
        $pixelData['action'] = 'add_to_cart';

        $this->dispatch('combo-added', $pixelData);
        $this->processing = false;
    }

    /**
     * Buy Now: Add combo to cart then redirect to checkout.
     * The redirect_url is set to /checkout and fires InitiateCheckout pixel.
     */
    public function buyNow(): void
    {
        $this->processing = true;

        if (!$this->validateSelections()) {
            $this->processing = false;
            return;
        }

        if (!$this->executeComboToCart()) {
            $this->processing = false;
            return;
        }

        $pixelData = $this->buildPixelData();
        $pixelData['redirect_url'] = route('checkout');
        $pixelData['action'] = 'buy_now';

        $this->dispatch('combo-added', $pixelData);
        $this->processing = false;
    }

    public function getProductIds(): array
    {
        $ids = [];
        foreach ($this->conditionData as $data) {
            if ($data['type'] === 'product') {
                $ids[] = $data['product_id'];
            } elseif ($data['type'] === 'category' && !empty($data['products'])) {
                foreach ($data['products'] as $p) {
                    $ids[] = $p['id'];
                }
            }
        }
        return array_unique($ids);
    }

    public function render()
    {
        return view('livewire.store.combo-landing-page', [
            'conditionData' => $this->conditionData,
            'selections'    => $this->selections,
        ])->layout('layouts.store', ['title' => $this->combo->name]);
    }
}
