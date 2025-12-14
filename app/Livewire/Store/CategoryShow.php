<?php

namespace App\Livewire\Store;

use App\Models\Category;
use Livewire\Component;

class CategoryShow extends Component
{
    public Category $category;

    public function mount(Category $category)
    {
        // Load relationships needed for display
        $category->load('children');

        $this->category = $category;
    }

    public function render()
    {
        $children = $this->category->children()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // If no children, redirect to products page filtered by this category
        if ($children->isEmpty()) {
            return redirect()->route('products.index', ['category' => $this->category->slug]);
        }

        return view('livewire.store.category-show', [
            'children' => $children,
        ])->layout('layouts.store');
    }
}
