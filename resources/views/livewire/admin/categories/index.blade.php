<div>
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">إدارة الفئات</h2>
        <button wire:click="openCreateModal" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition">
            <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            إضافة فئة جديدة
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" wire:model.live="search" placeholder="ابحث عن فئة..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select wire:model.live="showActive" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="all">الكل</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الفئة الأساسية</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الفئات الفرعية</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">المنتجات</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الترتيب</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الحالة</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($category->icon)
                                    <span class="text-2xl ml-3">{{ $category->icon }}</span>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $category->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $category->parent ? $category->parent->name : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $category->children_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $category->products_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ $category->order }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="toggleActive({{ $category->id }})" 
                                class="px-3 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="openEditModal({{ $category->id }})" class="text-blue-600 hover:text-blue-800 mx-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button wire:click="delete({{ $category->id }})" 
                                wire:confirm="هل أنت متأكد من حذف هذه الفئة؟"
                                class="text-red-600 hover:text-red-800 mx-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p>لا توجد فئات حالياً</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-xl bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $categoryId ? 'تعديل الفئة' : 'إضافة فئة جديدة' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم الفئة *</label>
                            <input type="text" wire:model="name" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                required>
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الفئة الأساسية</label>
                            <select wire:model="parentId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">لا يوجد (فئة رئيسية)</option>
                                @foreach($parentCategories as $parent)
                                    @if(!$categoryId || $parent->id != $categoryId)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('parentId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">الترتيب</label>
                                <input type="number" wire:model="order" min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('order') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="isActive" class="form-checkbox h-5 w-5 text-purple-600 rounded">
                                    <span class="mr-2 text-sm text-gray-700">نشط</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="closeModal" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            إلغاء
                        </button>
                        <button type="submit" 
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition">
                            {{ $categoryId ? 'تحديث' : 'حفظ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
