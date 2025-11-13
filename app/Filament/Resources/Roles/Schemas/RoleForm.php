<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('معلومات الدور')
                    ->description('البيانات الأساسية للدور')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الدور')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('اسم فريد للدور (مثل: Sales, Manager)'),
                        
                        TextInput::make('guard_name')
                            ->default('web')
                            ->required()
                            ->hidden(),
                    ]),
                
                Section::make('الصلاحيات')
                    ->description('اختر الصلاحيات المتاحة لهذا الدور')
                    ->columns(1)
                    ->schema([
                        // المنتجات
                        CheckboxList::make('permissions')
                            ->label('المنتجات')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view products',
                                'create products',
                                'edit products',
                                'delete products',
                            ])->pluck('name', 'id'))
                            ->columns(4)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // الفئات
                        CheckboxList::make('permissions_categories')
                            ->label('الفئات')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view categories',
                                'create categories',
                                'edit categories',
                                'delete categories',
                            ])->pluck('name', 'id'))
                            ->columns(4)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // الطلبات
                        CheckboxList::make('permissions_orders')
                            ->label('الطلبات')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view orders',
                                'create orders',
                                'edit orders',
                                'delete orders',
                                'manage order status',
                            ])->pluck('name', 'id'))
                            ->columns(4)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // المستخدمين
                        CheckboxList::make('permissions_users')
                            ->label('المستخدمين')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view users',
                                'create users',
                                'edit users',
                                'delete users',
                            ])->pluck('name', 'id'))
                            ->columns(4)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // الأدوار والصلاحيات
                        CheckboxList::make('permissions_roles')
                            ->label('الأدوار والصلاحيات')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view roles',
                                'create roles',
                                'edit roles',
                                'delete roles',
                                'view permissions',
                                'edit permissions',
                            ])->pluck('name', 'id'))
                            ->columns(3)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // المؤثرين والعمولات
                        CheckboxList::make('permissions_influencers')
                            ->label('المؤثرين والعمولات')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view influencers',
                                'manage influencer applications',
                                'edit influencers',
                                'delete influencers',
                                'view commissions',
                                'manage payouts',
                            ])->pluck('name', 'id'))
                            ->columns(3)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // أكواد الخصم
                        CheckboxList::make('permissions_discounts')
                            ->label('أكواد الخصم')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view discount codes',
                                'create discount codes',
                                'edit discount codes',
                                'delete discount codes',
                            ])->pluck('name', 'id'))
                            ->columns(4)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // المحتوى
                        CheckboxList::make('permissions_content')
                            ->label('المحتوى')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'manage content',
                                'manage blog',
                                'manage pages',
                            ])->pluck('name', 'id'))
                            ->columns(3)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                        
                        // التقارير
                        CheckboxList::make('permissions_reports')
                            ->label('التقارير')
                            ->relationship('permissions', 'name')
                            ->options(Permission::whereIn('name', [
                                'view reports',
                            ])->pluck('name', 'id'))
                            ->columns(1)
                            ->bulkToggleable()
                            ->gridDirection('row'),
                    ]),
            ]);
    }
}
