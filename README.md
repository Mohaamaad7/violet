# 🌸 Violet E-Commerce Platform<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



![Laravel](https://img.shields.io/badge/Laravel-12.37-FF2D20?style=flat&logo=laravel)<p align="center">

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

![License](https://img.shields.io/badge/License-MIT-green.svg)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

منصة تجارة إلكترونية متكاملة مبنية بـ Laravel 12 تتضمن واجهة عملاء حديثة، بوابة خاصة للمؤثرين مع نظام عمولات، ولوحة إدارة شاملة.<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

</p>

## 🚀 المميزات الرئيسية

## About Laravel

- 🛍️ **واجهة عملاء عصرية** - تجربة تسوق سلسة وسريعة

- 🌟 **بوابة مؤثرين** - نظام عمولات وأكواد خصم متقدمLaravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- 🎛️ **لوحة إدارة قوية** - إدارة شاملة للمنتجات والطلبات والمؤثرين

- 💳 **تكامل مع بوابات الدفع المصرية** - Paymob, Accept, InstaPay- [Simple, fast routing engine](https://laravel.com/docs/routing).

- 📧 **نظام إشعارات متطور** - Email + WhatsApp- [Powerful dependency injection container](https://laravel.com/docs/container).

- 🔐 **نظام صلاحيات محكم** - Spatie Laravel Permission- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).

## 🛠️ التقنيات المستخدمة- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

- [Robust background job processing](https://laravel.com/docs/queues).

### Backend- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

- **Laravel 12.37** - PHP Framework

- **Laravel Sanctum 4.0** - API AuthenticationLaravel is accessible, powerful, and provides tools required for large, robust applications.

- **Livewire 3.6** - Dynamic Interfaces

- **Spatie Permission 6.0** - Roles & Permissions## Learning Laravel

- **Spatie Activity Log 4.10** - Audit Trail

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

### Frontend

- **Tailwind CSS 4.0** - Utility-first CSSIf you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

- **Alpine.js 3.13** - Lightweight JavaScript

- **Vite** - Frontend Build Tool## Laravel Sponsors



### Database & CacheWe would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

- **MySQL 8.0** - Primary Database

- **Database Cache & Queue** - (Redis يمكن تفعيله لاحقاً)### Premium Partners



## 📋 المتطلبات- **[Vehikl](https://vehikl.com)**

- **[Tighten Co.](https://tighten.co)**

- PHP >= 8.2- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**

- Composer >= 2.0- **[64 Robots](https://64robots.com)**

- Node.js >= 18.0- **[Curotec](https://www.curotec.com/services/technologies/laravel)**

- MySQL >= 8.0- **[DevSquad](https://devsquad.com/hire-laravel-developers)**

- NPM >= 9.0- **[Redberry](https://redberry.international/laravel-development)**

- **[Active Logic](https://activelogic.com)**

## 🔧 التثبيت

## Contributing

### 1. استنساخ المشروع

```bashThank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

git clone <repository-url> violet

cd violet## Code of Conduct

```

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

### 2. تثبيت Dependencies

```bash## Security Vulnerabilities

# Backend dependencies

composer installIf you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.



# Frontend dependencies## License

npm install

```The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


### 3. إعداد البيئة
```bash
# نسخ ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate
```

### 4. إعداد قاعدة البيانات
```bash
# أنشئ قاعدة بيانات MySQL اسمها: violet

# قم بتحديث ملف .env
DB_CONNECTION=mysql
DB_DATABASE=violet
DB_USERNAME=root
DB_PASSWORD=

# تشغيل Migrations
php artisan migrate
```

### 5. تشغيل المشروع
```bash
# بناء Frontend Assets
npm run build

# تشغيل السيرفر
php artisan serve

# للتطوير (مع Hot Reload)
npm run dev
```

المشروع سيعمل على: http://localhost:8000

## 📁 هيكل المشروع

```
violet/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controllers
│   │   └── Requests/        # Form Requests
│   ├── Models/              # Eloquent Models
│   ├── Services/            # Business Logic
│   └── Providers/           # Service Providers
├── config/                  # Configuration Files
├── database/
│   ├── migrations/          # Database Migrations
│   ├── seeders/            # Database Seeders
│   └── factories/          # Model Factories
├── resources/
│   ├── views/              # Blade Templates
│   ├── css/                # Styles
│   └── js/                 # JavaScript
├── routes/
│   ├── web.php             # Web Routes
│   └── api.php             # API Routes
└── tests/                  # Tests
```

## 🧪 الاختبار

```bash
# تشغيل جميع الاختبارات
php artisan test

# اختبارات محددة
php artisan test --filter=ProductTest
```

## 📚 التوثيق

للمزيد من التفاصيل، راجع:
- [خطة العمل التفصيلية](violet%20work%20flow.md)
- [تقرير التقدم](PROGRESS.md)

## 🔐 الأمان

- CSRF Protection مفعل على جميع النماذج
- XSS Protection
- SQL Injection Prevention
- Secure Password Hashing (bcrypt)
- Activity Logging لجميع العمليات الحساسة

## 📄 الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE)

## 👨‍💻 المطور

تم تطوير المشروع بواسطة فريق Violet

---

**ملاحظة:** هذا المشروع قيد التطوير النشط. تابع ملف [PROGRESS.md](PROGRESS.md) لمعرفة آخر التحديثات.
