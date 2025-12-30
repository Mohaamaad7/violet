<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Models\Customer;
use Filament\Actions\Action;

class ViewWishlistAction
{
    public static function make(): Action
    {
        return Action::make('view_wishlist')
            ->label(trans_db('admin.customers.actions.view_wishlist'))
            ->icon('heroicon-o-heart')
            ->color('danger')
            ->modalHeading(fn(Customer $record) => trans_db('admin.customers.wishlist.heading', ['name' => $record->name]))
            ->modalContent(fn(Customer $record) => view('filament.resources.customers.wishlist-modal', [
                'customer' => $record,
                'wishlists' => $record->wishlists()->with('product.media')->get(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(trans_db('messages.close'))
            ->modalWidth('3xl')
            ->visible(fn(Customer $record) => $record->wishlists()->count() > 0);
    }
}
