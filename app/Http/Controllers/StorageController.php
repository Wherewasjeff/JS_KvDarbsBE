<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 
use App\Models\Storage as StorageModel;
use App\Models\Category;

class StorageController extends Controller
{
    public function storage(Request $request)
    {
        $validatedData = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'price' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'shelf_num' => 'nullable|string|max:255',
            'storage_num' => 'nullable|string|max:255',
            'quantity_in_storage' => 'nullable|integer',
            'should_be' => 'required|integer|min:1',
            'quantity_in_salesfloor' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Updated image handling: move file directly to public/images/public/storage_images
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '-' . $file->getClientOriginalName();
            $destinationPath = public_path('images/public/storage_images');
            $file->move($destinationPath, $filename);
            // Save the relative path to the database.
            $validatedData['image'] = 'images/public/storage_images/' . $filename;
        } else {
            $validatedData['image'] = null;
        }

        $validatedData['category_id'] = $validatedData['category_id'] ?? null;
        $validatedData['price'] = (float)$validatedData['price'];

        $storageItem = StorageModel::create($validatedData);

        return response()->json([
            'message' => 'Storage item added successfully',
            'storageItem' => $storageItem
        ]);
    }

    // Method to retrieve all storage items
    public function getStorage(Request $request)
    {
        $store_id = $request->input('store_id');

        if (!$store_id) {
            return response()->json([
                'success' => false,
                'message' => 'Store ID is required',
            ], 400);
        }

        $products = StorageModel::with('category')
            ->where('store_id', $store_id)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No products found for this store',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function destroy($id)
    {
        $storageItem = StorageModel::with('sales')->find($id); // Load related sales
    
        if (!$storageItem) {
            return response()->json(['error' => 'Storage item not found'], 404);
        }
    
        // Delete related sales first
        $storageItem->sales()->delete();
    
        // Then delete the product
        $storageItem->delete();
    
        return response()->json(['success' => true]);
    }

    public function getCategories(Request $request)
    {
        $storeId = $request->input('store_id');

        if (!$storeId) {
            return response()->json(['error' => 'Store ID is missing'], 400);
        }

        $categories = Category::where('store_id', $storeId)->get();

        return response()->json($categories);
    }

    public function addCategory(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $storeId = $user->store_id ?? null;
        if (!$storeId) {
            return response()->json(['error' => 'Store ID is missing'], 400);
        }

        $validatedData = $request->validate([
            'category' => 'required|string|max:255',
        ]);

        $validatedData['store_id'] = $storeId;

        $category = Category::create($validatedData);

        return response()->json([
            'message' => 'Category added successfully',
            'category' => $category
        ]);
    }
    
public function destroyCategory($id)
{
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['error' => 'Category not found'], 404);
    }

    // Unassign this category from all products
    StorageModel::where('category_id', $id)
                ->update(['category_id' => null]);

    $category->delete();

    return response()->json(['success' => true]);
}

    public function update(Request $request, $id)
    {
        Log::info('Update Request:', $request->all());

        $storageItem = StorageModel::find($id);

        if (!$storageItem) {
            return response()->json(['error' => 'Storage item not found'], 404);
        }

        $storageItem->product_name = $request->input('product_name', $storageItem->product_name);
        $storageItem->barcode = $request->input('barcode', $storageItem->barcode);
        $storageItem->sku          = $request->input('sku',          $storageItem->sku);
        $storageItem->category_id  = $request->input('category_id',  $storageItem->category_id);
        $storageItem->price = $request->input('price', $storageItem->price);
        $storageItem->shelf_num = $request->input('shelf_num', $storageItem->shelf_num);
        $storageItem->storage_num = $request->input('storage_num', $storageItem->storage_num);
        $storageItem->quantity_in_storage = $request->input('quantity_in_storage', $storageItem->quantity_in_storage);
        $storageItem->quantity_in_salesfloor = $request->input('quantity_in_salesfloor', $storageItem->quantity_in_salesfloor);

        // Updated image handling in update:
        if ($request->hasFile('image')) {
            // Optional: Delete the old image if it exists
            if ($storageItem->image && Storage::disk('public')->exists($storageItem->image)) {
                Storage::disk('public')->delete($storageItem->image);
            }
            $file = $request->file('image');
            $filename = time() . '-' . $file->getClientOriginalName();
            $destinationPath = public_path('images/public/storage_images');
            $file->move($destinationPath, $filename);
            $storageItem->image = 'images/public/storage_images/' . $filename;
        }

        $storageItem->updated_at = now();
        $storageItem->save();

        return response()->json([
            'success' => true,
            'storageItem' => $storageItem
        ]);
    }
    public function assignCategories(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:storage,id',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        // Set category_id to null if not provided (assign to uncategorized)
        $categoryId = $validated['category_id'] ?? null;

        // Update products
        StorageModel::whereIn('id', $validated['product_ids'])
            ->update(['category_id' => $categoryId]);

        return response()->json(['success' => true]);
    }
public function replenish($id, Request $request)
{
    try {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $product = StorageModel::findOrFail($id);

        if ($product->quantity_in_storage < $validated['amount']) {
            return response()->json([
                'success' => false,
                'error' => 'Not enough stock in storage'
            ], 400);
        }

        DB::transaction(function () use ($product, $validated) {
            $product->quantity_in_storage -= $validated['amount'];
            $product->quantity_in_salesfloor += $validated['amount'];
            $product->save();
        });

        return response()->json([
            'success' => true,
            'data' => $product->fresh()
        ]);

    } catch (\Exception $e) {
        \Log::error("Replenishment error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Server error'
        ], 500);
    }
}
public function drain($id, Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|integer|min:1'
    ]);

    $product = StorageModel::findOrFail($id);

    if ($product->quantity_in_salesfloor < $validated['amount']) {
        return response()->json([
            'success' => false,
            'error'   => 'Not enough stock on salesfloor'
        ], 400);
    }

    DB::transaction(function () use ($product, $validated) {
        $product->quantity_in_salesfloor -= $validated['amount'];
        $product->quantity_in_storage    += $validated['amount'];
        $product->save();
    });

    return response()->json([
        'success' => true,
        'data'    => $product->fresh()
    ]);
}
}
