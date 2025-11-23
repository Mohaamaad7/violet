@php
    use Filament\Actions\Action;
    use Filament\Actions\ActionGroup;
    $locale = app()->getLocale();
    $group = ActionGroup::make([
        Action::make('lang_ar')
            ->label('العربية')
            ->color($locale === 'ar' ? 'primary' : 'gray')
            ->icon('heroicon-m-language')
            ->action(function () { $this->switch('ar'); })
            ->extraAttributes(['class' => $locale === 'ar' ? 'locale-active' : '']),
        Action::make('lang_en')
            ->label('English')
            ->color($locale === 'en' ? 'primary' : 'gray')
            ->icon('heroicon-m-language')
            ->action(function () { $this->switch('en'); })
            ->extraAttributes(['class' => $locale === 'en' ? 'locale-active' : '']),
    ])->buttonGroup();
@endphp
<div x-data
     x-on:locale-updated.window="
        document.documentElement.setAttribute('dir', ($event.detail.locale === 'ar') ? 'rtl' : 'ltr');
        if ($event.detail.reload) { setTimeout(() => window.location.reload(), 120); }
     "
     class="flex items-center">
    {!! $group->toHtml() !!}
</div>
