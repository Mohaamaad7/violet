<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المستخدم')
                    ->schema([
                        FileUpload::make('profile_photo_path')
                            ->label('الصورة الشخصية')
                            ->image()
                            ->avatar()
                            ->directory('profile-photos')
                            ->imageEditor()
                            ->maxSize(1024)
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->numeric()
                            ->nullable()
                            ->maxLength(20),
                    ])
                    ->columns(2),
                
                Section::make('الدور والصلاحيات')
                    ->schema([
                        Select::make('role')
                            ->label('الدور الوظيفي')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('اختر الدور الذي سيحدد صلاحيات المستخدم')
                            ->afterStateHydrated(function (Select $component, $state, $record) {
                                // Load the first role from the user's roles relationship
                                if ($record && $record->roles()->exists()) {
                                    $component->state($record->roles()->first()->id);
                                }
                            })
                            ->dehydrated(false), // Will be handled manually in Create/Edit pages
                    ]),
                
                Section::make('كلمة المرور')
                    ->schema([
                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): ?string => 
                                $operation === 'edit' 
                                    ? 'اتركه فارغاً إذا كنت لا تريد تغيير كلمة المرور' 
                                    : null
                            ),
                    ]),
            ]);
    }
}
