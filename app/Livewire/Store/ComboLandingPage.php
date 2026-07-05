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
    // Cart Addition
    // ─────────────────────────────────────────────────────────────────────────

    public function addAllToCart(): void
    {
        $this->errors = [];
        $this->globalError = '';
        $this->processing = true;

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

        if (!empty($this->errors)) {
            $this->processing = false;
            return;
        }

        $cartService = app(CartService::class);
        $existingCart = $cartService->getCart();

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
                $slots = $this->selections[$conditionId];
                
                // Add items slot by slot
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
                        break 2;
                    }
                }
            }
        }

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

        $contentIds = [];
        $contentsMap = []; // product_id => quantity
        
        foreach ($this->selections as $conditionId => $selectionData) {
            $data = $this->conditionData[$conditionId] ?? null;
            if (!$data) {
                continue;
            }

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

        $this->dispatch('combo-added', [
            'combo_name'  => $this->combo->name,
            'content_ids' => array_values(array_unique($contentIds)),
            'contents'    => $contents,
            'value'       => $this->comboPrice,
            'currency'    => 'EGP',
            'num_items'   => count($contentIds),
            'redirect_url'=> route('cart'),
        ]);

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
