# مهمة: تنفيذ نظام خصم الكومبو (Combo Bundle Discount)
## مشروع: Violet E-Commerce — Laravel 12 / Filament v4 / Livewire v3

---

## 🎯 ملخص المهمة

أضف نظام خصم تلقائي يُطبَّق عندما يشتري العميل منتجاً من كل صنف من مجموعة أصناف محددة مسبقاً.
**مثال:** يشتري العميل أي منتج من "العناية بالبشرة" + أي منتج من "المكياج" + أي منتج من "العطور" → يحصل تلقائياً على خصم 15%.

النظام يعمل **تلقائياً بدون كود خصم** — يُكتشف بمجرد أن السلة تستوفي الشروط.

---

## 👤 الجزء الأول: تجربة العميل في المتجر (User-Facing Workflow)

### المطلوب: ثلاث نقاط لمس رئيسية يشوف فيها العميل العرض

---

### نقطة 1 — بانر الكومبو في صفحة المنتجات والصفحة الرئيسية

**المكان:** `resources/views/livewire/store/` — يظهر في أعلى صفحة قائمة المنتجات (`/products`) وفي الصفحة الرئيسية (`/`).

**السلوك:**
- يظهر تلقائياً لو في كومبو نشط
- يوضح للعميل: "اشترِ من هذه الأصناف واحصل على خصم كذا"
- يعرض الأصناف المطلوبة بأيقوناتها أو أسمائها
- لو فيه أكثر من كومبو نشط، يعرضهم بـ carousel أو يعرض الأقوى فقط

**الـ Blade Component المطلوب:** `resources/views/components/store/combo-offer-banner.blade.php`

```blade
{{-- يُستدعى من: resources/views/livewire/store/product-list.blade.php --}}
{{-- و من: resources/views/livewire/store/home.blade.php أو FeaturedProducts --}}

@php
    // جلب العروض النشطة من cache (مدة 10 دقائق)
    $activeComboRules = cache()->remember('active_combo_rules', 600, function () {
        return \App\Models\ComboRule::with('conditions')
            ->where('is_active', true)
            ->get()
            ->filter(fn ($r) => $r->isCurrentlyActive())
            ->values();
    });
@endphp

@if($activeComboRules->isNotEmpty())
<div class="w-full mb-6">
    @foreach($activeComboRules as $rule)
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 to-purple-700 text-white px-6 py-4 shadow-lg">

        {{-- خلفية زخرفية --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-32 h-32 rounded-full bg-white"></div>
            <div class="absolute -bottom-6 -left-6 w-40 h-40 rounded-full bg-white"></div>
        </div>

        <div class="relative flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- يسار: نص العرض --}}
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
                    🎁
                </div>
                <div>
                    <p class="font-bold text-lg leading-tight">{{ $rule->name }}</p>
                    @if($rule->description)
                        <p class="text-white/80 text-sm mt-0.5">{{ $rule->description }}</p>
                    @endif
                </div>
            </div>

            {{-- وسط: الأصناف المطلوبة --}}
            <div class="flex items-center gap-2 flex-wrap justify-center">
                @foreach($rule->conditions as $index => $condition)
                    @if($index > 0)
                        <span class="text-white/60 text-sm font-bold">+</span>
                    @endif
                    <span class="bg-white/20 backdrop-blur-sm border border-white/30 rounded-full px-3 py-1 text-sm font-medium whitespace-nowrap">
                        {{ $condition->condition_name }}
                    </span>
                @endforeach
            </div>

            {{-- يمين: قيمة الخصم --}}
            <div class="flex-shrink-0 text-center">
                <div class="bg-yellow-400 text-yellow-900 font-black text-2xl rounded-xl px-4 py-2 leading-none">
                    @if($rule->discount_type === 'percentage')
                        {{ $rule->discount_value }}%
                    @else
                        {{ number_format($rule->discount_value) }} ج.م
                    @endif
                </div>
                <p class="text-white/70 text-xs mt-1">{{ __('messages.combo_off') }}</p>
            </div>

        </div>
    </div>
    @endforeach
</div>
@endif
```

**أضف في `resources/views/livewire/store/product-list.blade.php`:**
ابحث عن أول `<div>` رئيسي داخل الـ layout وأضف قبل grid المنتجات مباشرة:
```blade
<x-store.combo-offer-banner />
```

**أضف في المكون المسؤول عن الصفحة الرئيسية** (`resources/views/livewire/store/home.blade.php` أو ما يعادله):
```blade
{{-- بعد الـ Hero Slider مباشرة --}}
<x-store.combo-offer-banner />
```

---

### نقطة 2 — مؤشر تقدم "اقترب من العرض" في صفحة المنتج

**المكان:** `resources/views/livewire/store/product-details.blade.php`

**السلوك:**
- لو في كومبو نشط يشترط هذا الصنف، يظهر تحت زر "أضف للسلة" مباشرة
- لو العميل عنده بالفعل بعض الأصناف المطلوبة في سلته، يظهر له تقدمه: "عندك 2 من 3 أصناف مطلوبة!"
- لو ما عندوش في السلة، يوضح له العرض بشكل جذاب

**الـ Blade Component:** `resources/views/components/store/combo-progress-hint.blade.php`

```blade
{{-- يُستدعى من صفحة تفاصيل المنتج --}}
{{-- $product متاح من الـ parent component --}}

@php
    // جلب الكومبو اللي يشمل هذا المنتج أو صنفه
    $relevantRules = \App\Models\ComboRule::with('conditions')
        ->where('is_active', true)
        ->get()
        ->filter(function ($rule) use ($product) {
            return $rule->isCurrentlyActive() && $rule->conditions->contains(function ($c) use ($product) {
                return ($c->condition_type === 'category' && $c->condition_id == $product->category_id)
                    || ($c->condition_type === 'product' && $c->condition_id == $product->id);
            });
        });

    if ($relevantRules->isEmpty()) return;

    // جلب محتوى السلة الحالية للمستخدم
    $cartCategoryIds = collect(session('cart', []))->pluck('category_id')->filter()->unique();
    // أو لو السلة eloquent-based:
    // $cartCategoryIds = auth('customer')->check()
    //     ? auth('customer')->user()->cart?->items->pluck('product.category_id')->filter()->unique()
    //     : collect();

    $bestRule = $relevantRules->first();
    $totalConditions = $bestRule->conditions->count();
    $metConditions = $bestRule->conditions->filter(function ($c) use ($cartCategoryIds) {
        return $cartCategoryIds->contains($c->condition_id);
    })->count();
    // +1 للمنتج الحالي نفسه لأنهم ناوين يضيفوه
    $metWithCurrent = min($metConditions + 1, $totalConditions);
@endphp

@if($bestRule)
<div class="mt-4 p-4 rounded-xl border-2 border-dashed border-violet-300 bg-violet-50">
    <div class="flex items-start gap-3">
        <span class="text-2xl flex-shrink-0">🎁</span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-violet-800 text-sm">{{ $bestRule->name }}</p>

            {{-- شريط التقدم --}}
            <div class="mt-2 flex gap-1.5">
                @for($i = 0; $i < $totalConditions; $i++)
                <div class="flex-1 h-2 rounded-full {{ $i < $metWithCurrent ? 'bg-violet-600' : 'bg-violet-200' }}"></div>
                @endfor
            </div>

            <p class="text-violet-600 text-xs mt-1.5">
                @if($metWithCurrent >= $totalConditions)
                    ✅ {{ __('messages.combo_ready_to_apply') }}
                @else
                    {{ __('messages.combo_x_of_y', ['done' => $metWithCurrent, 'total' => $totalConditions]) }}
                @endif
            </p>

            {{-- الأصناف المطلوبة --}}
            <div class="flex flex-wrap gap-1 mt-2">
                @foreach($bestRule->conditions as $condition)
                <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full">
                    {{ $condition->condition_name }}
                </span>
                @endforeach
            </div>
        </div>

        {{-- الخصم --}}
        <div class="flex-shrink-0 bg-violet-600 text-white font-black text-lg rounded-lg px-3 py-1.5 leading-none">
            @if($bestRule->discount_type === 'percentage')
                {{ $bestRule->discount_value }}%
            @else
                {{ number_format($bestRule->discount_value) }}
            @endif
        </div>
    </div>
</div>
@endif
```

**أضف في `resources/views/livewire/store/product-details.blade.php`:**
ابحث عن زر "أضف للسلة" وأضف بعده مباشرة:
```blade
<x-store.combo-progress-hint :product="$product" />
```

---

### نقطة 3 — تأكيد الخصم في صفحة السلة

**المكان:** `resources/views/livewire/store/cart-page.blade.php`

**السلوك:**
- لو الكومبو اتطبّق: يظهر بانر أخضر/بنفسجي احتفالي مع اسم العرض وقيمة الخصم
- لو الكومبو ما اتطبقش لكن العميل اقترب منه (عنده n-1 صنف): يظهر تلميح "أضف منتجاً من صنف X وفّر Y"
- الخصم يظهر في ملخص الطلب بين الـ subtotal والـ total بنفس أسلوب خصم الكوبون

**أضف في `resources/views/livewire/store/cart-page.blade.php`:**

```blade
{{-- === بانر الكومبو المطبّق === --}}
@if(session('combo_discount') > 0)
<div class="mb-4 rounded-xl overflow-hidden shadow-sm">
    <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white">
            <span class="text-xl">🎉</span>
            <div>
                <p class="font-bold text-sm">{{ __('messages.combo_discount_applied') }}</p>
                <p class="text-white/75 text-xs">{{ session('combo_rule_name') }}</p>
            </div>
        </div>
        <span class="bg-yellow-400 text-yellow-900 font-black text-base rounded-lg px-3 py-1">
            - {{ number_format(session('combo_discount'), 2) }} ج.م
        </span>
    </div>
</div>
@else
    {{-- === تلميح: اقترب من الكومبو === --}}
    @php
        // هذا اللوجيك يُحسب في CartPage.php (Livewire component)
        // ويُمرَّر كـ property: $nearbyComboHint
        // شوف الـ CartPage.php section أدناه
    @endphp
    @if(isset($nearbyComboHint) && $nearbyComboHint)
    <div class="mb-4 p-3 rounded-xl border border-amber-200 bg-amber-50 flex items-center gap-3">
        <span class="text-xl flex-shrink-0">💡</span>
        <p class="text-sm text-amber-800">{{ $nearbyComboHint }}</p>
    </div>
    @endif
@endif

{{-- === في ملخص الطلب (Order Summary) === --}}
{{-- ابحث عن سطر خصم الكوبون وأضف بجانبه: --}}
@if(session('combo_discount') > 0)
<div class="flex justify-between text-sm text-violet-700 font-medium">
    <span>{{ __('messages.combo_offer') }} 🎁</span>
    <span>- {{ number_format(session('combo_discount'), 2) }} ج.م</span>
</div>
@endif
```

---

### المنطق في `app/Livewire/Store/CartPage.php`

في `mount()` أو `updated()` أضف:

```php
public string $nearbyComboHint = '';

public function calculateNearbyComboHint(): void
{
    // لو الكومبو اتطبّق فعلاً، ما نحتاجش hint
    if (session('combo_discount', 0) > 0) {
        $this->nearbyComboHint = '';
        return;
    }

    $cartItems = $this->getCartItems(); // استخدم الـ method الموجودة
    $cartCategoryIds = $cartItems->pluck('product.category_id')->filter()->unique();

    $rules = \App\Models\ComboRule::with('conditions')
        ->where('is_active', true)
        ->get()
        ->filter(fn ($r) => $r->isCurrentlyActive());

    foreach ($rules as $rule) {
        $total = $rule->conditions->count();
        $met   = $rule->conditions->filter(
            fn ($c) => $c->condition_type === 'category'
                       && $cartCategoryIds->contains($c->condition_id)
        )->count();

        // لو ناقص صنف واحد بس
        if ($met === $total - 1) {
            $missingCondition = $rule->conditions->first(
                fn ($c) => ! $cartCategoryIds->contains($c->condition_id)
            );
            $discountLabel = $rule->discount_type === 'percentage'
                ? "{$rule->discount_value}%"
                : number_format($rule->discount_value) . ' ج.م';

            $this->nearbyComboHint = __('messages.combo_almost_there', [
                'category' => $missingCondition->condition_name,
                'discount' => $discountLabel,
                'name'     => $rule->name,
            ]);
            return;
        }
    }

    $this->nearbyComboHint = '';
}
```

---

## 👨‍💼 الجزء الثاني: تجربة الأدمين في لوحة التحكم (Admin Workflow)

### الهدف: الأدمين يقدر ينشئ كومبو في أقل من دقيقتين

---

### الخطوة في Filament — نموذج الإنشاء

الأدمين بيروح لـ `/admin/combo-rules/create` ويملأ:

1. **اسم العرض** — مثلاً "كومبو نضارة الصيف"
2. **وصف مختصر** — يظهر للعميل في البانر
3. **نوع الخصم** — نسبة مئوية أو مبلغ ثابت
4. **قيمة الخصم** — 15 أو 50 ج.م
5. **تاريخ البداية والنهاية** — لو موسمي
6. **الحد الأقصى للاستخدام** — لو عايز يحدد عدد المرات
7. **الشروط** عبر Repeater — يضيف صنف أو منتج لكل شرط

**تأكد أن فورم الـ Filament يتضمن:**

```php
// في ComboRuleResource::form()

Forms\Components\Section::make('معاينة البانر')
    ->description('هكذا سيظهر العرض للعملاء في الموقع')
    ->schema([
        Forms\Components\Placeholder::make('preview')
            ->content(new \Illuminate\Support\HtmlString(
                '<div class="p-4 rounded-xl bg-gradient-to-r from-violet-600 to-purple-700 text-white text-center">
                    <p class="font-bold">معاينة العرض ستظهر هنا بعد الحفظ</p>
                 </div>'
            )),
    ])
    ->collapsible()
    ->collapsed(),
```

---

### صفحة قائمة الكومبو (List Page) — معلومات واضحة دفعة واحدة

في `ComboRuleResource::table()` تأكد إن الجدول يعرض:

```php
Tables\Columns\TextColumn::make('name')
    ->label('اسم العرض')
    ->description(fn ($record) => $record->description)
    ->searchable(),

Tables\Columns\TextColumn::make('discount_value')
    ->label('الخصم')
    ->formatStateUsing(fn ($state, $record) =>
        $record->discount_type === 'percentage' ? "{$state}%" : "{$state} ج.م"
    )
    ->badge()
    ->color('success'),

// عمود يعرض الأصناف المطلوبة كـ tags
Tables\Columns\TextColumn::make('conditions_summary')
    ->label('الأصناف المطلوبة')
    ->getStateUsing(fn ($record) =>
        $record->conditions->map(fn ($c) => $c->condition_name)->implode(' + ')
    ),

Tables\Columns\TextColumn::make('uses_count')
    ->label('استُخدم')
    ->suffix(fn ($record) => $record->max_uses ? " / {$record->max_uses}" : ' مرة')
    ->color(fn ($record) =>
        $record->max_uses && $record->uses_count >= $record->max_uses * 0.9
            ? 'danger' : 'gray'
    ),

Tables\Columns\IconColumn::make('is_active')
    ->label('نشط')
    ->boolean(),

Tables\Columns\TextColumn::make('ends_at')
    ->label('ينتهي')
    ->since()           // يعرض "بعد 3 أيام" بدل التاريخ الكامل
    ->color(fn ($record) =>
        $record->ends_at && $record->ends_at->diffInDays() < 3 ? 'danger' : 'gray'
    ),
```

**أضف Action سريع لتفعيل/تعطيل العرض:**
```php
Tables\Actions\Action::make('toggle_active')
    ->label(fn ($record) => $record->is_active ? 'تعطيل' : 'تفعيل')
    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
    ->requiresConfirmation(),
```

---

## 📁 السياق الكامل للمشروع

### المسار
`c:\laragon\www\violet`

### الملفات ذات الصلة التي ستتعامل معها

```
app/
├── Models/
│   ├── Order.php              ← ستضيف علاقة للكومبو
│   ├── OrderItem.php
│   ├── Category.php
│   └── Product.php
├── Services/
│   ├── CartService.php        ← ستضيف استدعاء ComboDiscountService هنا
│   └── OrderService.php       ← ستضيف تسجيل الكومبو المطبّق
├── Livewire/Store/
│   ├── CartPage.php           ← ستضيف nearbyComboHint property والـ method
│   └── CheckoutPage.php       ← ستستخدم الخصم المحسوب
app/Filament/Resources/        ← ستضيف ComboRuleResource هنا
database/migrations/           ← ستضيف migration جديد
resources/views/
├── components/store/
│   ├── combo-offer-banner.blade.php     ← جديد
│   └── combo-progress-hint.blade.php   ← جديد
└── livewire/store/
    ├── product-list.blade.php           ← ستضيف x-store.combo-offer-banner
    ├── product-details.blade.php        ← ستضيف x-store.combo-progress-hint
    └── cart-page.blade.php              ← ستضيف بانر الكومبو + hint + سطر الخصم
```

### الـ Services الموجودة كمرجع للأسلوب
- `app/Services/CouponService.php` — الأسلوب المتبع لحساب الخصومات، اتبع نفس النمط
- `app/Services/CartService.php` — method `recalculateTotals()` هي نقطة الاندماج

### الـ Models المهمة
```php
// Order.php — الحقول الموجودة في جدول orders:
// subtotal, discount_amount, shipping_cost, total
// discount_code_id (nullable FK)
// → ستضيف: combo_rule_id (nullable FK)

// Cart.php — جلسة السلة
// CartService::recalculateTotals() هي الدالة التي تحسب الإجماليات
```

---

## 🗄️ الخطوة 1: قاعدة البيانات

### أنشئ Migration واحد باسم:
`create_combo_rules_tables`

### جدول `combo_rules`
```php
Schema::create('combo_rules', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
    $table->decimal('discount_value', 8, 2);
    $table->boolean('is_active')->default(true);
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('ends_at')->nullable();
    $table->integer('max_uses')->nullable();
    $table->integer('uses_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```

### جدول `combo_rule_conditions`
```php
Schema::create('combo_rule_conditions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('combo_rule_id')->constrained()->cascadeOnDelete();
    $table->enum('condition_type', ['category', 'product']);
    $table->unsignedBigInteger('condition_id');
    $table->integer('min_quantity')->default(1);
    $table->timestamps();
    $table->index(['combo_rule_id']);
    $table->index(['condition_type', 'condition_id']);
});
```

### جدول `combo_rule_usages`
```php
Schema::create('combo_rule_usages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('combo_rule_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->decimal('discount_applied', 8, 2);
    $table->timestamps();
});
```

### عدّل جدول `orders` في migration منفصل:
```php
Schema::table('orders', function (Blueprint $table) {
    $table->foreignId('combo_rule_id')->nullable()->after('discount_code_id')
          ->constrained('combo_rules')->nullOnDelete();
    $table->decimal('combo_discount_amount', 8, 2)->default(0)->after('discount_amount');
});
```

---

## 🏗️ الخطوة 2: Models

### أنشئ `app/Models/ComboRule.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComboRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'discount_type', 'discount_value',
        'is_active', 'starts_at', 'ends_at', 'max_uses', 'uses_count',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'discount_value' => 'decimal:2',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(ComboRuleCondition::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ComboRuleUsage::class);
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) return false;
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at   && $now->gt($this->ends_at))   return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round($subtotal * ($this->discount_value / 100), 2);
        }
        return min($this->discount_value, $subtotal);
    }
}
```

### أنشئ `app/Models/ComboRuleCondition.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboRuleCondition extends Model
{
    protected $fillable = ['combo_rule_id', 'condition_type', 'condition_id', 'min_quantity'];

    public function comboRule(): BelongsTo
    {
        return $this->belongsTo(ComboRule::class);
    }

    public function getConditionNameAttribute(): string
    {
        if ($this->condition_type === 'category') {
            return Category::find($this->condition_id)?->name ?? 'صنف محذوف';
        }
        return Product::find($this->condition_id)?->name ?? 'منتج محذوف';
    }
}
```

### أنشئ `app/Models/ComboRuleUsage.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboRuleUsage extends Model
{
    protected $fillable = ['combo_rule_id', 'order_id', 'discount_applied'];
    public function comboRule(): BelongsTo { return $this->belongsTo(ComboRule::class); }
    public function order(): BelongsTo     { return $this->belongsTo(Order::class); }
}
```

### عدّل `app/Models/Order.php` — أضف:
```php
// في $fillable أضف:
'combo_rule_id', 'combo_discount_amount',

// أضف العلاقة:
public function comboRule(): BelongsTo
{
    return $this->belongsTo(ComboRule::class);
}
```

---

## ⚙️ الخطوة 3: ComboDiscountService

### أنشئ `app/Services/ComboDiscountService.php`

```php
<?php

namespace App\Services;

use App\Models\ComboRule;
use App\Models\ComboRuleUsage;
use App\Models\Order;
use Illuminate\Support\Collection;

class ComboDiscountService
{
    /**
     * فحص السلة وإرجاع أفضل Combo Rule ينطبق (الأعلى قيمة)
     *
     * @param  Collection  $cartItems  مع علاقات product و product.category
     * @return array{rule: ComboRule|null, discount: float, matched_rule_id: int|null}
     */
    public function evaluate(Collection $cartItems): array
    {
        $empty = ['rule' => null, 'discount' => 0.0, 'matched_rule_id' => null];

        if ($cartItems->isEmpty()) return $empty;

        $rules = ComboRule::with('conditions')
            ->where('is_active', true)
            ->get()
            ->filter(fn ($rule) => $rule->isCurrentlyActive());

        if ($rules->isEmpty()) return $empty;

        $subtotal     = $cartItems->sum(fn ($item) => $item->price * $item->quantity);
        $bestRule     = null;
        $bestDiscount = 0.0;

        foreach ($rules as $rule) {
            if ($this->ruleMatchesCart($rule, $cartItems)) {
                $discount = $rule->calculateDiscount($subtotal);
                if ($discount > $bestDiscount) {
                    $bestDiscount = $discount;
                    $bestRule     = $rule;
                }
            }
        }

        if ($bestRule === null) return $empty;

        return [
            'rule'            => $bestRule,
            'discount'        => $bestDiscount,
            'matched_rule_id' => $bestRule->id,
        ];
    }

    private function ruleMatchesCart(ComboRule $rule, Collection $cartItems): bool
    {
        foreach ($rule->conditions as $condition) {
            $quantityFound = 0;
            foreach ($cartItems as $item) {
                if ($condition->condition_type === 'category') {
                    $categoryId = $item->product?->category_id ?? $item->category_id;
                    if ($categoryId == $condition->condition_id) {
                        $quantityFound += $item->quantity;
                    }
                } elseif ($condition->condition_type === 'product') {
                    if ($item->product_id == $condition->condition_id) {
                        $quantityFound += $item->quantity;
                    }
                }
            }
            if ($quantityFound < $condition->min_quantity) return false;
        }
        return true;
    }

    public function recordUsage(Order $order, ComboRule $rule, float $discountApplied): void
    {
        ComboRuleUsage::create([
            'combo_rule_id'    => $rule->id,
            'order_id'         => $order->id,
            'discount_applied' => $discountApplied,
        ]);
        $rule->increment('uses_count');
    }
}
```

---

## 🛒 الخطوة 4: دمج ComboDiscountService في CartService

### في `app/Services/CartService.php`

**أضف في أعلى الملف:**
```php
use App\Services\ComboDiscountService;
```

**عدّل constructor:**
```php
public function __construct(
    // ... الـ dependencies الموجودة كما هي ...
    private ComboDiscountService $comboDiscountService,
) {}
```

**في method `recalculateTotals()` — بعد حساب خصم الكوبون مباشرة:**
```php
// فحص خصم الكومبو
$comboResult   = $this->comboDiscountService->evaluate($cartItems);
$comboDiscount = $comboResult['discount'];
$comboRule     = $comboResult['rule'];

session([
    'combo_discount'             => $comboDiscount,
    'combo_rule_id'              => $comboResult['matched_rule_id'],
    'combo_rule_name'            => $comboRule?->name,
    'combo_rule_discount_type'   => $comboRule?->discount_type,
    'combo_rule_discount_value'  => $comboRule?->discount_value,
]);

// اندمج الكومبو في الـ total — اضبط هذا السطر حسب الحسابات الموجودة:
$total = $subtotal - $couponDiscount - $comboDiscount + $shippingCost;
```

> ⚠️ اقرأ `recalculateTotals()` كاملة أولاً — لا تعيد كتابة أي منطق موجود.

---

## 💰 الخطوة 5: تسجيل الكومبو عند إنشاء الطلب

### في `app/Services/OrderService.php`

```php
// أضف في أعلى الملف:
use App\Services\ComboDiscountService;

// في constructor:
private ComboDiscountService $comboDiscountService,

// في بيانات إنشاء الطلب (بجانب discount_code_id):
'combo_rule_id'         => session('combo_rule_id'),
'combo_discount_amount' => session('combo_discount', 0),

// بعد حفظ الطلب بنجاح:
if ($order->combo_rule_id && $order->combo_discount_amount > 0) {
    $this->comboDiscountService->recordUsage(
        $order,
        $order->comboRule,
        $order->combo_discount_amount
    );
    session()->forget([
        'combo_discount', 'combo_rule_id', 'combo_rule_name',
        'combo_rule_discount_type', 'combo_rule_discount_value',
    ]);
}
```

---

## 🛠️ الخطوة 6: Filament Resource للإدارة

### أنشئ `app/Filament/Resources/ComboRuleResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComboRuleResource\Pages;
use App\Models\Category;
use App\Models\ComboRule;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ComboRuleResource extends Resource
{
    protected static ?string $model = ComboRule::class;
    protected static ?string $navigationIcon  = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel         = 'عرض كومبو';
    protected static ?string $pluralModelLabel   = 'عروض الكومبو';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات العرض')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم العرض')
                    ->required()->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('وصف يظهر للعميل في الموقع')
                    ->rows(2),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('discount_type')
                        ->label('نوع الخصم')
                        ->options(['percentage' => 'نسبة مئوية %', 'fixed' => 'مبلغ ثابت'])
                        ->required()->live(),

                    Forms\Components\TextInput::make('discount_value')
                        ->label('قيمة الخصم')
                        ->numeric()->required()
                        ->suffix(fn ($get) => $get('discount_type') === 'percentage' ? '%' : 'ج.م'),
                ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\DateTimePicker::make('starts_at')->label('يبدأ في'),
                    Forms\Components\DateTimePicker::make('ends_at')->label('ينتهي في'),
                ]),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('max_uses')
                        ->label('الحد الأقصى للاستخدام')
                        ->numeric()->nullable()
                        ->helperText('اتركه فارغاً للاستخدام غير المحدود'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط الآن')
                        ->default(true),
                ]),
            ]),

            Forms\Components\Section::make('شروط الكومبو')
                ->description('يجب أن تتحقق كل الشروط في نفس الطلب للحصول على الخصم.')
                ->schema([
                    Forms\Components\Repeater::make('conditions')
                        ->relationship()
                        ->label('الشروط')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('condition_type')
                                    ->label('النوع')
                                    ->options(['category' => 'صنف (Category)', 'product' => 'منتج محدد'])
                                    ->required()->live(),

                                Forms\Components\Select::make('condition_id')
                                    ->label('الاختيار')
                                    ->required()
                                    ->options(fn ($get) =>
                                        $get('condition_type') === 'category'
                                            ? Category::pluck('name', 'id')
                                            : Product::pluck('name', 'id')
                                    )
                                    ->searchable(),

                                Forms\Components\TextInput::make('min_quantity')
                                    ->label('الحد الأدنى')
                                    ->numeric()->default(1)->minValue(1),
                            ]),
                        ])
                        ->minItems(2)
                        ->addActionLabel('+ أضف شرط جديد')
                        ->reorderable(false)
                        ->helperText('أدنى حد: شرطان — أي صنفان مختلفان.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم العرض')
                    ->description(fn ($record) => $record->description)
                    ->searchable(),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('الخصم')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->discount_type === 'percentage' ? "{$state}%" : "{$state} ج.م"
                    )
                    ->badge()->color('success'),

                Tables\Columns\TextColumn::make('conditions_summary')
                    ->label('الأصناف المطلوبة')
                    ->getStateUsing(fn ($record) =>
                        $record->conditions->map(fn ($c) => $c->condition_name)->implode(' + ')
                    ),

                Tables\Columns\TextColumn::make('uses_count')
                    ->label('استُخدم')
                    ->suffix(fn ($record) =>
                        $record->max_uses ? " / {$record->max_uses} مرة" : ' مرة'
                    )
                    ->color(fn ($record) =>
                        $record->max_uses && $record->uses_count >= $record->max_uses * 0.9
                            ? 'danger' : 'gray'
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')->boolean(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('ينتهي')
                    ->since()
                    ->color(fn ($record) =>
                        $record->ends_at && $record->ends_at->diffInDays() < 3
                            ? 'danger' : 'gray'
                    ),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('النشطة فقط'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'تعطيل' : 'تفعيل')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListComboRules::route('/'),
            'create' => Pages\CreateComboRule::route('/create'),
            'edit'   => Pages\EditComboRule::route('/{record}/edit'),
        ];
    }
}
```

### أنشئ صفحات الـ Resource (3 ملفات):

**`app/Filament/Resources/ComboRuleResource/Pages/ListComboRules.php`**
```php
<?php
namespace App\Filament\Resources\ComboRuleResource\Pages;
use App\Filament\Resources\ComboRuleResource;
use Filament\Resources\Pages\ListRecords;
class ListComboRules extends ListRecords {
    protected static string $resource = ComboRuleResource::class;
}
```

**`app/Filament/Resources/ComboRuleResource/Pages/CreateComboRule.php`**
```php
<?php
namespace App\Filament\Resources\ComboRuleResource\Pages;
use App\Filament\Resources\ComboRuleResource;
use Filament\Resources\Pages\CreateRecord;
class CreateComboRule extends CreateRecord {
    protected static string $resource = ComboRuleResource::class;
}
```

**`app/Filament/Resources/ComboRuleResource/Pages/EditComboRule.php`**
```php
<?php
namespace App\Filament\Resources\ComboRuleResource\Pages;
use App\Filament\Resources\ComboRuleResource;
use Filament\Resources\Pages\EditRecord;
class EditComboRule extends EditRecord {
    protected static string $resource = ComboRuleResource::class;
}
```

---

## 🌍 الخطوة 7: الترجمات

### في `lang/ar/messages.php` أضف:
```php
'combo_discount_applied'  => 'تم تطبيق خصم الكومبو! 🎉',
'combo_offer'             => 'عرض الكومبو',
'combo_off'               => 'خصم',
'combo_almost_there'      => 'أضف منتجاً من ":category" وفّر :discount — عرض ":name"',
'combo_x_of_y'            => ':done من :total أصناف — أكمل العرض!',
'combo_ready_to_apply'    => 'رائع! سيُطبَّق الخصم تلقائياً عند الإضافة للسلة',
```

### في `lang/en/messages.php` أضف:
```php
'combo_discount_applied'  => 'Combo discount applied! 🎉',
'combo_offer'             => 'Combo Offer',
'combo_off'               => 'OFF',
'combo_almost_there'      => 'Add an item from ":category" to save :discount — ":name" offer',
'combo_x_of_y'            => ':done of :total items — complete the combo!',
'combo_ready_to_apply'    => 'Great! Discount will apply automatically when added to cart',
```

---

## 🔗 الخطوة 8: ربط الـ Service في Container

### في `app/Providers/AppServiceProvider.php`:
```php
$this->app->singleton(\App\Services\ComboDiscountService::class);
```

---

## ✅ الخطوة 9: التحقق النهائي

```bash
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Checklist العميل:**
- [ ] بانر الكومبو يظهر في `/products` وفي الصفحة الرئيسية لو فيه كومبو نشط
- [ ] في صفحة المنتج (`/products/{slug}`) يظهر مؤشر التقدم تحت زر السلة لو المنتج ضمن كومبو
- [ ] لما السلة ناقصة صنف واحد يظهر تلميح "أضف من صنف X وفّر Y" في `/cart`
- [ ] لما السلة مكتملة يظهر البانر الاحتفالي البنفسجي وسطر الخصم في الملخص
- [ ] `combo_discount_amount` محفوظ في الطلب بعد الإتمام

**Checklist الأدمين:**
- [ ] `/admin/combo-rules` تفتح وتعرض الجدول بكل الأعمدة
- [ ] إنشاء كومبو جديد بشرطين يعمل بدون أخطاء
- [ ] زر تفعيل/تعطيل السريع يعمل من الجدول مباشرة
- [ ] عمود "الأصناف المطلوبة" يعرض الأسماء مفصولة بـ +

---

## ⛔ قواعد يجب الالتزام بها

1. **لا تعيد كتابة CartService أو OrderService كاملاً** — فقط أضف السطور المحددة
2. **لا تغير schema الجداول الموجودة** غير إضافة الحقلين في `orders`
3. **اتبع نفس أسلوب CouponService** في بناء ComboDiscountService
4. **الـ ComboDiscountService لا يعرف شيئاً عن الـ Session** — Session يتعامل معها CartService فقط
5. **لو كان هناك خصم كوبون وخصم كومبو في نفس الوقت** — يُطبَّقان معاً ولا تلغِ أحدهما
6. **الـ cache للبانر** — استخدم `cache()->remember('active_combo_rules', 600, ...)` لتجنب query في كل page load
7. **الـ Filament Resource** في نفس navigation group الخاصة بـ CouponResource (مجموعة "Sales")

---

*نظام Violet E-Commerce — مهمة Combo Discount — يونيو 2026*
