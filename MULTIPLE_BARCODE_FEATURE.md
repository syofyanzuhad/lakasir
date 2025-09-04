# Multiple Barcode Feature Implementation

## Overview
This implementation adds support for multiple barcodes per product in the Lakasir POS system, addressing issue #320.

## Features Implemented

### 1. Database Schema
- Created `product_additional_barcodes` table with:
  - `product_id` (foreign key to products table)
  - `additional_barcode` (string, indexed)
  - `is_deleted` (boolean for soft delete functionality)
  - Unique constraint on `product_id` + `additional_barcode`

### 2. Model Relationships
- **ProductAdditionalBarcode** model with:
  - `belongsTo` relationship with Product
  - `active()` scope to filter non-deleted barcodes
- **Product** model enhanced with:
  - `additionalBarcodes()` - all additional barcodes
  - `activeBarcodes()` - only non-deleted additional barcodes

### 3. Search Functionality Enhanced
- **Scanner Search** (`CartInteraction::addCartUsingScanner`):
  - Now searches in main barcode, SKU, and additional barcodes
  - Uses `orWhereHas('activeBarcodes')` to find products by additional barcodes

- **API Search** (`SearchFields` filter):
  - Enhanced to automatically search additional barcodes when 'barcode' field is included
  - Searches only active (non-deleted) additional barcodes

- **Table Search** (`TableProduct` trait):
  - Updated searchable fields to include `activeBarcodes.additional_barcode`
  - Updated search placeholder to mention additional barcodes

- **Global Search** (ProductResource):
  - Added `activeBarcodes.additional_barcode` to globally searchable attributes

### 4. Admin Interface (Filament)
- **Form Component**: Added repeater field for managing additional barcodes
  - Uses relationship to automatically save to the junction table
  - Includes barcode scanning helper text
  - Validates uniqueness per product
  - Supports cloning and deletion with confirmation

- **Info View**: Enhanced to display additional barcodes as badges

### 5. Translation Support
- Added translations in English, Indonesian, and Spanish for:
  - "Additional Barcodes"
  - "Add Additional Barcode"
  - "Enter additional barcode"
  - Updated search placeholders

### 6. Test Coverage
- Comprehensive test suite covering:
  - Model relationships
  - API search functionality
  - Scanner search functionality
  - Soft delete behavior
  - Uniqueness constraints

## Usage Examples

### Adding Additional Barcodes via Admin
1. Edit a product in the admin panel
2. Scroll to "Additional Barcodes" section
3. Click "Add Additional Barcode"
4. Scan or enter the barcode
5. Save the product

### Scanner Integration
- Any barcode scanner in the POS will now find products using:
  - Main product barcode
  - Product SKU
  - Any active additional barcode

### API Search
```php
// Search for products using any barcode
GET /api/master/product?filter[global]=ADDITIONAL_BARCODE_VALUE
```

## Technical Details

### Database Migration
```sql
CREATE TABLE product_additional_barcodes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    additional_barcode VARCHAR(255) NOT NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_barcode (product_id, additional_barcode),
    INDEX idx_barcode_lookup (additional_barcode, is_deleted)
);
```

### Search Query Example
```php
// How products are found by any barcode
$product = Product::whereBarcode($scannedValue)
    ->orWhere('sku', $scannedValue)
    ->orWhereHas('activeBarcodes', function ($query) use ($scannedValue) {
        $query->where('additional_barcode', $scannedValue);
    })
    ->first();
```

## Benefits
1. **Flexibility**: Products can have multiple barcodes for different suppliers
2. **Migration**: Easy to migrate from other systems with different barcode schemes
3. **Variants**: Same product with different packaging can share barcodes
4. **Supplier**: Different suppliers can use their own barcode system
5. **Performance**: Indexed searches ensure fast barcode lookup
6. **Data Integrity**: Soft delete preserves history while maintaining search accuracy

## Files Modified/Created
- `database/migrations/tenant/2024_12_18_000000_create_product_additional_barcodes_table.php` (new)
- `app/Models/Tenants/ProductAdditionalBarcode.php` (new)
- `app/Models/Tenants/Product.php` (enhanced)
- `app/Filament/Tenant/Pages/Traits/CartInteraction.php` (enhanced)
- `app/Http/Filters/SearchFields.php` (enhanced)
- `app/Filament/Tenant/Pages/Traits/TableProduct.php` (enhanced)
- `app/Filament/Tenant/Resources/ProductResource.php` (enhanced)
- `app/Filament/Tenant/Resources/ProductResource/Traits/HasProductForm.php` (enhanced)
- `tests/Feature/ProductAdditionalBarcodeTest.php` (new)
- `lang/en.json`, `lang/id.json`, `lang/es.json` (enhanced)