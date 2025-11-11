<?php

namespace Tests\Feature;

use App\Jobs\ProcessProductImage;
use App\Services\ProductImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected ProductImageUploader $uploader;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        $this->uploader = new ProductImageUploader();
    }

    /** @test */
    public function it_can_upload_a_valid_image()
    {
        Queue::fake();
        
        $file = UploadedFile::fake()->image('product.jpg', 800, 600);
        
        $path = $this->uploader->upload($file);
        
        $this->assertNotEmpty($path);
        $this->assertStringContainsString('products/', $path);
        Storage::disk('public')->assertExists($path);
        
        Queue::assertPushed(ProcessProductImage::class, function ($job) use ($path) {
            return $job->imagePath === $path;
        });
    }

    /** @test */
    public function it_rejects_files_larger_than_5mb()
    {
        $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File size exceeds 5MB limit');
        
        $this->uploader->upload($file);
    }

    /** @test */
    public function it_rejects_invalid_file_types()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid image type');
        
        $this->uploader->upload($file);
    }

    /** @test */
    public function it_can_upload_multiple_images()
    {
        Queue::fake();
        
        $files = [
            UploadedFile::fake()->image('product1.jpg'),
            UploadedFile::fake()->image('product2.png'),
            UploadedFile::fake()->image('product3.webp'),
        ];
        
        $paths = $this->uploader->uploadMultiple($files);
        
        $this->assertCount(3, $paths);
        
        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
        }
        
        Queue::assertPushed(ProcessProductImage::class, 3);
    }

    /** @test */
    public function it_generates_unique_filenames()
    {
        Queue::fake();
        
        $file1 = UploadedFile::fake()->image('test.jpg');
        $file2 = UploadedFile::fake()->image('test.jpg');
        
        $path1 = $this->uploader->upload($file1);
        sleep(1); // Ensure different timestamp
        $path2 = $this->uploader->upload($file2);
        
        $this->assertNotEquals($path1, $path2);
    }

    /** @test */
    public function it_can_delete_image_and_its_variants()
    {
        $file = UploadedFile::fake()->image('product.jpg');
        $path = Storage::disk('public')->putFileAs('products', $file, 'test-image.jpg');
        
        // Create fake thumbnails
        $pathInfo = pathinfo($path);
        $thumbnailPath = "{$pathInfo['dirname']}/thumbnails/test-image.jpg";
        $mediumPath = "{$pathInfo['dirname']}/medium/test-image.jpg";
        
        Storage::disk('public')->put($thumbnailPath, 'fake-thumbnail-content');
        Storage::disk('public')->put($mediumPath, 'fake-medium-content');
        
        // Verify files exist
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($thumbnailPath);
        Storage::disk('public')->assertExists($mediumPath);
        
        // Delete
        $result = $this->uploader->delete($path);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($thumbnailPath);
        Storage::disk('public')->assertMissing($mediumPath);
    }

    /** @test */
    public function it_returns_correct_image_urls()
    {
        $file = UploadedFile::fake()->image('product.jpg');
        $path = Storage::disk('public')->putFileAs('products', $file, 'test.jpg');
        
        // Create fake variants
        Storage::disk('public')->put('products/thumbnails/test.jpg', 'thumbnail');
        Storage::disk('public')->put('products/medium/test.jpg', 'medium');
        
        $originalUrl = $this->uploader->getImageUrl($path, 'original');
        $thumbnailUrl = $this->uploader->getImageUrl($path, 'thumbnail');
        $mediumUrl = $this->uploader->getImageUrl($path, 'medium');
        
        $this->assertStringContainsString('products/test.jpg', $originalUrl);
        $this->assertStringContainsString('products/thumbnails/test.jpg', $thumbnailUrl);
        $this->assertStringContainsString('products/medium/test.jpg', $mediumUrl);
    }

    /** @test */
    public function it_processes_images_correctly_when_job_runs()
    {
        // Use real storage for this test since we need actual image processing
        Storage::fake('public');
        
        // Create test directories
        Storage::disk('public')->makeDirectory('products/thumbnails');
        Storage::disk('public')->makeDirectory('products/medium');
        
        $file = UploadedFile::fake()->image('product.jpg', 1000, 1000);
        $path = Storage::disk('public')->putFileAs('products', $file, 'test-process.jpg');
        
        // Run the job
        $job = new ProcessProductImage($path);
        $job->handle();
        
        // Verify all sizes exist
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists('products/thumbnails/test-process.jpg');
        Storage::disk('public')->assertExists('products/medium/test-process.jpg');
    }

    /** @test */
    public function it_handles_custom_path_correctly()
    {
        Queue::fake();
        
        $file = UploadedFile::fake()->image('product.jpg');
        $customFilename = 'custom-name-' . time() . '.jpg';
        
        $path = $this->uploader->upload($file, $customFilename);
        
        $this->assertEquals('products/' . $customFilename, $path);
        Storage::disk('public')->assertExists($path);
    }
}
