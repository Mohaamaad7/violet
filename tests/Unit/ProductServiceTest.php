<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $productService;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productService = new ProductService();
        
        // Create a test category
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_product_with_images()
    {
        $data = [
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
            'status' => 'active',
            'images' => [
                [
                    'image' => 'products/test1.jpg',
                    'is_primary' => true,
                    'order' => 0,
                ],
                [
                    'image' => 'products/test2.jpg',
                    'is_primary' => false,
                    'order' => 1,
                ],
            ],
        ];

        $product = $this->productService->createWithImages($data);

        // Assert product was created
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(10, $product->stock);
        $this->assertNotEmpty($product->sku);
        $this->assertNotEmpty($product->slug);

        // Assert images were created
        $this->assertCount(2, $product->images);
        
        $primaryImage = $product->images->where('is_primary', true)->first();
        $this->assertNotNull($primaryImage);
        $this->assertEquals('products/test1.jpg', $primaryImage->image_path);
    }

    /** @test */
    public function it_can_update_product_with_images()
    {
        // Create initial product
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Original Product',
            'slug' => 'original-product',
            'sku' => 'TEST-001',
            'price' => 50.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        // Add initial image
        $product->images()->create([
            'image_path' => 'products/old.jpg',
            'is_primary' => true,
            'order' => 0,
        ]);

        // Update product with new data
        $updateData = [
            'name' => 'Updated Product',
            'price' => 75.00,
            'images' => [
                [
                    'image' => 'products/new1.jpg',
                    'is_primary' => true,
                    'order' => 0,
                ],
            ],
        ];

        $updatedProduct = $this->productService->updateWithImages($product, $updateData);

        // Assert product was updated
        $this->assertEquals('Updated Product', $updatedProduct->name);
        $this->assertEquals(75.00, $updatedProduct->price);
        $this->assertEquals('updated-product', $updatedProduct->slug);

        // Assert images were replaced
        $this->assertCount(1, $updatedProduct->images);
        $this->assertEquals('products/new1.jpg', $updatedProduct->images->first()->image_path);
    }

    /** @test */
    public function it_can_sync_product_variants()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-002',
            'price' => 100.00,
            'stock' => 20,
            'status' => 'active',
        ]);

        $variants = [
            [
                'sku' => 'TEST-002-RED',
                'name' => 'Red Variant',
                'price' => 100.00,
                'stock' => 10,
                'attributes' => ['color' => 'red'],
            ],
            [
                'sku' => 'TEST-002-BLUE',
                'name' => 'Blue Variant',
                'price' => 105.00,
                'stock' => 10,
                'attributes' => ['color' => 'blue'],
            ],
        ];

        $productWithVariants = $this->productService->syncVariants($product, $variants);

        // Assert variants were created
        $this->assertCount(2, $productWithVariants->variants);
        
        $redVariant = $productWithVariants->variants->where('sku', 'TEST-002-RED')->first();
        $this->assertNotNull($redVariant);
        $this->assertEquals('Red Variant', $redVariant->name);
        $this->assertEquals(100.00, $redVariant->price);
        $this->assertEquals(10, $redVariant->stock);
        $this->assertEquals(['color' => 'red'], $redVariant->attributes);

        $blueVariant = $productWithVariants->variants->where('sku', 'TEST-002-BLUE')->first();
        $this->assertNotNull($blueVariant);
        $this->assertEquals('Blue Variant', $blueVariant->name);
    }

    /** @test */
    public function it_replaces_existing_variants_when_syncing()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-003',
            'price' => 100.00,
            'stock' => 20,
            'status' => 'active',
        ]);

        // Create initial variants
        $product->variants()->create([
            'sku' => 'OLD-VARIANT',
            'name' => 'Old Variant',
            'price' => 100.00,
            'stock' => 5,
        ]);

        $this->assertCount(1, $product->variants);

        // Sync new variants
        $newVariants = [
            [
                'sku' => 'NEW-VARIANT-1',
                'name' => 'New Variant 1',
                'price' => 110.00,
                'stock' => 8,
            ],
        ];

        $productWithNewVariants = $this->productService->syncVariants($product, $newVariants);

        // Assert old variants were deleted and new ones created
        $this->assertCount(1, $productWithNewVariants->variants);
        $this->assertEquals('NEW-VARIANT-1', $productWithNewVariants->variants->first()->sku);
        $this->assertNull(ProductVariant::where('sku', 'OLD-VARIANT')->first());
    }

    /** @test */
    public function it_throws_exception_when_variant_missing_required_fields()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-004',
            'price' => 100.00,
            'stock' => 20,
            'status' => 'active',
        ]);

        $invalidVariants = [
            [
                'sku' => 'TEST-VARIANT',
                // Missing 'name' field
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Variant must have 'sku' and 'name' fields");

        $this->productService->syncVariants($product, $invalidVariants);
    }

    /** @test */
    public function it_auto_generates_sku_and_slug_when_not_provided()
    {
        $data = [
            'category_id' => $this->category->id,
            'name' => 'Product Without SKU',
            'price' => 50.00,
            'stock' => 5,
            'status' => 'active',
        ];

        $product = $this->productService->createWithImages($data);

        $this->assertNotEmpty($product->sku);
        $this->assertStringStartsWith('PRD-', $product->sku);
        $this->assertEquals('product-without-sku', $product->slug);
    }

    /** @test */
    public function it_ensures_unique_slug_when_duplicate_exists()
    {
        // Create first product
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Duplicate Name',
            'slug' => 'duplicate-name',
            'sku' => 'TEST-005',
            'price' => 50.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        // Create second product with same name
        $data = [
            'category_id' => $this->category->id,
            'name' => 'Duplicate Name',
            'price' => 60.00,
            'stock' => 10,
            'status' => 'active',
        ];

        $product = $this->productService->createWithImages($data);

        $this->assertEquals('duplicate-name-1', $product->slug);
    }

    /** @test */
    public function it_sets_first_image_as_primary_when_not_specified()
    {
        $data = [
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
            'status' => 'active',
            'images' => [
                ['image' => 'products/test1.jpg'],
                ['image' => 'products/test2.jpg'],
            ],
        ];

        $product = $this->productService->createWithImages($data);

        $primaryImage = $product->images->where('is_primary', true)->first();
        $this->assertNotNull($primaryImage);
        $this->assertEquals('products/test1.jpg', $primaryImage->image_path);
    }
}
