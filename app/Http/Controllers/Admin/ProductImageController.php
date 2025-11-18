<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function upload(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:5120',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            
            $order = ProductImage::where('product_id', $product->id)->max('order') ?? -1;
            
            $productImage = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => ProductImage::where('product_id', $product->id)->count() === 0,
                'order' => $order + 1,
            ]);

            $uploadedImages[] = $productImage;
        }

        return response()->json([
            'success' => true,
            'images' => $product->fresh()->images()->orderBy('order')->get(),
        ]);
    }

    public function setPrimary(ProductImage $image)
    {
        // Unset all primaries for this product
        ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);

        // Set new primary
        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'images' => $image->product->images()->orderBy('order')->get(),
        ]);
    }

    public function destroy(ProductImage $image)
    {
        $product = $image->product;
        $wasPrimary = $image->is_primary;

        // Delete file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        // If deleted image was primary, set first image as primary
        if ($wasPrimary) {
            $firstImage = ProductImage::where('product_id', $product->id)
                ->orderBy('order')
                ->first();
                
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'images' => $product->fresh()->images()->orderBy('order')->get(),
        ]);
    }

    public function updateOrder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:product_images,id',
        ]);

        foreach ($request->order as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update(['order' => $index]);
        }

        return response()->json([
            'success' => true,
            'images' => $product->fresh()->images()->orderBy('order')->get(),
        ]);
    }
}
