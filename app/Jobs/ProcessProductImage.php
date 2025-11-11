<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProcessProductImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $imagePath,
        public bool $deleteOriginal = false
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $fullPath = Storage::disk('public')->path($this->imagePath);
            
            if (!file_exists($fullPath)) {
                Log::warning("Image file not found: {$fullPath}");
                return;
            }

            // Get filename and directory
            $pathInfo = pathinfo($this->imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];

            // Create thumbnail (150x150)
            $thumbnailPath = "{$directory}/thumbnails/{$filename}.{$extension}";
            $image = Image::read($fullPath);
            $image->cover(150, 150);
            Storage::disk('public')->put(
                $thumbnailPath,
                $image->encode()
            );

            // Create medium size (500x500)
            $mediumPath = "{$directory}/medium/{$filename}.{$extension}";
            $image = Image::read($fullPath);
            $image->cover(500, 500);
            Storage::disk('public')->put(
                $mediumPath,
                $image->encode()
            );

            // Optimize original (max 1200x1200)
            $image = Image::read($fullPath);
            $image->scaleDown(width: 1200, height: 1200);
            Storage::disk('public')->put(
                $this->imagePath,
                $image->encode()
            );

            Log::info("Image processed successfully: {$this->imagePath}");
            
        } catch (\Exception $e) {
            Log::error("Failed to process image: {$this->imagePath}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['image-processing', 'product-image', $this->imagePath];
    }
}
