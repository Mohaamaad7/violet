<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Services\SystemResetService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

/**
 * System Reset Page
 * 
 * Allows Super Admin to selectively reset data before going live.
 * Protected by multiple security layers.
 */
class SystemReset extends Page implements HasForms
{
    use InteractsWithForms;
    use ChecksPageAccess;

    protected static ?int $navigationSort = 99;

    public array $selectedCategories = [];
    public bool $createBackup = true;
    public string $confirmationPhrase = '';
    public string $password = '';
    public ?string $preset = null;

    protected string $view = 'filament.pages.system-reset';

    public static function getNavigationIcon(): string|null
    {
        return 'heroicon-o-arrow-path';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.system_reset.title');
    }

    public function getTitle(): string
    {
        return __('admin.system_reset.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.settings');
    }

    /**
     * Only Super Admin can access this page
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function mount(): void
    {
        $this->form->fill([
            'createBackup' => true,
            'selectedCategories' => [],
            'confirmationPhrase' => '',
            'password' => '',
            'preset' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $service = app(SystemResetService::class);
        $stats = $service->getCategoryStats();
        $presets = $service->getPresets();

        return $schema
            ->schema([
                Section::make(__('admin.system_reset.warning_title'))
                    ->description(__('admin.system_reset.warning_description'))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger')
                    ->schema([
                        Placeholder::make('warning')
                            ->content(new HtmlString('
                                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <p class="text-red-700 dark:text-red-300 font-bold text-lg">⚠️ ' . __('admin.system_reset.irreversible_warning') . '</p>
                                </div>
                            ')),
                    ]),

                Section::make(__('admin.system_reset.presets_title'))
                    ->description(__('admin.system_reset.presets_description'))
                    ->schema([
                        Select::make('preset')
                            ->label(__('admin.system_reset.select_preset'))
                            ->options(collect($presets)->mapWithKeys(fn($preset, $key) => [$key => $preset['label']]))
                            ->placeholder(__('admin.system_reset.custom_selection'))
                            ->live()
                            ->afterStateUpdated(function ($state) use ($presets) {
                                if ($state && isset($presets[$state])) {
                                    $this->selectedCategories = $presets[$state]['categories'];
                                }
                            }),
                    ]),

                Section::make(__('admin.system_reset.select_data_title'))
                    ->description(__('admin.system_reset.select_data_description'))
                    ->schema([
                        CheckboxList::make('selectedCategories')
                            ->label('')
                            ->options(
                                collect($stats)->mapWithKeys(function ($stat, $key) {
                                    $count = number_format($stat['count']);
                                    $related = $stat['related_count'] ?? 0;
                                    $warning = $stat['special'] ? ' ⚠️' : '';

                                    // Show main count, and related tables count if exists
                                    $label = "{$stat['label']} ({$count}";
                                    if ($related > 0) {
                                        $label .= " + " . number_format($related) . " مرتبط";
                                    }
                                    $label .= " سجل){$warning}";

                                    return [$key => $label];
                                })->toArray()
                            )
                            ->columns(2)
                            ->gridDirection('row')
                            ->live(),
                    ]),

                Section::make(__('admin.system_reset.preview_title'))
                    ->description(__('admin.system_reset.preview_description'))
                    ->schema([
                        Placeholder::make('preview')
                            ->content(function () use ($service) {
                                if (empty($this->selectedCategories)) {
                                    return new HtmlString('<p class="text-gray-500">' . __('admin.system_reset.no_selection') . '</p>');
                                }

                                $tables = $service->getAffectedTables($this->selectedCategories);
                                $html = '<div class="grid grid-cols-3 gap-2">';
                                foreach ($tables as $table) {
                                    $html .= '<span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded text-sm">' . $table . '</span>';
                                }
                                $html .= '</div>';

                                return new HtmlString($html);
                            }),
                    ])
                    ->visible(fn() => !empty($this->selectedCategories)),

                Section::make(__('admin.system_reset.safety_title'))
                    ->description(__('admin.system_reset.safety_description'))
                    ->schema([
                        Toggle::make('createBackup')
                            ->label(__('admin.system_reset.create_backup'))
                            ->helperText(__('admin.system_reset.backup_recommended'))
                            ->default(true),

                        TextInput::make('confirmationPhrase')
                            ->label(__('admin.system_reset.type_confirmation'))
                            ->helperText(__('admin.system_reset.confirmation_phrase'))
                            ->required()
                            ->placeholder(__('admin.system_reset.confirmation_phrase')),

                        TextInput::make('password')
                            ->label(__('admin.system_reset.enter_password'))
                            ->password()
                            ->required(),
                    ]),
            ]);
    }

    /**
     * Execute the reset operation
     */
    public function executeReset(): void
    {
        // Validate form
        $this->validate([
            'selectedCategories' => 'required|array|min:1',
            'confirmationPhrase' => 'required',
            'password' => 'required',
        ]);

        // Verify confirmation phrase
        if ($this->confirmationPhrase !== __('admin.system_reset.confirmation_phrase')) {
            Notification::make()
                ->title(__('admin.system_reset.invalid_phrase'))
                ->danger()
                ->send();
            return;
        }

        // Verify password
        if (!Hash::check($this->password, auth()->user()->password)) {
            Notification::make()
                ->title(__('admin.system_reset.invalid_password'))
                ->danger()
                ->send();
            return;
        }

        // Execute reset
        $service = app(SystemResetService::class);

        Notification::make()
            ->title(__('admin.system_reset.in_progress'))
            ->info()
            ->send();

        $result = $service->reset(
            $this->selectedCategories,
            $this->createBackup
        );

        if ($result['success']) {
            $deletedCount = array_sum($result['deleted_records']);

            Notification::make()
                ->title(__('admin.system_reset.success'))
                ->body(__('admin.system_reset.deleted_records', ['count' => number_format($deletedCount)]))
                ->success()
                ->persistent()
                ->send();

            // Reset form
            $this->selectedCategories = [];
            $this->confirmationPhrase = '';
            $this->password = '';
            $this->preset = null;
        } else {
            Notification::make()
                ->title(__('admin.system_reset.failed'))
                ->body(implode("\n", $result['errors']))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
