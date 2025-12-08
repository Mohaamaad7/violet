# 📧 نظام الإيميلات - Violet Email System

> **تاريخ التوثيق:** 8 ديسمبر 2025  
> **الإصدار:** 1.0.0  
> **الحالة:** ✅ مكتمل وجاهز للاختبار

---

## 📋 جدول المحتويات

1. [نظرة عامة](#نظرة-عامة)
2. [المتطلبات الأصلية](#المتطلبات-الأصلية)
3. [النقاش والقرارات](#النقاش-والقرارات)
4. [الحل النهائي](#الحل-النهائي)
5. [الملفات المُنشأة](#الملفات-المُنشأة)
6. [قوالب الإيميل](#قوالب-الإيميل)
7. [كيفية الاستخدام](#كيفية-الاستخدام)
8. [إعداد السيرفر](#إعداد-السيرفر)
9. [الاختبار](#الاختبار)

---

## 🎯 نظرة عامة

نظام إيميلات متكامل لمتجر Violet الإلكتروني يتضمن:
- قوالب إيميل جاهزة (HTML responsive)
- دعم كامل للعربية (RTL)
- تتبع حالة الإيميلات (sent, delivered, opened, failed)
- لوحة تحكم Filament لإدارة القوالب والسجلات
- **بدون أي اعتماديات خارجية على السيرفر**

---

## 📝 المتطلبات الأصلية

من ملف `AI_AGENT_INSTRUCTIONS_EMAIL_SYSTEM.md`:

### المطلوب:
1. ✅ نظام قوالب إيميل مرن
2. ✅ دعم MJML للإيميلات الـ responsive
3. ✅ تتبع الإيميلات المرسلة
4. ✅ لوحة تحكم Filament
5. ✅ قوالب جاهزة (تأكيد الطلب، ترحيب، استعادة كلمة المرور، إلخ)

---

## 💬 النقاش والقرارات

### المرحلة 1: اختيار التقنية

**السؤال:** كيف نحول MJML إلى HTML؟

**الخيارات المطروحة:**
1. `spatie/mjml-php` - يحتاج npm/Node.js على السيرفر
2. MJML API - خدمة خارجية مجانية
3. Pre-compiled HTML - تحويل محلي ورفع HTML جاهز

**القرار المبدئي:** استخدام `spatie/mjml-php`

---

### المرحلة 2: مشكلة npm على السيرفر

**المشكلة:** السيرفر لا يدعم npm

**الحل المقترح:** استخدام MJML API (https://mjml.io/api)

```
المستخدم: "لا انا معترض لسبب أمني بحت
المعلومات بالايميل قد تكون حساسه للغاية
ما ينفعش نهائي طرف تالت يشوفها حتى لو من باب المعالجة"
```

**✅ قرار أمني صحيح 100%**

---

### المرحلة 3: الحل النهائي

**الحلول المقترحة:**

1. **Pre-compile Templates** ⭐ (الأفضل)
   - تحويل MJML → HTML محلياً
   - رفع HTML الجاهز مع الكود
   - السيرفر يقرأ HTML مباشرة

2. **Pure HTML Templates**
   - كتابة HTML مباشرة (صعب للـ responsive)

3. **Blade Templates**
   - استخدام Laravel Blade العادي

```
المستخدم: "خليه قالب جاهز - اسهل"
```

**✅ القرار النهائي:** Pre-compiled HTML Templates

---

### المرحلة 4: إلغاء Queue

```
المستخدم: "قم بالغاء php artisan queue:work 
عاوز التنبيه فورا مش لازم اشغل الامر ده"
```

**التغيير:**
- ❌ `Mail::queue()` - يحتاج `php artisan queue:work`
- ✅ `Mail::send()` - إرسال فوري

---

## ✅ الحل النهائي

### المعمارية:

```
┌─────────────────────────────────────────────────────────┐
│                    Violet Email System                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────┐    ┌──────────────┐    ┌───────────┐ │
│  │ EmailService │───▶│TemplateMail │───▶│   SMTP    │ │
│  └──────────────┘    └──────────────┘    └───────────┘ │
│         │                   │                           │
│         ▼                   ▼                           │
│  ┌──────────────┐    ┌──────────────┐                  │
│  │EmailTemplate │    │  EmailLog    │                  │
│  │ (content_html)│    │  (tracking)  │                  │
│  └──────────────┘    └──────────────┘                  │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### المميزات:
- ✅ **بدون npm/Node.js** على السيرفر
- ✅ **بدون API خارجي** - الأمان أولاً
- ✅ **بدون Queue Worker** - إرسال فوري
- ✅ **HTML جاهز** - أداء عالي
- ✅ **RTL/Arabic** - دعم كامل

---

## 📁 الملفات المُنشأة

### Models (النماذج):

```
app/Models/
├── EmailTemplate.php    # قوالب الإيميل
└── EmailLog.php         # سجل الإيميلات المرسلة
```

### Services (الخدمات):

```
app/Services/
├── EmailTemplateService.php  # عرض القوالب واستبدال المتغيرات
└── EmailService.php          # إرسال الإيميلات
```

### Mail (البريد):

```
app/Mail/
└── TemplateMail.php     # Mailable class
```

### Filament Resources (لوحة التحكم):

```
app/Filament/Resources/
├── EmailTemplates/
│   ├── EmailTemplateResource.php
│   ├── Pages/
│   │   ├── CreateEmailTemplate.php
│   │   ├── EditEmailTemplate.php
│   │   └── ListEmailTemplates.php
│   ├── Schemas/
│   │   └── EmailTemplateForm.php
│   └── Tables/
│       └── EmailTemplatesTable.php
│
└── EmailLogs/
    ├── EmailLogResource.php
    ├── Pages/...
    ├── Schemas/...
    └── Tables/...
```

### Migrations (قاعدة البيانات):

```
database/migrations/
├── 2025_12_08_134718_create_email_templates_table.php
├── 2025_12_08_134728_create_email_logs_table.php
└── 2025_12_08_180000_add_content_html_to_email_templates.php
```

### Templates (القوالب الجاهزة):

```
resources/views/emails/templates/
├── order-confirmation.html      # تأكيد الطلب
├── order-status-update.html     # تحديث حالة الطلب
├── welcome.html                 # رسالة ترحيب
├── password-reset.html          # استعادة كلمة المرور
└── admin-new-order.html         # إشعار طلب جديد (للإدارة)
```

### Seeder:

```
database/seeders/
└── EmailTemplateSeeder.php      # يحمّل القوالب من HTML files
```

---

## 📧 قوالب الإيميل

### 1. تأكيد الطلب (order-confirmation)
- **النوع:** customer
- **التصنيف:** order
- **الموضوع:** `تم استلام طلبك #{{ order_number }}`
- **المتغيرات:**
  - `order_number`, `order_total`, `order_subtotal`
  - `order_shipping`, `order_discount`, `order_date`
  - `user_name`, `shipping_address`, `track_url`

### 2. تحديث حالة الطلب (order-status-update)
- **النوع:** customer
- **التصنيف:** order
- **الموضوع:** `تحديث على طلبك #{{ order_number }} - {{ order_status }}`
- **المتغيرات:**
  - `order_number`, `order_status`, `order_total`
  - `user_name`, `track_url`

### 3. رسالة ترحيب (welcome)
- **النوع:** customer
- **التصنيف:** auth
- **الموضوع:** `مرحباً بك في {{ app_name }}!`
- **المتغيرات:**
  - `user_name`, `app_name`, `app_url`

### 4. استعادة كلمة المرور (password-reset)
- **النوع:** customer
- **التصنيف:** auth
- **الموضوع:** `طلب استعادة كلمة المرور - {{ app_name }}`
- **المتغيرات:**
  - `user_name`, `reset_url`

### 5. إشعار طلب جديد للإدارة (admin-new-order)
- **النوع:** admin
- **التصنيف:** order
- **الموضوع:** `🛒 طلب جديد #{{ order_number }} - {{ order_total }}`
- **المتغيرات:**
  - `order_number`, `order_total`, `order_items_count`
  - `user_name`, `user_email`, `user_phone`
  - `shipping_address`, `shipping_city`

---

## 💻 كيفية الاستخدام

### إرسال إيميل تأكيد طلب:

```php
use App\Services\EmailService;

$emailService = app(EmailService::class);

$emailService->send(
    templateSlug: 'order-confirmation',
    recipientEmail: $order->user->email,
    variables: [
        'order_number' => $order->order_number,
        'order_total' => $order->formatted_total,
        'order_date' => $order->created_at->format('Y/m/d'),
        'user_name' => $order->user->name,
        'shipping_name' => $order->shipping_name,
        'shipping_address' => $order->shipping_address,
        'shipping_city' => $order->shipping_city,
        'shipping_governorate' => $order->shipping_governorate,
        'track_url' => route('orders.track', $order),
        // ... المزيد
    ],
    recipientName: $order->user->name,
    related: $order, // للربط بالسجل
);
```

### إرسال رسالة ترحيب:

```php
$emailService->sendWelcome($user);
```

### إرسال استعادة كلمة المرور:

```php
$emailService->sendPasswordReset($user, $resetUrl);
```

### إرسال تحديث حالة الطلب:

```php
$emailService->sendOrderStatusUpdate($order);
```

---

## 🖥️ إعداد السيرفر

### 1. سحب التحديثات:

```bash
git pull origin master
```

### 2. تثبيت الاعتماديات:

```bash
composer install --no-dev
```

### 3. تشغيل المايجريشن:

```bash
php artisan migrate
```

### 4. تحميل القوالب:

```bash
php artisan db:seed --class=EmailTemplateSeeder
```

### 5. مسح الكاش:

```bash
php artisan optimize:clear
```

### 6. إعداد SMTP في `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🧪 الاختبار

### اختبار يدوي سريع:

```bash
php artisan tinker
```

```php
// اختبار إرسال إيميل
$service = app(\App\Services\EmailService::class);

$log = $service->send(
    templateSlug: 'welcome',
    recipientEmail: 'test@example.com',
    variables: ['user_name' => 'أحمد'],
    recipientName: 'أحمد'
);

// التحقق من الحالة
$log->status; // should be 'sent'
```

### التحقق من القوالب:

```php
// عرض جميع القوالب
\App\Models\EmailTemplate::all()->pluck('name', 'slug');

// معاينة قالب
$template = \App\Models\EmailTemplate::findBySlug('order-confirmation');
$service = app(\App\Services\EmailTemplateService::class);
$html = $service->preview($template);
file_put_contents('preview.html', $html);
```

### التحقق من السجلات:

```php
// آخر 10 إيميلات
\App\Models\EmailLog::latest()->take(10)->get();

// الإيميلات الفاشلة
\App\Models\EmailLog::failed()->get();
```

### لوحة التحكم:

- **القوالب:** `/admin/email-templates`
- **السجلات:** `/admin/email-logs`

---

## 📊 جدول email_templates

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | اسم القالب (عربي) |
| slug | string | المعرف الفريد |
| type | enum | customer, admin, system |
| category | enum | order, auth, notification, marketing |
| description | text | وصف القالب |
| subject_ar | string | موضوع الإيميل (عربي) |
| subject_en | string | موضوع الإيميل (إنجليزي) |
| content_mjml | longtext | كود MJML الأصلي (للأرشيف) |
| content_html | longtext | HTML الجاهز للإرسال ✅ |
| available_variables | json | المتغيرات المتاحة |
| primary_color | string | اللون الأساسي |
| secondary_color | string | اللون الثانوي |
| logo_path | string | مسار اللوجو |
| is_active | boolean | مفعل/معطل |
| created_at | timestamp | - |
| updated_at | timestamp | - |
| deleted_at | timestamp | Soft delete |

---

## 📊 جدول email_logs

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| email_template_id | bigint | FK to email_templates |
| related_type | string | Polymorphic (Order, User, etc) |
| related_id | bigint | Polymorphic ID |
| recipient_email | string | إيميل المستلم |
| recipient_name | string | اسم المستلم |
| subject | string | الموضوع الفعلي |
| locale | string | ar, en |
| status | enum | pending, sent, delivered, opened, clicked, failed, bounced |
| error_message | text | رسالة الخطأ (إن وجد) |
| metadata | json | بيانات إضافية |
| sent_at | timestamp | وقت الإرسال |
| delivered_at | timestamp | وقت التسليم |
| opened_at | timestamp | وقت الفتح |
| clicked_at | timestamp | وقت النقر |
| created_at | timestamp | - |
| updated_at | timestamp | - |

---

## 🔒 ملاحظات أمنية

1. ✅ **لا يتم إرسال محتوى الإيميل لأي طرف ثالث**
2. ✅ **القوالب محفوظة محلياً في الكود**
3. ✅ **لا حاجة لـ npm أو Node.js على السيرفر**
4. ✅ **لا حاجة لـ MJML API**
5. ✅ **الإرسال عبر SMTP الخاص بك فقط**

---

## 📝 ملاحظات إضافية

### لإضافة قالب جديد:

1. أنشئ MJML محلياً
2. حوّله لـ HTML (يدوياً أو عبر https://mjml.io/try-it-live)
3. أضف الـ HTML في `resources/views/emails/templates/`
4. حدّث `EmailTemplateSeeder.php`
5. شغّل `php artisan db:seed --class=EmailTemplateSeeder`

### لتعديل قالب:

1. عدّل ملف HTML في `resources/views/emails/templates/`
2. شغّل الـ seeder مرة أخرى (أو عدّل من لوحة التحكم)

---

## ✅ الخلاصة

تم إنشاء نظام إيميلات متكامل يلبي جميع المتطلبات مع مراعاة:
- **الأمان:** لا بيانات تخرج لطرف ثالث
- **البساطة:** لا اعتماديات معقدة
- **الأداء:** HTML جاهز وإرسال فوري
- **المرونة:** لوحة تحكم كاملة

**جاهز للاختبار! 🚀**
