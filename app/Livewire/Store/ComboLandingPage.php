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
     * Structure: [ conditionId => [
     *   'condition' => ComboRuleCondition,
     *   'type' => 'product'|'category',
     *   'product' => Product|null,           // set for product-type
     *   'products' => Collection|null,       // set for category-type
     *   'required_quantity' => int,
     * ]]
     */
    public array $conditionData = [];

    /**
     * Customer selections.
     * Structure: [ conditionId => ['product_id' => int|null, 'variant_id' => int|null] ]
     */
    public array $selections = [];

    /**
     * Error messages keyed by condition ID.
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
     * Mount the component — resolve slug to combo rule.
     */
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

    /**
     * Resolve each condition into displayable data.
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
                    'type' => 'product',
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->primary_image,
                    'product_price' => (float) $product->final_price,
                    'has_variants' => $product->variants->count() > 0,
                    'variants' => $product->variants->map(fn ($v) => [
                        'id' => $v->id,
                        'name' => $v->name,
                        'price' => (float) $v->price,
                        'stock' => $v->stock,
                    ])->toArray(),
                    'required_quantity' => $condition->required_quantity,
                ];

                // Pre-select product for product-type conditions
                $this->selections[$conditionId] = [
                    'product_id' => $product->id,
                    'variant_id' => null,
                ];

                // Auto-select first in-stock variant if product has variants
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
                    'type' => 'category',
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'products' => $products->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image' => $p->primary_image,
                        'price' => (float) $p->final_price,
                        'has_variants' => $p->variants->count() > 0,
                        'variants' => $p->variants->map(fn ($v) => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'price' => (float) $v->price,
                            'stock' => $v->stock,
                        ])->toArray(),
                    ])->toArray(),
                    'required_quantity' => $condition->required_quantity,
                ];

                $this->selections[$conditionId] = [
                    'product_id' => null,
                    'variant_id' => null,
                ];
            }
        }
    }

    /**
     * Handle product selection for category-type conditions.
     * Resets the variant when a new product is chosen.
     */
    public function selectProduct(int $conditionId, int $productId): void
    {
        if (!isset($this->conditionData[$conditionId])) {
            return;
        }

        $this->selections[$conditionId]['product_id'] = $productId;
        $this->selections[$conditionId]['variant_id'] = null;

        // Auto-select first in-stock variant
        $data = $this->conditionData[$conditionId];
        if ($data['type'] === 'category') {
            $productData = collect($data['products'])->firstWhere('id', $productId);
            if ($productData && $productData['has_variants']) {
                $firstInStock = collect($productData['variants'])->first(fn ($v) => $v['stock'] > 0);
                if ($firstInStock) {
                    $this->selections[$conditionId]['variant_id'] = $firstInStock['id'];
                }
            }
        }

        // Clear error for this condition
        unset($this->errors[$conditionId]);

        $this->calculateComboPrice();
    }

    /**
     * Handle variant selection for a condition.
     */
    public function selectVariant(int $conditionId, int $variantId): void
    {
        if (!isset($this->selections[$conditionId])) {
            return;
        }

        $this->selections[$conditionId]['variant_id'] = $variantId;

        // Clear error for this condition
        unset($this->errors[$conditionId]);

        $this->calculateComboPrice();
    }

    /**
     * Calculate the total combo price after discount.
     */
    public function calculateComboPrice(): void
    {
        $totalOriginal = 0;

        foreach ($this->conditionData as $conditionId => $data) {
            $selection = $this->selections[$conditionId] ?? null;
            $quantity = $data['required_quantity'];

            if (!$selection || !$selection['product_id']) {
                // Use first product price as placeholder for category conditions
                if ($data['type'] === 'category' && !empty($data['products'])) {
                    $totalOriginal += (float) $data['products'][0]['price'] * $quantity;
                } elseif ($data['type'] === 'product') {
                    $totalOriginal += (float) $data['product_price'] * $quantity;
                }
                continue;
            }

            $unitPrice = 0;

            // Determine unit price from selection
            if ($selection['variant_id']) {
                // Find variant price
                $variants = $data['type'] === 'product'
                    ? $data['variants']
                    : (collect($data['products'])->firstWhere('id', $selection['product_id'])['variants'] ?? []);

                $variant = collect($variants)->firstWhere('id', $selection['variant_id']);
                $unitPrice = $variant ? (float) $variant['price'] : 0;
            } else {
                // Use product price
                if ($data['type'] === 'product') {
                    $unitPrice = (float) $data['product_price'];
                } else {
                    $productData = collect($data['products'])->firstWhere('id', $selection['product_id']);
                    $unitPrice = $productData ? (float) $productData['price'] : 0;
                }
            }

            $totalOriginal += $unitPrice * $quantity;
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

    /**
     * Validate all selections and add items to cart.
     * Uses all-or-nothing rollback via existing CartService methods.
     */
    public function addAllToCart(): void
    {
        $this->errors = [];
        $this->globalError = '';
        $this->processing = true;

        // --- Server-side validation ---
        foreach ($this->conditionData as $conditionId => $data) {
            $selection = $this->selections[$conditionId] ?? null;

            if (!$selection || !$selection['product_id']) {
                $label = $data['type'] === 'product' ? $data['product_name'] : $data['category_name'];
                $this->errors[$conditionId] = "يرجى اختيار المنتج لـ: {$label}";
                continue;
            }

            // Check variant requirement
            $hasVariants = false;
            if ($data['type'] === 'product') {
                $hasVariants = $data['has_variants'];
            } else {
                $productData = collect($data['products'])->firstWhere('id', $selection['product_id']);
                $hasVariants = $productData ? $productData['has_variants'] : false;
            }

            if ($hasVariants && !$selection['variant_id']) {
                $label = $data['type'] === 'product' ? $data['product_name'] : $data['category_name'];
                $this->errors[$conditionId] = "يرجى اختيار النوع لـ: {$label}";
            }
        }

        if (!empty($this->errors)) {
            $this->processing = false;
            return;
        }

        // --- Cart addition with rollback ---
        $cartService = app(CartService::class);
        $existingCart = $cartService->getCart();

        // Snapshot: capture existing item IDs and their quantities
        $snapshot = [];
        if ($existingCart) {
            foreach ($existingCart->items as $item) {
                $snapshot[$item->id] = [
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                ];
            }
        }

        $newlyAddedItemKeys = []; // Track items we added so we can rollback
        $addFailed = false;
        $failMessage = '';

        foreach ($this->conditionData as $conditionId => $data) {
            $selection = $this->selections[$conditionId];
            $quantity = $data['required_quantity'];

            $result = $cartService->addToCart(
                productId: $selection['product_id'],
                quantity: $quantity,
                variantId: $selection['variant_id']
            );

            if (!$result['success']) {
                $addFailed = true;
                $label = $data['type'] === 'product' ? $data['product_name'] : $data['category_name'];
                $failMessage = "{$label}: {$result['message']}";
                break;
            }

            // Track this addition for potential rollback
            $newlyAddedItemKeys[] = [
                'product_id' => $selection['product_id'],
                'variant_id' => $selection['variant_id'],
            ];
        }

        // --- Rollback on failure ---
        if ($addFailed) {
            $currentCart = $cartService->getCart();
            if ($currentCart) {
                $currentCart->load('items');
                foreach ($currentCart->items as $item) {
                    $snapshotEntry = $snapshot[$item->id] ?? null;

                    if ($snapshotEntry) {
                        // Existed before — restore original quantity if changed
                        if ($item->quantity !== $snapshotEntry['quantity']) {
                            $cartService->updateQuantity($item->id, $snapshotEntry['quantity']);
                        }
                    } else {
                        // Newly added — remove it
                        $cartService->removeItem($item->id);
                    }
                }
            }

            $this->globalError = $failMessage;
            $this->processing = false;
            return;
        }

        // --- Success: dispatch browser event for Pixel + JS redirect ---
        $contentIds = collect($this->selections)->pluck('product_id')->filter()->values()->toArray();

        $this->dispatch('combo-added', [
            'combo_name' => $this->combo->name,
            'content_ids' => $contentIds,
            'value' => $this->comboPrice,
            'currency' => 'EGP',
            'num_items' => count($contentIds),
            'redirect_url' => route('cart'),
        ]);

        $this->processing = false;
    }

    /**
     * Get the variants for a selected product in a category-type condition.
     */
    public function getSelectedProductVariants(int $conditionId): array
    {
        $data = $this->conditionData[$conditionId] ?? null;
        $selection = $this->selections[$conditionId] ?? null;

        if (!$data || !$selection || !$selection['product_id']) {
            return [];
        }

        if ($data['type'] === 'product') {
            return $data['variants'];
        }

        $productData = collect($data['products'])->firstWhere('id', $selection['product_id']);
        return $productData ? $productData['variants'] : [];
    }

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
            'selections' => $this->selections,
        ])->layout('layouts.store', ['title' => $this->combo->name]);
    }
}
