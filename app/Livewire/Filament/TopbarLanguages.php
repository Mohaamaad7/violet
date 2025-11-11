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

        // Persist locale and apply immediately for this request
        session(['locale' => $locale]);
        app()->setLocale($locale);

        // Notify the frontend (for things like toggling document direction instantly)
        $this->dispatch('locale-updated', locale: $locale);

        // Refresh the current page to apply locale changes
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.filament.topbar-languages');
    }
}
