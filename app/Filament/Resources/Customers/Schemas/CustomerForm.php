<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(trans_db('admin.customers.sections.basic_info'))
                ->schema([
                    FileUpload::make('profile_photo_path')
                        ->label(trans_db('admin.customers.fields.profile_photo'))
                        ->image()
                        ->avatar()
                        ->imageEditor()
                        ->directory('customers/profiles')
                        ->maxSize(2048)
                        ->columnSpanFull(),

                    TextInput::make('name')
                        ->label(trans_db('admin.customers.fields.name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label(trans_db('admin.customers.fields.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label(trans_db('admin.customers.fields.phone'))
                        ->tel()
                        ->maxLength(20),

                    Select::make('status')
                        ->label(trans_db('admin.customers.fields.status'))
                        ->options([
                            'active' => trans_db('admin.customers.status.active'),
                            'blocked' => trans_db('admin.customers.status.blocked'),
                            'inactive' => trans_db('admin.customers.status.inactive'),
                        ])
                        ->required()
                        ->default('active')
                        ->native(false),

                    Select::make('locale')
                        ->label(trans_db('admin.customers.fields.locale'))
                        ->options([
                            'ar' => '🇪🇬 العربية',
                            'en' => '🇬🇧 English',
                        ])
                        ->required()
                        ->default('ar')
                        ->native(false),
                ])
                ->columns(2),

            Section::make(trans_db('admin.customers.sections.security_note'))
                ->schema([
                    Placeholder::make('password_note')
                        ->label('')
                        ->content(trans_db('admin.customers.messages.password_security_note'))
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }
}
