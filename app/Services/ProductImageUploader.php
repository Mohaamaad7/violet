<?php

namespace App\Services;

use App\Jobs\ProcessProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageUploader
{
    /**
     * Upload a product image and dispatch processing job
     *
     * @param UploadedFile $file
     * @param string|null $customPath
     * @return string The stored file path
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?string $customPath = null): string
    {
        try {
            $this->validateImage($file);
            
            // Generate unique filename
            $filename = $this->generateFilename($file, $customPath);
            
            // Store the original image
            $path = Storage::disk('public')->putFileAs(
                'products',
                $file,
                $filename
            );
            
            if (!$path) {
                throw new \Exception('Failed to store image file');
            }
            
            // Dispatch job to create thumbnails and optimize
            ProcessProductImage::dispatch($path);
            
            return $path;
            
        } catch (\Exception $e) {
            throw new \Exception("Image upload failed: {$e->getMessage()}");
        }
    }

    /**
     * Upload multiple images
     *
     * @param array $files Array of UploadedFile instances
     * @return array Array of stored file paths
     */
    public function uploadMultiple(array $files): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file);
            }
        }
        
        return $paths;
    }

    /**
     * Delete product image and all its variants
     *
     * @param string $imagePath
     * @return bool
     */
    public function delete(string $imagePath): bool
    {
        try {
            $pathInfo = pathinfo($imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            
            // Delete original
            Storage::disk('public')->delete($imagePath);
            
            // Delete thumbnail
            $thumbnailPath = "{$directory}/thumbnails/{$filename}.{$extension}";
            Storage::disk('public')->delete($thumbnailPath);
            
            // Delete medium
            $mediumPath = "{$directory}/medium/{$filename}.{$extension}";
            Storage::disk('public')->delete($mediumPath);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Failed to delete product image: {$imagePath}", [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get URL for specific image size
     *
     * @param string $imagePath
     * @param string $size original|medium|thumbnail
     * @return string|null
     */
    public function getImageUrl(string $imagePath, string $size = 'original'): ?string
    {
        if ($size === 'original') {
            return Storage::disk('public')->url($imagePath);
        }
        
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        $path = match($size) {
            'thumbnail' => "{$directory}/thumbnails/{$filename}.{$extension}",
            'medium' => "{$directory}/medium/{$filename}.{$extension}",
            default => $imagePath,
        };
        
        return Storage::disk('public')->exists($path) 
            ? Storage::disk('public')->url($path) 
            : null;
    }

    /**
     * Validate uploaded image file
     *
     * @param UploadedFile $file
     * @return void
     * @throws \Exception
     */
    protected function validateImage(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds 5MB limit');
        }
        
        // Check mime type
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid image type. Allowed: JPEG, PNG, WebP, GIF');
        }
    }

    /**
     * Generate unique filename for the uploaded file
     *
     * @param UploadedFile $file
     * @param string|null $customPath
     * @return string
     */
    protected function generateFilename(UploadedFile $file, ?string $customPath = null): string
    {
        if ($customPath) {
            return $customPath;
        }
        
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($basename);
        
        return $slug . '_' . time() . '.' . $extension;
    }
}
