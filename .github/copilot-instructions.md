# Violet Project - AI Coding Agent Instructions

## Project Overview

**Violet** is a Laravel application located at `c:\server\www\violet`. This is a Windows development environment using PowerShell as the default shell.

## Core Development Principles

### Architecture & Code Quality
- **Service Layer Pattern**: Controllers handle HTTP concerns only. All business logic goes in dedicated Service classes in `app/Services/`.
- **Dependency Injection**: Use Laravel's container for DI. Avoid direct facade calls in business logic.
- **Form Requests**: ALL validation and authorization for POST/PUT/PATCH requests MUST use dedicated Form Request classes (`app/Http/Requests/`).
- **Single Responsibility**: Each class/method has one reason to change.

### Eloquent & Database
- **Eager Loading Required**: Always use `->with()` to prevent N+1 queries when loading relationships.
- **Route Model Binding**: Use implicit/explicit binding to inject models directly into route handlers.
- **Eloquent First**: Prefer Eloquent ORM over Query Builder or raw SQL.

### Code Style (PSR-12)
- Classes: `PascalCase`
- Methods/Variables: `camelCase`
- Database tables/columns: `snake_case`
- Use descriptive names; comment only complex logic.

## Development Workflows

### Running Commands (Windows/PowerShell)
```powershell
# Artisan commands
php artisan migrate
php artisan make:controller UserController --resource
php artisan make:model Post -mfsc  # model + migration + factory + seeder + controller

# Composer
composer install
composer require package/name
composer dump-autoload

# NPM (if frontend assets exist)
npm install
npm run dev
npm run build
```

### File Generation Patterns
When creating new features:
1. **Model** with migration: `php artisan make:model ModelName -m`
2. **Form Request**: `php artisan make:request StoreModelRequest`
3. **Service Class**: Create manually in `app/Services/ModelService.php`
4. **Controller**: `php artisan make:controller ModelController --resource`
5. **Routes**: Add to `routes/web.php` or `routes/api.php`

### Testing
- Run tests: `php artisan test` or `vendor/bin/phpunit`
- Create tests: `php artisan make:test FeatureTest` or `php artisan make:test UnitTest --unit`

## Project-Specific Standards

### Controller Example (Thin)
```php
public function store(StorePostRequest $request, PostService $postService)
{
    $post = $postService->createPost($request->validated());
    return redirect()->route('posts.show', $post);
}
```

### Service Class Example
```php
namespace App\Services;

class PostService
{
    public function createPost(array $data): Post
    {
        // Business logic here
        return Post::create($data);
    }
}
```

### Blade Components
- Use `{{ $variable }}` for escaped output (default)
- Use `{!! $variable !!}` ONLY for sanitized HTML
- Break views into reusable components in `resources/views/components/`

## Critical Checks Before Committing

1. **No N+1 Queries**: Verify eager loading on all relationship access
2. **Form Requests Used**: All POST/PUT/PATCH routes have Form Request validation
3. **No Logic in Controllers**: Business logic is in Service classes
4. **PSR-12 Compliant**: Run `vendor/bin/phpcs` if available
5. **No Deprecated Methods**: Verify against current Laravel version docs

## When Unsure

- **Check Laravel Docs First**: Always consult official Laravel documentation (specify version compatibility)
- **Don't Guess**: State when verification is needed rather than providing potentially incorrect code
- **Community Resources**: Laracasts, Laravel News, and community packages are good secondary sources