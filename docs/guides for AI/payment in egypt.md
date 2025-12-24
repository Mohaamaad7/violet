# دليل شامل لدمج وسائل الدفع المصرية في Laravel 12 مع Filament 4

**فودافون كاش وإنستاباي** هما أبرز وسائل الدفع الإلكتروني في مصر، لكن التكامل البرمجي يتم حصرياً عبر بوابات وسيطة مثل **Paymob** التي توفر APIs موثقة وبيئات اختبار جاهزة. لا توجد APIs رسمية مباشرة من فودافون أو البنك المركزي للمطورين، مما يجعل الاعتماد على Payment Gateways ضرورة تقنية. الحل الأمثل هو استخدام package مثل `nafezly/payments` مع Paymob، التي تدعم **30+ بوابة دفع** بما فيها جميع المحافظ المصرية.

---

## فودافون كاش: المحفظة الأكثر انتشاراً في مصر

فودافون كاش هي محفظة إلكترونية على الهاتف المحمول تقدمها فودافون مصر بالتعاون مع بنك HSBC، وتخدم حالياً **25 مليون مستخدم** بحصة سوقية تتجاوز **55%** من إجمالي المحافظ الإلكترونية. تتيح للمستخدمين إجراء التحويلات المالية، دفع الفواتير، الشراء عبر الإنترنت باستخدام بطاقة افتراضية، والدفع عبر QR Code في المتاجر.

**الحدود والرسوم (2025):**
- الحد اليومي للمعاملات: **60,000 جنيه**
- الحد الشهري: **200,000 جنيه**
- رسوم التحويل لمحفظة فودافون كاش: **1 جنيه**
- رسوم التحويل لمحافظ أخرى: **0.5%** (حد أقصى 15 جنيه)
- إنشاء بطاقة افتراضية للدفع أونلاين: **10 جنيه**

**لا يوجد API رسمي مباشر** من فودافون مصر للمطورين. التكامل متاح فقط عبر وسطاء الدفع المرخصين مثل Paymob وFawaterak.

---

## إنستاباي: البنية التحتية الوطنية للمدفوعات اللحظية

إنستاباي هو التطبيق الوطني للمدفوعات اللحظية الذي أطلقته شركة البنوك المصرية (EBC) بتوجيهات من البنك المركزي المصري في مارس 2022. يخدم حالياً **12 مليون مستخدم** ويتيح تحويلات فورية 24/7 بين الحسابات البنكية والمحافظ الإلكترونية، مع دعم التحويل برقم الهاتف أو IPA أو IBAN.

**الحدود والرسوم (بدءاً من أبريل 2025):**
- المعاملة الواحدة: **70,000 جنيه**
- الحد اليومي: **120,000 جنيه**
- الحد الشهري: **400,000 جنيه**
- رسوم التحويلات: **0.1%** (حد أقصى 20 جنيه)

**لا يوجد API عام للمطورين حالياً.** Paymob تعلن أن دعم InstaPay **"Coming Soon"**. التكامل الحالي للتجار يتم عبر QR Code أو Payment Links فقط.

### المقارنة التقنية بين النظامين

| المعيار | فودافون كاش | إنستاباي |
|---------|-------------|----------|
| **نوع الخدمة** | محفظة إلكترونية | شبكة مدفوعات بين البنوك |
| **عدد المستخدمين** | 25 مليون | 12 مليون |
| **API للمطورين** | عبر Paymob/Fawaterak ✅ | غير متاح حالياً ❌ |
| **Sandbox** | متاحة عبر Paymob | غير متاحة |
| **سهولة التكامل** | سهل | صعب |

---

## Paymob: البوابة الأمثل للتكامل

Paymob هي بوابة الدفع الأكثر شمولية في مصر، وتوفر أفضل توثيق تقني ودعم لجميع وسائل الدفع المصرية.

**روابط التوثيق الأساسية:**
- بوابة المطورين: `https://developers.paymob.com`
- توثيق مصر: `https://developers.paymob.com/egypt`
- Payouts API: `https://payouts.paymobsolutions.com/docs/instant_cashin_api/`

**طرق الدفع المدعومة:**
- بطاقات Visa/Mastercard/Meeza
- المحافظ الإلكترونية: Vodafone Cash، Orange Money، Etisalat Cash، Meeza Wallet
- Apple Pay
- Kiosk (أمان/مصاري)
- أقساط البنوك
- BNPL: ValU، Souhoola، Halan، SYMPL، وغيرها

**الرسوم:**
- البطاقات: **2.75% + 3 جنيه** لكل معاملة ناجحة
- لا رسوم شهرية أو اشتراك
- التسوية أسبوعياً إلى الحساب البنكي

**عملية التسجيل:**
1. التسجيل في `https://accept.paymob.com`
2. رفع السجل التجاري والبطاقة الضريبية وهوية المالك
3. انتظار التحقق (حتى 3 أيام)
4. الحصول على API Key وIntegration ID من Dashboard

**بيانات الاختبار (Sandbox):**
```
Card Number: 4987654321098769
Expiry: 12/25
CVV: 123
Mobile Wallet Test: 01010101010 (PIN & OTP: 123456)
```

### مقارنة بوابات الدفع المصرية

| الميزة | Paymob | Fawry | Kashier |
|--------|--------|-------|---------|
| **رسوم البطاقات** | 2.75% + 3 EGP | ~2.50% | 2.85% + 3 EGP |
| **رسوم شهرية** | لا | 500 EGP | لا |
| **فودافون كاش** | ✅ | ✅ | ✅ |
| **InstaPay** | 🔜 قريباً | ❌ | ❌ |
| **جودة التوثيق** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Mobile SDK** | iOS, Android, Flutter, React Native | iOS, Android | محدود |

---

## Laravel Packages للدفع المصري

### nafezly/payments (الموصى به بشدة)

أفضل package شاملة للدفع في مصر مع دعم **30+ بوابة** وجميع المحافظ الإلكترونية المصرية.

```bash
composer require nafezly/payments dev-master
php artisan vendor:publish --tag="nafezly-payments-config"
```

| المعيار | التفاصيل |
|---------|----------|
| **GitHub** | github.com/Nafezly/payments |
| **النجوم** | 458 ⭐ |
| **Downloads** | 15,535+ |
| **Laravel المدعوم** | 6.0+ (يدعم Laravel 11/12 نظرياً) |

**متغيرات البيئة (.env):**
```env
PAYMOB_API_KEY=your_api_key
PAYMOB_INTEGRATION_ID=your_integration_id
PAYMOB_IFRAME_ID=your_iframe_id
PAYMOB_HMAC=your_hmac_secret
PAYMOB_WALLET_INTEGRATION_ID=your_wallet_integration_id
```

### Packages بديلة

| Package | Downloads | آخر تحديث | Laravel 11/12 |
|---------|-----------|-----------|---------------|
| basketin/laravel-paymob | 2,170 | Aug 2024 | ✅ |
| madarit/laravel-kashier | جديدة | Nov 2025 | ✅ |
| paymob/laravel-package (رسمية) | 270 | May 2024 | ⚠️ |

---

## Laravel 12 و Filament 4: أحدث الإصدارات

### Laravel 12

صدر في **24 فبراير 2025** كإصدار صيانة مع الحد الأدنى من التغييرات الجذرية. معظم تطبيقات Laravel يمكنها الترقية دون تعديل الكود. يتطلب **PHP 8.2+**.

**لا توجد تغييرات تؤثر على Payment Integrations** - نظام Queue وNotifications والDatabase يعمل كما في Laravel 11.

### Filament 4

صدرت النسخة المستقرة في ديسمبر 2025 مع تحسينات أداء هائلة: **سرعة عرض الجداول أسرع 2-3x**، ونظام Schema موحد للـ Forms والTables، ودعم Nested Resources وMFA المدمج.

**المتطلبات:** PHP 8.2+، Laravel 11.28+، Tailwind CSS v4.1+

---

## Database Structure للمدفوعات

### Migration للمدفوعات

```php
// database/migrations/create_payments_table.php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->nullable()->constrained();
    $table->string('reference')->unique();
    $table->string('transaction_id')->nullable()->unique();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('EGP');
    $table->enum('payment_method', [
        'vodafone_cash', 'orange_money', 'etisalat_cash',
        'credit_card', 'meeza', 'fawry', 'instapay'
    ]);
    $table->enum('status', [
        'pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'
    ])->default('pending');
    $table->string('gateway')->default('paymob');
    $table->string('gateway_reference')->nullable();
    $table->json('gateway_response')->nullable();
    $table->string('failure_reason')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('refunded_at')->nullable();
    $table->string('idempotency_key')->unique()->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['user_id', 'status']);
    $table->index(['gateway', 'gateway_reference']);
    $table->index('created_at');
});
```

### Payment Model

```php
// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'order_id', 'reference', 'transaction_id',
        'amount', 'currency', 'payment_method', 'status',
        'gateway', 'gateway_reference', 'gateway_response',
        'failure_reason', 'paid_at', 'refunded_at',
        'idempotency_key', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'encrypted:array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' 
            && $this->paid_at->diffInDays(now()) <= 30;
    }

    public function markAsCompleted(string $transactionId): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    // Generate unique reference
    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        } while (self::where('reference', $reference)->exists());
        
        return $reference;
    }
}
```

---

## Payment Service Architecture

### PaymentService

```php
// app/Services/PaymentService.php
namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Jobs\ProcessPaymentCallback;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nafezly\Payments\Facades\Payments;

class PaymentService
{
    public function initiatePayment(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            // إنشاء سجل الدفع
            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $data['order_id'] ?? null,
                'reference' => Payment::generateReference(),
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'EGP',
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
                'gateway' => 'paymob',
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // تحديد البوابة حسب وسيلة الدفع
            $gateway = $this->resolveGateway($data['payment_method']);
            
            // إنشاء طلب الدفع
            $response = Payments::$gateway()->pay(
                amount: $payment->amount,
                user_id: $user->id,
                user_first_name: $user->first_name,
                user_last_name: $user->last_name,
                user_email: $user->email,
                user_phone: $user->phone,
                source: $data['source'] ?? $user->phone,
            );

            // تحديث السجل بمعلومات البوابة
            $payment->update([
                'gateway_reference' => $response['payment_id'] ?? null,
                'gateway_response' => $response,
            ]);

            Log::channel('payments')->info('Payment initiated', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'amount' => $payment->amount,
                'method' => $payment->payment_method,
            ]);

            return [
                'payment' => $payment,
                'redirect_url' => $response['redirect_url'] ?? null,
                'iframe_url' => $response['html'] ?? null,
            ];
        });
    }

    public function handleCallback(array $data): Payment
    {
        $payment = Payment::where('gateway_reference', $data['payment_id'])
            ->orWhere('reference', $data['merchant_order_id'] ?? null)
            ->firstOrFail();

        // التحقق من HMAC
        if (!$this->verifyHmac($data)) {
            Log::channel('payments')->warning('Invalid HMAC', [
                'payment_id' => $payment->id,
                'data' => $data,
            ]);
            throw new \Exception('Invalid payment signature');
        }

        // تحديث حالة الدفع
        if ($data['success'] === 'true' || $data['success'] === true) {
            $payment->markAsCompleted($data['transaction_id']);
            $payment->user->notify(new PaymentSuccessNotification($payment));
        } else {
            $payment->markAsFailed($data['error_message'] ?? 'Payment declined');
            $payment->user->notify(new PaymentFailedNotification($payment));
        }

        return $payment->fresh();
    }

    public function refund(Payment $payment, ?float $amount = null): bool
    {
        if (!$payment->canBeRefunded()) {
            throw new \Exception('Payment cannot be refunded');
        }

        $refundAmount = $amount ?? $payment->amount;
        
        $response = Payments::paymob()->refund(
            transaction_id: $payment->transaction_id,
            amount: $refundAmount * 100, // بالقروش
        );

        if ($response['success']) {
            $payment->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    protected function resolveGateway(string $method): string
    {
        return match($method) {
            'vodafone_cash', 'orange_money', 'etisalat_cash' => 'paymobWallet',
            'credit_card', 'meeza' => 'paymob',
            'fawry' => 'fawry',
            default => 'paymob',
        };
    }

    protected function verifyHmac(array $data): bool
    {
        $hmacSecret = config('nafezly-payments.PAYMOB_HMAC');
        
        // ترتيب الحقول حسب توثيق Paymob
        $fields = [
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order.id', 'owner', 'pending',
            'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success'
        ];
        
        $concatenated = '';
        foreach ($fields as $field) {
            $concatenated .= data_get($data, $field, '');
        }
        
        $calculatedHmac = hash_hmac('sha512', $concatenated, $hmacSecret);
        
        return hash_equals($calculatedHmac, $data['hmac'] ?? '');
    }
}
```

### PaymentController

```php
// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Http\Requests\InitiatePaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function initiate(InitiatePaymentRequest $request)
    {
        $result = $this->paymentService->initiatePayment(
            user: auth()->user(),
            data: $request->validated()
        );

        if ($result['redirect_url']) {
            return redirect()->away($result['redirect_url']);
        }

        return response()->json([
            'success' => true,
            'payment' => $result['payment'],
            'iframe' => $result['iframe_url'],
        ]);
    }

    public function callback(Request $request)
    {
        try {
            $payment = $this->paymentService->handleCallback($request->all());
            
            return redirect()->route('payment.result', [
                'status' => $payment->status,
                'reference' => $payment->reference,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('payment.result', [
                'status' => 'failed',
                'error' => 'Payment verification failed',
            ]);
        }
    }

    public function webhook(Request $request)
    {
        // معالجة في الخلفية للسرعة
        ProcessPaymentCallback::dispatch($request->all());
        
        return response()->json(['status' => 'received'], 200);
    }
}
```

### Routes

```php
// routes/web.php
Route::middleware(['auth'])->prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate'])
        ->name('payment.initiate')
        ->middleware('throttle:payments');
    
    Route::get('/callback', [PaymentController::class, 'callback'])
        ->name('payment.callback')
        ->withoutMiddleware(['auth']);
    
    Route::get('/result', [PaymentController::class, 'result'])
        ->name('payment.result');
});

// Webhook route - بدون CSRF
Route::post('/webhooks/paymob', [PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

---

## Filament 4 Integration

### PaymentResource

```php
// app/Filament/Resources/PaymentResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payment Details')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->disabled(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('EGP')
                        ->required(),
                    Forms\Components\Select::make('payment_method')
                        ->options([
                            'vodafone_cash' => 'Vodafone Cash',
                            'orange_money' => 'Orange Money',
                            'etisalat_cash' => 'Etisalat Cash',
                            'credit_card' => 'Credit Card',
                            'meeza' => 'Meeza',
                            'fawry' => 'Fawry',
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'completed' => 'Completed',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                        ])
                        ->required(),
                ])->columns(2),
            
            Forms\Components\Section::make('Gateway Information')
                ->schema([
                    Forms\Components\TextInput::make('transaction_id')
                        ->disabled(),
                    Forms\Components\TextInput::make('gateway_reference')
                        ->disabled(),
                    Forms\Components\Textarea::make('failure_reason')
                        ->disabled()
                        ->columnSpanFull(),
                ])->columns(2)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'vodafone_cash' => 'Vodafone Cash',
                        'credit_card' => 'Credit Card',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'vodafone_cash' => 'Vodafone Cash',
                        'credit_card' => 'Credit Card',
                        'fawry' => 'Fawry',
                    ]),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Refund Payment')
                    ->modalDescription('Are you sure you want to refund this payment?')
                    ->visible(fn (Payment $record) => $record->canBeRefunded())
                    ->action(function (Payment $record) {
                        app(PaymentService::class)->refund($record);
                        Notification::make()
                            ->title('Payment refunded successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn ($records) => /* export logic */),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
```

### PaymentStatsWidget

```php
// app/Filament/Widgets/PaymentStatsWidget.php
namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $todayRevenue = Payment::completed()
            ->whereDate('created_at', today())
            ->sum('amount');

        $monthlyRevenue = Payment::completed()
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $pendingCount = Payment::pending()->count();
        $failedToday = Payment::where('status', 'failed')
            ->whereDate('created_at', today())
            ->count();

        return [
            Stat::make('Today Revenue', number_format($todayRevenue, 2) . ' EGP')
                ->description('Total completed today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Monthly Revenue', number_format($monthlyRevenue, 2) . ' EGP')
                ->description('This month total')
                ->color('primary'),
            
            Stat::make('Pending Payments', $pendingCount)
                ->description('Awaiting processing')
                ->color('warning'),
            
            Stat::make('Failed Today', $failedToday)
                ->description('Need attention')
                ->color($failedToday > 0 ? 'danger' : 'gray'),
        ];
    }
}
```

---

## Security Best Practices

### Webhook Verification Middleware

```php
// app/Http/Middleware/VerifyPaymobWebhook.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyPaymobWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $hmacSecret = config('nafezly-payments.PAYMOB_HMAC');
        $receivedHmac = $request->input('hmac');
        
        if (!$receivedHmac) {
            abort(403, 'Missing HMAC signature');
        }
        
        // حساب HMAC المتوقع
        $data = $request->input('obj', []);
        $fields = [
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order.id', 'owner', 'pending',
            'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success'
        ];
        
        $concatenated = '';
        foreach ($fields as $field) {
            $concatenated .= data_get($data, $field, '');
        }
        
        $expectedHmac = hash_hmac('sha512', $concatenated, $hmacSecret);
        
        if (!hash_equals($expectedHmac, $receivedHmac)) {
            abort(403, 'Invalid HMAC signature');
        }
        
        return $next($request);
    }
}
```

### Rate Limiting للمدفوعات

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('payments', function ($request) {
        return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
    });
}
```

### Idempotency Middleware

```php
// app/Http/Middleware/EnsureIdempotency.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class EnsureIdempotency
{
    public function handle($request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');
        
        if (!$key) {
            return $next($request);
        }
        
        $cacheKey = "idempotency:{$key}";
        
        if ($cached = Cache::get($cacheKey)) {
            return response()->json($cached['data'], $cached['status'])
                ->header('Idempotency-Replayed', 'true');
        }
        
        $response = $next($request);
        
        Cache::put($cacheKey, [
            'data' => $response->getData(),
            'status' => $response->getStatusCode(),
        ], now()->addHours(24));
        
        return $response;
    }
}
```

---

## Testing

### Feature Test للمدفوعات

```php
// tests/Feature/PaymentTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_payment()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/payment/initiate', [
                'amount' => 100.00,
                'payment_method' => 'vodafone_cash',
                'source' => '01012345678',
            ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'payment']);
        
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending',
        ]);
    }

    public function test_webhook_updates_payment_status()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
            'gateway_reference' => '12345',
        ]);
        
        $webhookData = [
            'payment_id' => '12345',
            'success' => 'true',
            'transaction_id' => 'TXN_123',
            'hmac' => $this->generateValidHmac([...]),
        ];
        
        $response = $this->postJson('/webhooks/paymob', $webhookData);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'transaction_id' => 'TXN_123',
        ]);
    }

    public function test_rate_limiting_blocks_excessive_requests()
    {
        $user = User::factory()->create();
        
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user)
                ->postJson('/payment/initiate', [
                    'amount' => 100,
                    'payment_method' => 'credit_card',
                ]);
        }
        
        $response->assertStatus(429);
    }
}
```

---

## ملف التكوين الكامل

```php
// config/nafezly-payments.php
return [
    // Paymob Configuration
    'PAYMOB_API_KEY' => env('PAYMOB_API_KEY'),
    'PAYMOB_INTEGRATION_ID' => env('PAYMOB_INTEGRATION_ID'),
    'PAYMOB_IFRAME_ID' => env('PAYMOB_IFRAME_ID'),
    'PAYMOB_HMAC' => env('PAYMOB_HMAC'),
    'PAYMOB_WALLET_INTEGRATION_ID' => env('PAYMOB_WALLET_INTEGRATION_ID'),
    
    // Fawry Configuration
    'FAWRY_URL' => env('FAWRY_URL', 'https://atfawry.com'),
    'FAWRY_MERCHANT' => env('FAWRY_MERCHANT'),
    'FAWRY_SECRET' => env('FAWRY_SECRET'),
    
    // General Settings
    'VERIFY_ROUTE_NAME' => 'payment.callback',
    'APP_NAME' => env('APP_NAME', 'My App'),
];
```

```env
# .env
PAYMOB_API_KEY=your_api_key_here
PAYMOB_INTEGRATION_ID=your_integration_id
PAYMOB_IFRAME_ID=your_iframe_id
PAYMOB_HMAC=your_hmac_secret
PAYMOB_WALLET_INTEGRATION_ID=your_wallet_integration_id

FAWRY_URL=https://atfawry.com
FAWRY_MERCHANT=your_merchant_code
FAWRY_SECRET=your_secret_key
```

---

## الخلاصة والتوصيات

للتكامل الناجح مع وسائل الدفع المصرية في Laravel 12 مع Filament 4، اتبع هذه الخطوات:

1. **استخدم Paymob** كبوابة دفع رئيسية - تدعم جميع المحافظ المصرية وتوفر أفضل توثيق
2. **ثبّت nafezly/payments** - أشمل package للدفع المصري مع دعم 30+ بوابة
3. **طبّق Security Best Practices** - HMAC verification، Rate limiting، Idempotency
4. **استخدم Queue Jobs** لمعالجة Webhooks بشكل غير متزامن
5. **أنشئ Filament Resource** لإدارة المعاملات من لوحة التحكم

**إنستاباي غير متاح للتكامل المباشر حالياً** - انتظر دعم Paymob القادم أو استخدم QR Code كحل مؤقت.

---

## المصادر والروابط المهمة

- **Paymob Developer Portal**: https://developers.paymob.com/egypt
- **nafezly/payments Documentation**: https://github.com/Nafezly/payments
- **Laravel 12 Documentation**: https://laravel.com/docs/12.x
- **Filament 4 Documentation**: https://filamentphp.com/docs/4.x
- **فودافون كاش**: https://web.vodafone.com.eg/ar/vodafone-cash
- **إنستاباي**: https://www.instapay.eg