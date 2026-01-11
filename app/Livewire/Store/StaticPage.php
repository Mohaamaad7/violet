<?php

namespace App\Livewire\Store;

use App\Models\Page;
use Livewire\Component;

class StaticPage extends Component
{
    public Page $page;

    public function mount(string $slug)
    {
        $this->page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.store.static-page', [
            'page' => $this->page,
        ])->layout('layouts.store', [
                    'title' => $this->page->meta_title ?: $this->page->title,
                    'description' => $this->page->meta_description,
                ]);
    }
}
