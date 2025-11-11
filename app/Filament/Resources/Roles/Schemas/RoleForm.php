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
            ->components([
                Section::make('معلومات الدور')
                    ->description('البيانات الأساسية للدور')
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
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('الصلاحيات')
                            ->relationship('permissions', 'name')
                            ->options(Permission::all()->pluck('name', 'id'))
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->gridDirection('row'),
                    ]),
            ]);
    }
}
