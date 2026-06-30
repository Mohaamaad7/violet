# ⚠️ CLARIFICATIONS ADDENDUM — Combo Discount Implementation
## Addendum to: implementation_plan.md
## Project: Violet E-Commerce — Laravel 12 / Filament v4 / Livewire v3

---

> **AGENT INSTRUCTIONS**
> Read `implementation_plan.md` first, then read this file.
> This file clarifies 4 points that `implementation_plan.md` left ambiguous.
> Each clarification overrides or supplements the corresponding step.

---

## CLARIFICATION #1 — Exact signature and return type of `getComboDiscount()`

### Step affected
implementation_plan.md step #8: "Service: CartService — إضافة getComboDiscount() method"

### The problem with the current description
`implementation_plan.md` says "add `getComboDiscount()` method" but does not specify:
- What it accepts as parameters
- What it returns
- How it accesses `ComboDiscountService` given that CartService has no constructor

### The exact implementation

Add this method to `app/Services/CartService.php`:

```php
/**
 * Evaluate the cart against all active combo rules.
 *
 * Returns an array with:
 *   - 'discount'        (float)          : amount to deduct, 0.0 if no rule matches
 *   - 'rule'            (ComboRule|null) : the matched rule object
 *   - 'matched_rule_id' (int|null)       : the matched rule's primary key
 *
 * Uses app() to resolve ComboDiscountService because CartService has no
 * constructor and therefore cannot use constructor injection.
 */
public function getComboDiscount(): array
{
    /** @var \App\Services\ComboDiscountService $comboService */
    $comboService = app(\App\Services\ComboDiscountService::class);

    // getItems() returns the CartItem collection with product relation loaded.
    // Confirm the exact method name by reading CartService before writing this.
    // Common candidates: getItems(), getCartItems(), items().
    // Use whichever method CheckoutPage uses to get cart items.
    $cartItems = $this->getItems();

    return $comboService->evaluate($cartItems);
}
```

**Important:** Before writing this, search `CartService.php` for the method that returns
the cart items collection (with product relation). Use that exact method name.

---

## CLARIFICATION #2 — CartPage.php: the full logic for combo calculation

### Step affected
implementation_plan.md step #18: "Livewire: CartPage.php — nearbyComboHint + comboDiscount"

### The exact implementation

Add the following to `app/Livewire/Store/CartPage.php`:

#### A) Add public properties (Livewire 3 — public properties only)

```php
public float  $comboDiscount  = 0.0;
public string $comboRuleName  = '';
public string $nearbyComboHint = '';
```

#### B) Add a method that calculates all three values

```php
public function recalculateCombo(): void
{
    /** @var \App\Services\CartService $cartService */
    $cartService = app(\App\Services\CartService::class);

    // Full evaluation result from ComboDiscountService via CartService
    $result = $cartService->getComboDiscount();

    if ($result['discount'] > 0) {
        // ✅ Combo is fully matched
        $this->comboDiscount = $result['discount'];
        $this->comboRuleName = $result['rule']?->name ?? '';
        $this->nearbyComboHint = '';
        return;
    }

    // ❌ Combo not matched — check if the cart is 1 condition away
    $this->comboDiscount = 0.0;
    $this->comboRuleName = '';
    $this->nearbyComboHint = '';

    // Load all active rules to check proximity
    $allRules = \App\Models\ComboRule::with('conditions.category', 'conditions.product')
        ->where('is_active', true)
        ->get()
        ->filter(fn ($r) => $r->isCurrentlyActive());

    // Get category IDs currently in the cart
    $cartItems = app(\App\Services\CartService::class)->getItems();
    $cartCategoryIds = $cartItems->pluck('product.category_id')->filter()->unique();

    foreach ($allRules as $rule) {
        $total = $rule->conditions->count();
        $met   = $rule->conditions->filter(
            fn ($c) => $c->condition_type === 'category'
                       && $cartCategoryIds->contains($c->condition_id)
        )->count();

        if ($met === $total - 1) {
            // Exactly 1 condition away — show the hint
            $missingCondition = $rule->conditions->first(
                fn ($c) => $c->condition_type === 'category'
                           && ! $cartCategoryIds->contains($c->condition_id)
            );
            $discountLabel = $rule->discount_type === 'percentage'
                ? "{$rule->discount_value}%"
                : number_format($rule->discount_value) . ' ج.م';

            $this->nearbyComboHint = __('messages.combo_almost_there', [
                'category' => $missingCondition?->condition_name ?? '',
                'discount' => $discountLabel,
                'name'     => $rule->name,
            ]);
            return;
        }
    }
}
```

#### C) Call `recalculateCombo()` in `mount()` and in any method that changes the cart

```php
public function mount(): void
{
    // ... existing mount logic ...
    $this->recalculateCombo();
}

// If CartPage has an updateQuantity() or removeItem() method, call
// $this->recalculateCombo() at the end of each one.
// Search for all methods that modify the cart and add the call there.
```

#### D) In `cart-page.blade.php` — pass values from the Livewire component

The Blade template receives `$comboDiscount`, `$comboRuleName`, and `$nearbyComboHint`
as Livewire public properties. Do NOT read from session. Use the properties directly:

```blade
{{-- Use $comboDiscount instead of session('combo_discount') --}}
{{-- Use $comboRuleName instead of session('combo_rule_name') --}}
```

---

## CLARIFICATION #3 — `placeOrder()` must pass combo fields to OrderService

### Step affected
implementation_plan.md step #9: "Livewire: CheckoutPage.php — دمج الكومبو في recalculateTotal() + placeOrder()"
and step #10: "Service: OrderService — تسجيل الاستخدام بعد الإنشاء"

### The problem
`implementation_plan.md` says to "add combo to `placeOrder()`" but does not show
that the `$data` array passed to `OrderService::createOrder(array $data)` must
include the combo fields. Without this, OrderService cannot record the usage.

### The exact additions

#### In `CheckoutPage.php` — add properties

```php
// Add these as public Livewire properties alongside $couponDiscount:
public float  $comboDiscount  = 0.0;
public ?int   $comboRuleId    = null;
public string $comboRuleName  = '';
```

#### In `CheckoutPage::recalculateTotal()` — add combo calculation

```php
// After the existing coupon discount calculation, add:
$comboResult         = app(\App\Services\CartService::class)->getComboDiscount();
$this->comboDiscount = $comboResult['discount'];
$this->comboRuleId   = $comboResult['matched_rule_id'];
$this->comboRuleName = $comboResult['rule']?->name ?? '';

// Update the total formula to include combo:
// BEFORE: $this->total = $this->subtotal + $this->shippingCost - $this->couponDiscount;
// AFTER:
$this->total = $this->subtotal
             + $this->shippingCost
             - $this->couponDiscount
             - $this->comboDiscount;
```

#### In `CheckoutPage::placeOrder()` — add combo fields to $data

Read the existing `$data` array that is passed to `OrderService::createOrder()`.
Add these two keys to it:

```php
// Add alongside 'discount_code_id' and 'discount_amount':
'combo_rule_id'          => $this->comboRuleId,
'combo_discount_amount'  => $this->comboDiscount,
```

#### In `OrderService::createOrder(array $data)` — read and record

After `Order::create([...])` succeeds, add:

```php
// Record combo usage if a combo rule was applied
if (! empty($data['combo_rule_id']) && ! empty($data['combo_discount_amount'])) {
    $comboRule = \App\Models\ComboRule::find($data['combo_rule_id']);
    if ($comboRule) {
        app(\App\Services\ComboDiscountService::class)
            ->recordUsage($order, $comboRule, (float) $data['combo_discount_amount']);
    }
}
```

---

## CLARIFICATION #4 — ComboRuleSeeder must NOT run automatically in production

### Step affected
implementation_plan.md step #23: "Seeder: ComboRuleSeeder + DatabaseSeeder"

### The problem
`implementation_plan.md` says to add `$this->call(ComboRuleSeeder::class)` to
`DatabaseSeeder.php`. This means running `php artisan db:seed` in production
would insert demo combo rules into the live database.

### The fix
Do NOT add ComboRuleSeeder to DatabaseSeeder.php unconditionally.
Instead, use an environment guard:

```php
// In database/seeders/DatabaseSeeder.php — wrap in environment check:
if (app()->isLocal() || app()->environment('testing', 'staging')) {
    $this->call(ComboRuleSeeder::class);
}
```

Or — preferred — do NOT add it to DatabaseSeeder at all. Run it manually when needed:

```bash
php artisan db:seed --class=ComboRuleSeeder
```

Document this in the seeder file itself:

```php
/**
 * Demo combo rules for local/staging testing.
 *
 * Run manually: php artisan db:seed --class=ComboRuleSeeder
 * Do NOT include in DatabaseSeeder for production.
 */
class ComboRuleSeeder extends Seeder
```

---

## SUMMARY — What this file changes vs implementation_plan.md

| implementation_plan.md | This file overrides with |
|------------------------|--------------------------|
| "add `getComboDiscount()` method" | Full method with signature, return type, and `app()` resolution |
| "إضافة `$nearbyComboHint` + `$comboDiscount`" in CartPage | Full `recalculateCombo()` method with complete logic |
| "دمج في `placeOrder()`" | Explicit addition of `combo_rule_id` + `combo_discount_amount` to `$data` |
| `$this->call(ComboRuleSeeder::class)` in DatabaseSeeder | Environment guard or manual-only seeder |

All other steps in `implementation_plan.md` are correct as written.

---

*Clarifications Addendum — Violet E-Commerce — Combo Discount Feature*
