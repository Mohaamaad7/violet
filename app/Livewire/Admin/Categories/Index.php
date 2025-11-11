<?php

namespace App\Livewire\Admin\Categories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\CategoryService;
use App\Models\Category;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showActive = 'all'; // all, active, inactive
    public $showModal = false;
    public $categoryId = null;
    public $name = '';
    public $parentId = null;
    public $description = '';
    public $order = 0;
    public $isActive = true;
    
    protected $categoryService;
    
    public function boot(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    
    protected $rules = [
        'name' => 'required|max:255',
        'parentId' => 'nullable|exists:categories,id',
        'description' => 'nullable',
        'order' => 'integer|min:0',
        'isActive' => 'boolean',
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingShowActive()
    {
        $this->resetPage();
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }
    
    public function openEditModal($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        
        if ($category) {
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->parentId = $category->parent_id;
            $this->description = $category->description;
            $this->order = $category->order;
            $this->isActive = $category->is_active;
            $this->showModal = true;
        }
    }
    
    public function save()
    {
        $this->validate();
        
        $data = [
            'name' => $this->name,
            'parent_id' => $this->parentId,
            'description' => $this->description,
            'order' => $this->order,
            'is_active' => $this->isActive,
        ];
        
        try {
            if ($this->categoryId) {
                $this->categoryService->updateCategory($this->categoryId, $data);
                session()->flash('message', 'تم تحديث الفئة بنجاح');
            } else {
                $this->categoryService->createCategory($data);
                session()->flash('message', 'تم إنشاء الفئة بنجاح');
            }
            
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    
    public function toggleActive($id)
    {
        try {
            $this->categoryService->toggleActive($id);
            session()->flash('message', 'تم تحديث حالة الفئة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    
    public function delete($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            session()->flash('message', 'تم حذف الفئة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    private function resetForm()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->parentId = null;
        $this->description = '';
        $this->order = 0;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        $filters = [];
        
        if ($this->search) {
            $filters['search'] = $this->search;
        }
        
        if ($this->showActive !== 'all') {
            $filters['is_active'] = $this->showActive === 'active' ? 1 : 0;
        }
        
        $categories = Category::query()
            ->with(['parent', 'children'])
            ->withCount(['products', 'children'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->showActive !== 'all', function ($query) {
                $query->where('is_active', $this->showActive === 'active' ? 1 : 0);
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(15);
        
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        
        return view('livewire.admin.categories.index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ])->layout('layouts.admin', ['title' => 'إدارة الفئات']);
    }
}
