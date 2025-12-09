<?php

namespace App\Livewire\Store\Account;

use App\Models\Customer;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Addresses extends Component
{
    use WithPagination;

    // Form Fields
    public ?int $editingAddressId = null;
    public string $full_name = '';
    public string $phone = '';
    public string $governorate = '';
    public string $city = '';
    public string $area = '';
    public string $street_address = '';
    public string $building_number = '';
    public string $floor = '';
    public string $apartment = '';
    public string $landmark = '';
    public bool $is_default = false;

    // UI State
    public bool $showForm = false;
    public bool $confirmingDelete = false;
    public ?int $deletingAddressId = null;

    // Egyptian Governorates
    public array $governorates = [
        'Cairo' => 'Cairo',
        'Giza' => 'Giza',
        'Alexandria' => 'Alexandria',
        'Qalyubia' => 'Qalyubia',
        'Sharqia' => 'Sharqia',
        'Dakahlia' => 'Dakahlia',
        'Beheira' => 'Beheira',
        'Kafr El Sheikh' => 'Kafr El Sheikh',
        'Gharbia' => 'Gharbia',
        'Monufia' => 'Monufia',
        'Damietta' => 'Damietta',
        'Port Said' => 'Port Said',
        'Ismailia' => 'Ismailia',
        'Suez' => 'Suez',
        'North Sinai' => 'North Sinai',
        'South Sinai' => 'South Sinai',
        'Beni Suef' => 'Beni Suef',
        'Fayoum' => 'Fayoum',
        'Minya' => 'Minya',
        'Asyut' => 'Asyut',
        'Sohag' => 'Sohag',
        'Qena' => 'Qena',
        'Luxor' => 'Luxor',
        'Aswan' => 'Aswan',
        'Red Sea' => 'Red Sea',
        'New Valley' => 'New Valley',
        'Matrouh' => 'Matrouh',
    ];

    /**
     * Get the currently authenticated customer ID
     */
    private function getCustomerId(): ?int
    {
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->id();
        }
        return null;
    }

    /**
     * Get the currently authenticated customer
     */
    private function getCustomer(): ?Customer
    {
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }
        return null;
    }

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'governorate' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'street_address' => ['required', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:20'],
            'apartment' => ['nullable', 'string', 'max:20'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ];
    }

    public function openForm(?int $addressId = null): void
    {
        $this->resetValidation();
        $customerId = $this->getCustomerId();

        if ($addressId) {
            $address = ShippingAddress::where('id', $addressId)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            $this->editingAddressId = $address->id;
            $this->full_name = $address->full_name;
            $this->phone = $address->phone;
            $this->governorate = $address->governorate;
            $this->city = $address->city;
            $this->area = $address->area ?? '';
            $this->street_address = $address->street_address;
            $this->building_number = $address->building_number ?? '';
            $this->floor = $address->floor ?? '';
            $this->apartment = $address->apartment ?? '';
            $this->landmark = $address->landmark ?? '';
            $this->is_default = $address->is_default;
        } else {
            $this->editingAddressId = null;
            $this->resetForm();

            // Pre-fill name and phone from customer
            $customer = $this->getCustomer();
            $this->full_name = $customer?->name ?? '';
            $this->phone = $customer?->phone ?? '';
        }

        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->editingAddressId = null;
        $this->full_name = '';
        $this->phone = '';
        $this->governorate = '';
        $this->city = '';
        $this->area = '';
        $this->street_address = '';
        $this->building_number = '';
        $this->floor = '';
        $this->apartment = '';
        $this->landmark = '';
        $this->is_default = false;
    }

    public function save(): void
    {
        $this->validate();
        $customerId = $this->getCustomerId();

        $data = [
            'customer_id' => $customerId,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'area' => $this->area ?: null,
            'street_address' => $this->street_address,
            'building_number' => $this->building_number ?: null,
            'floor' => $this->floor ?: null,
            'apartment' => $this->apartment ?: null,
            'landmark' => $this->landmark ?: null,
            'is_default' => $this->is_default,
        ];

        // If setting as default, unset other defaults
        if ($this->is_default) {
            ShippingAddress::where('customer_id', $customerId)
                ->where('id', '!=', $this->editingAddressId)
                ->update(['is_default' => false]);
        }

        if ($this->editingAddressId) {
            // Authorization check
            $address = ShippingAddress::where('id', $this->editingAddressId)
                ->where('customer_id', $customerId)
                ->firstOrFail();

            $address->update($data);
            $message = __('messages.account.address_updated');
        } else {
            // If this is first address, make it default
            $existingCount = ShippingAddress::where('customer_id', $customerId)->count();
            if ($existingCount === 0) {
                $data['is_default'] = true;
            }

            ShippingAddress::create($data);
            $message = __('messages.account.address_added');
        }

        $this->closeForm();
        $this->dispatch('show-toast', message: $message, type: 'success');
    }

    public function setDefault(int $addressId): void
    {
        $customerId = $this->getCustomerId();

        // Authorization check
        $address = ShippingAddress::where('id', $addressId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        // Unset all defaults
        ShippingAddress::where('customer_id', $customerId)->update(['is_default' => false]);

        // Set new default
        $address->update(['is_default' => true]);

        $this->dispatch('show-toast', message: __('messages.account.default_address_set'), type: 'success');
    }

    public function confirmDelete(int $addressId): void
    {
        $this->deletingAddressId = $addressId;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingAddressId = null;
        $this->confirmingDelete = false;
    }

    public function delete(): void
    {
        if (!$this->deletingAddressId) {
            return;
        }

        $customerId = $this->getCustomerId();

        // Authorization check
        $address = ShippingAddress::where('id', $this->deletingAddressId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        // Check if address is used in orders
        if ($address->orders()->exists()) {
            $this->dispatch('show-toast', message: __('messages.account.address_in_use'), type: 'error');
            $this->cancelDelete();
            return;
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = ShippingAddress::where('customer_id', $customerId)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $this->cancelDelete();
        $this->dispatch('show-toast', message: __('messages.account.address_deleted'), type: 'success');
    }

    public function render()
    {
        $customerId = $this->getCustomerId();

        $addresses = ShippingAddress::where('customer_id', $customerId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.store.account.addresses', [
            'addresses' => $addresses,
        ])->layout('layouts.store', ['title' => __('messages.account.addresses')]);
    }
}
