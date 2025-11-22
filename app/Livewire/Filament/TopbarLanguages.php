<?php

namespace App\Livewire\Filament;

use Livewire\Component;

class TopbarLanguages extends Component
{
    public function switch($locale)
    {
        if (!in_array($locale, ['ar', 'en'])) {
            return;
        }

        // Persist locale in session & cookie so middleware picks it up on next request
        session(['locale' => $locale]);
        app()->setLocale($locale);
        // 1 year cookie to keep admin preference stable
        cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));

        // Use JavaScript to reload the entire page
        $this->dispatch('locale-updated', locale: $locale, reload: true);
    }

    public function render()
    {
        return view('livewire.filament.topbar-languages');
    }
}
