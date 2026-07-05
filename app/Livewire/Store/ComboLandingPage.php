<?php

namespace App\Livewire\Store;

use App\Models\ComboRule;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class ComboLandingPage extends Component
{
    /**
     * The combo rule for this landing page.
     */
    public ComboRule $combo;

    /**
     * Resolved condition data for the view.
     *
     * Structure: [ conditionId => [
     *   'type'              => 'product'|'category',
     *   'required_quantity' => int,
     *   // product-type only:
     *   'product_id'    => int,
     *   'product_name'  => string,
     *   'product_image' => string,
     *   'product_price' => float,
     *   'regular_price' => float,
     *   'is_on_sale'    => bool,
     *   'has_variants'  => bool,
     *   'variants'      => array,
     *   // category-type only:
     *   'category_id'   => int,
     *   'category_name' => string,
     *   'products'      => array,
     * ]]
     */
    public array $conditionData = [];

    /**
     * Customer selections — keyed by condition ID.
     *
     * For PRODUCT-type conditions (product fixed, only variant chosen):
     *   [ conditionId => ['product_id' => int, 'variant_id' => int|null] ]
     *
     * For CATEGORY-type conditions (Mix & Match — one slot per required unit):
     *   [ conditionId => [
     *       0 => ['product_id' => int|null, 'variant_id' => int|null],
     *       1 => ['product_id' => int|null, 'variant_id' => int|null],
     *       ...
     *   ]]
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
        $this->resolveConditions();
        $this->calculateComboPrice();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Condition Resolution
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve each condition into view-ready data and initialise selections.
     */
    private function resolveConditions(): void
    {
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
                    'required_quantity' => $condition->required_quantity,
                ];

                // Product-type: single flat selection (product is pre-determined)
                $this->selections[$conditionId] = [
                    'product_id' => $product->id,
                    'variant_id' => null,
                ];

                // Auto-select first in-stock variant
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
                    'required_quantity' => $condition->required_quantity,
                ];

                // Category-type: one empty slot per required unit (Mix & Match)
                $slots = [];
                for ($i = 0; $i < $condition->required_quantity; $i++) {
                    $slots[$i] = ['product_id' => null, 'variant_id' => null];
                }
                $this->selections[$conditionId] = $slots;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Selection Handlers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Handle product selection for a specific Mix & Match slot
     * (category-type conditions only).
     */
    public function selectProductForSlot(int $conditionId, int $slot, int $productId): void
    {
        if (!isset($this->conditionData[$conditionId])) {
            return;
        }

        $this->selections[$conditionId][$slot]['product_id'] = $productId;
        $this->selections[$conditionId][$slot]['variant_id'] = null;

        // Auto-select first in-stock variant for this product
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

        // Clear error for this slot
        unset($this->errors["{$conditionId}.{$slot}"]);

        $this->calculateComboPrice();
    }

    /**
     * Handle variant selection for a specific Mix & Match slot
     * (category-type conditions).
     */
    public function selectVariantForSlot(int $conditionId, int $slot, int $variantId): void
    {
        if (!isset($this->selections[$conditionId][$slot])) {
            return;
        }

        $this->selections[$conditionId][$slot]['variant_id'] = $variantId;

        unset($this->errors["{$conditionId}.{$slot}"]);

        $this->calculateComboPrice();
    }

    /**
     * Handle variant selection for product-type conditions (flat, single selection).
     */
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

    /**
     * Calculate the total combo price after discount.
     */
    public function calculateComboPrice(): void
    {
        $totalOriginal = 0;

        foreach ($this->conditionData as $conditionId => $data) {

            if ($data['type'] === 'product') {
                // Product-type: fixed product, qty × unit price
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
                // Category-type: sum each slot's price (Mix & Match)
                $slots = $this->selections[$conditionId] ?? [];

                if (empty($slots)) {
                    // Fallback: use first product price × qty as placeholder
                    if (!empty($data['products'])) {
                        $totalOriginal += (float) $data['products'][0]['price'] * $data['required_quantity'];
                    }
                    continue;
                }

                foreach ($slots as $slotIndex => $slot) {
                    if (!$slot['product_id']) {
                        // Slot not filled — use first product price as placeholder
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

        // Apply combo discount
        if ($this->combo->discount_type === 'fixed_price') {
            $this->comboPrice = round((float) $this->combo->fixed_price, 2);
        } elseif ($this->combo->discount_type === 'percentage') {
            $discount = $totalOriginal * ($this->combo->discount_percentage / 100);
            $this->comboPrice = round($totalOriginal - $discount, 2);
        } else {
            $this->comboPrice = $this->originalPrice;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cart Addition
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate all selections and add items to cart (all-or-nothing rollback).
     */
    public function addAllToCart(): void
    {
        $this->errors = [];
        $this->globalError = '';
        $this->processing = true;

        // ── Server-side validation ──────────────────────────────────────────
        foreach ($this->conditionData as $conditionId => $data) {

            if ($data['type'] === 'product') {
                // Product-type: only validate variant when product has variants
                $selection = $this->selections[$conditionId] ?? null;
                if ($data['has_variants'] && (!$selection || !$selection['variant_id'])) {
                    $this->errors[$conditionId] = "يرجى اختيار النوع لـ: {$data['product_name']}";
                }

            } elseif ($data['type'] === 'category') {
                // Category-type: validate every slot
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

        if (!empty($this->errors)) {
            $this->processing = false;
            return;
        }

        // ── Cart addition with rollback ─────────────────────────────────────
        $cartService = app(CartService::class);
        $existingCart = $cartService->getCart();

        // Snapshot existing cart
        $snapshot = [];
        if ($existingCart) {
            foreach ($existingCart->items as $item) {
                $snapshot[$item->id] = [
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                ];
            }
        }

        $addFailed   = false;
        $failMessage = '';

        foreach ($this->conditionData as $conditionId => $data) {

            if ($data['type'] === 'product') {
                // Product-type: add required_quantity as a single cart call
                $selection = $this->selections[$conditionId];
                $result = $cartService->addToCart(
                    productId: $selection['product_id'],
                    quantity:  $data['required_quantity'],
                    variantId: $selection['variant_id']
                );

                if (!$result['success']) {
                    $addFailed   = true;
                    $failMessage = "{$data['product_name']}: {$result['message']}";
                    break;
                }

            } elseif ($data['type'] === 'category') {
                // Category-type (Mix & Match): add qty=1 per slot
                $slots = $this->selections[$conditionId];

                foreach ($slots as $slot => $slotData) {
                    $result = $cartService->addToCart(
                        productId: $slotData['product_id'],
                        quantity:  1,
                        variantId: $slotData['variant_id']
                    );

                    if (!$result['success']) {
                        $addFailed   = true;
                        $productData = collect($data['products'])->firstWhere('id', $slotData['product_id']);
                        $name        = $productData ? $productData['name'] : $data['category_name'];
                        $failMessage = "{$name}: {$result['message']}";
                        break 2; // Exit both loops
                    }
                }
            }
        }

        // ── Rollback on failure ─────────────────────────────────────────────
        if ($addFailed) {
            $currentCart = $cartService->getCart();
            if ($currentCart) {
                $currentCart->load('items');
                foreach ($currentCart->items as $item) {
                    $snapshotEntry = $snapshot[$item->id] ?? null;
                    if ($snapshotEntry) {
                        if ($item->quantity !== $snapshotEntry['quantity']) {
                            $cartService->updateQuantity($item->id, $snapshotEntry['quantity']);
                        }
                    } else {
                        $cartService->removeItem($item->id);
                    }
                }
            }

            $this->globalError = $failMessage;
            $this->processing  = false;
            return;
        }

        // ── Success: dispatch Pixel + JS redirect ───────────────────────────
        $contentIds = [];
        foreach ($this->selections as $conditionId => $selectionData) {
            $data = $this->conditionData[$conditionId] ?? null;
            if (!$data) {
                continue;
            }

            if ($data['type'] === 'product') {
                $contentIds[] = $selectionData['product_id'];
            } elseif ($data['type'] === 'category') {
                foreach ($selectionData as $slot) {
                    if ($slot['product_id']) {
                        $contentIds[] = $slot['product_id'];
                    }
                }
            }
        }

        $this->dispatch('combo-added', [
            'combo_name'  => $this->combo->name,
            'content_ids' => array_values(array_unique($contentIds)),
            'value'       => $this->comboPrice,
            'currency'    => 'EGP',
            'num_items'   => count($contentIds),
            'redirect_url'=> route('cart'),
        ]);

        $this->processing = false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get all product IDs in this combo for Pixel events.
     */
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

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.store.combo-landing-page', [
            'conditionData' => $this->conditionData,
            'selections'    => $this->selections,
        ])->layout('layouts.store', ['title' => $this->combo->name]);
    }
}
