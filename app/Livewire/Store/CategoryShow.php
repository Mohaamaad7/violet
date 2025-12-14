<?php

namespace App\Livewire\Store;

use App\Models\Category;
use Livewire\Component;

class CategoryShow extends Component
{
    public Category $category;

    public function mount(Category $category)
    {
        $this->category = $category;

        // التحقق مما إذا كان القسم يحتوي على أقسام فرعية
        $hasChildren = $this->category->children()->where('is_active', true)->exists();

        // إذا لم يكن لديه أقسام فرعية، يتم تحويل المستخدم لصفحة المنتجات
        if (!$hasChildren) {
            $this->redirect(route('products.index', ['category' => $this->category->slug]));
        }
    }

    public function render()
    {
        // جلب الأقسام الفرعية لعرضها في حال لم يتم التحويل
        $children = $this->category->children()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('livewire.store.category-show', [
            'children' => $children,
        ])->layout('layouts.store');
    }
}
