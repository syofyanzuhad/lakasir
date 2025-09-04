# Visual UI Mockup for Multiple Barcode Feature

## Product Edit Form - Additional Barcodes Section

```
┌─────────────────────────────────────────────────────────────────┐
│                     Edit Product: "Coffee Beans"                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Name: [Coffee Beans                                          ]  │
│                                                                 │
│ Barcode: [123456789                                          ]  │
│ (Point the cursor to this input first then scan the barcode)    │
│                                                                 │
│ SKU: [COFFEE001                                              ]  │
│                                                                 │
│ ▼ Additional Barcodes                                           │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Additional Barcode                                          │ │
│ │ [987654321                                              ]   │ │
│ │ (Point the cursor to this input first then scan barcode)   │ │
│ │                                            [🗑️ Delete]      │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Additional Barcode                                          │ │
│ │ [SUP2024001                                             ]   │ │
│ │ (Point the cursor to this input first then scan barcode)   │ │
│ │                                            [🗑️ Delete]      │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ [+ Add Additional Barcode]                                      │
│                                                                 │
│ Category: [Beverages                    ▼]                      │
│                                                                 │
│ Stock: [100                                                  ]  │
│                                                                 │
│                               [Cancel]           [Save Product] │
└─────────────────────────────────────────────────────────────────┘
```

## Product Info View - Additional Barcodes Display

```
┌─────────────────────────────────────────────────────────────────┐
│                    Product Details: Coffee Beans               │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Name: Coffee Beans                                              │
│ Category: Beverages                                             │
│ SKU: COFFEE001                                                  │
│ Barcode: 123456789                                              │
│                                                                 │
│ Additional Barcodes:                                            │
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│ │ 987654321   │ │ SUP2024001  │ │ VENDOR_XYZ  │               │
│ └─────────────┘ └─────────────┘ └─────────────┘               │
│                                                                 │
│ Stock: 100 PCS                                                  │
│ Initial Price: IDR 15,000                                       │
│ Selling Price: IDR 25,000                                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## POS Scanner Search Behavior

### Before (Original):
```
Scanner Input: "987654321"
Search Query: WHERE barcode = '987654321' OR sku = '987654321'
Result: ❌ Product not found
```

### After (With Multiple Barcodes):
```
Scanner Input: "987654321"
Search Query: 
  WHERE barcode = '987654321' 
  OR sku = '987654321'
  OR EXISTS (
    SELECT 1 FROM product_additional_barcodes 
    WHERE product_id = products.id 
    AND additional_barcode = '987654321' 
    AND is_deleted = false
  )
Result: ✅ Found "Coffee Beans"
```

## Search Results Display

### Product Table Search:
```
Search: "987654321"

┌─────────────────────────────────────────────────────────────────┐
│ Search (SKU, name, barcode, additional barcodes): [987654321 ] │
├─────────────────────────────────────────────────────────────────┤
│ ID │ Name         │ SKU       │ Barcode   │ Stock │ Price      │
├────┼──────────────┼───────────┼───────────┼───────┼────────────┤
│ 23 │ Coffee Beans │ COFFEE001 │ 123456789 │ 100   │ IDR 25,000 │
│    │              │           │ +2 more   │       │            │
└─────────────────────────────────────────────────────────────────┘
```

## API Response Example:
```json
{
  "data": [
    {
      "id": 23,
      "name": "Coffee Beans",
      "sku": "COFFEE001", 
      "barcode": "123456789",
      "additional_barcodes": [
        {
          "id": 1,
          "additional_barcode": "987654321",
          "is_deleted": false
        },
        {
          "id": 2,
          "additional_barcode": "SUP2024001",
          "is_deleted": false
        }
      ],
      "selling_price": 25000,
      "stock": 100
    }
  ]
}
```