<?php

use App\Models\Tenants\Category;
use App\Models\Tenants\Product;
use App\Models\Tenants\ProductAdditionalBarcode;
use App\Models\Tenants\User;
use Tests\RefreshDatabaseWithTenant;

uses(RefreshDatabaseWithTenant::class);

beforeEach(function () {
    $this->user = User::first();
    $this->category = Category::create(['name' => 'Test Category']);
});

test('product can have additional barcodes', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Product',
        'stock' => 10,
        'initial_price' => 1000,
        'selling_price' => 1500,
        'barcode' => '123456789',
    ]);

    $additionalBarcode1 = ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'ADD001',
    ]);

    $additionalBarcode2 = ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'ADD002',
    ]);

    expect($product->additionalBarcodes()->count())->toBe(2);
    expect($product->activeBarcodes()->count())->toBe(2);
});

test('product search includes additional barcodes in API', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Product',
        'stock' => 10,
        'initial_price' => 1000,
        'selling_price' => 1500,
        'barcode' => '123456789',
    ]);

    ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'SEARCHABLE123',
    ]);

    $this->actingAs($this->user)
        ->getJson('/api/master/product?filter[global]=SEARCHABLE123')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('scanner can find product by additional barcode', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Product',
        'stock' => 10,
        'initial_price' => 1000,
        'selling_price' => 1500,
        'barcode' => '123456789',
    ]);

    ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'SCANNER001',
    ]);

    // Test if the scanner can find the product using the additional barcode
    $foundProduct = Product::whereBarcode('SCANNER001')
        ->orWhere('sku', 'SCANNER001')
        ->orWhereHas('activeBarcodes', function ($query) {
            $query->where('additional_barcode', 'SCANNER001');
        })
        ->first();

    expect($foundProduct)->not->toBeNull();
    expect($foundProduct->id)->toBe($product->id);
});

test('deleted additional barcodes are excluded from search', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Product',
        'stock' => 10,
        'initial_price' => 1000,
        'selling_price' => 1500,
        'barcode' => '123456789',
    ]);

    $deletedBarcode = ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'DELETED001',
        'is_deleted' => true,
    ]);

    $activeBarcode = ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'ACTIVE001',
        'is_deleted' => false,
    ]);

    expect($product->additionalBarcodes()->count())->toBe(2);
    expect($product->activeBarcodes()->count())->toBe(1);

    // Test scanner cannot find product by deleted barcode
    $foundProduct = Product::whereBarcode('DELETED001')
        ->orWhere('sku', 'DELETED001')
        ->orWhereHas('activeBarcodes', function ($query) {
            $query->where('additional_barcode', 'DELETED001');
        })
        ->first();

    expect($foundProduct)->toBeNull();

    // Test scanner can find product by active barcode
    $foundProduct = Product::whereBarcode('ACTIVE001')
        ->orWhere('sku', 'ACTIVE001')
        ->orWhereHas('activeBarcodes', function ($query) {
            $query->where('additional_barcode', 'ACTIVE001');
        })
        ->first();

    expect($foundProduct)->not->toBeNull();
    expect($foundProduct->id)->toBe($product->id);
});

test('additional barcode must be unique per product', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Product',
        'stock' => 10,
        'initial_price' => 1000,
        'selling_price' => 1500,
        'barcode' => '123456789',
    ]);

    ProductAdditionalBarcode::create([
        'product_id' => $product->id,
        'additional_barcode' => 'UNIQUE001',
    ]);

    // Try to create another barcode with the same value for the same product
    expect(function () use ($product) {
        ProductAdditionalBarcode::create([
            'product_id' => $product->id,
            'additional_barcode' => 'UNIQUE001',
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});