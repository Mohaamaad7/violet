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

        // Persist locale in session
        session(['locale' => $locale]);
        app()->setLocale($locale);

        // Notify the frontend to update direction
        $this->dispatch('locale-updated', locale: $locale);

        // Refresh the page without redirect (Livewire-safe)
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.filament.topbar-languages');
    }
}
