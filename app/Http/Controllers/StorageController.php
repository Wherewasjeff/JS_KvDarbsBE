<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'price' => 'nullable|numeric',
            'shelf_num' => 'nullable|string|max:255',
            'storage_num' => 'nullable|string|max:255',
            'quantity_in_storage' => 'nullable|integer',
            'quantity_in_salesfloor' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        $storageItem = StorageModel::find($id);

        if (!$storageItem) {
            return response()->json(['error' => 'Storage item not found'], 404);
        }

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

    public function update(Request $request, $id)
    {
        Log::info('Update Request:', $request->all());

        $storageItem = StorageModel::find($id);

        if (!$storageItem) {
            return response()->json(['error' => 'Storage item not found'], 404);
        }

        $storageItem->product_name = $request->input('product_name', $storageItem->product_name);
        $storageItem->barcode = $request->input('barcode', $storageItem->barcode);
        $storageItem->category_id = $request->input('category_id', $storageItem->category_id);
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

    // New method to import storage items via JSON file upload with calibration mapping.
    public function importJson(Request $request)
    {
        $validatedData = $request->validate([
            'json_file' => 'required|file|mimes:json',
            'mapping'   => 'required|array',
            // Optionally you can require store_id here:
            'store_id'  => 'required|exists:stores,id',
        ]);

        $file = $request->file('json_file');
        $jsonContent = file_get_contents($file->getPathname());
        $jsonData = json_decode($jsonContent, true);

        if (!$jsonData) {
            return response()->json(['error' => 'Invalid JSON data'], 400);
        }

        // Normalize to an array of records
        if (!isset($jsonData[0])) {
            $jsonData = [$jsonData];
        }

        $importedItems = [];
        $fields = [
            'product_name', 'sku', 'barcode',
            'category_id', 'price', 'shelf_num',
            'storage_num', 'quantity_in_storage',
            'quantity_in_salesfloor', 'image'
        ];

        foreach ($jsonData as $record) {
            $dataToInsert = [];
            // Assign store_id from request, required field.
            $dataToInsert['store_id'] = $request->input('store_id');

            foreach ($fields as $field) {
                // For each field, check if mapping exists and is not 'none'
                if (isset($validatedData['mapping'][$field]) && $validatedData['mapping'][$field] !== 'none') {
                    $jsonKey = $validatedData['mapping'][$field];
                    $dataToInsert[$field] = isset($record[$jsonKey]) ? $record[$jsonKey] : null;
                } else {
                    $dataToInsert[$field] = null;
                }
            }

            try {
                $storageItem = StorageModel::create($dataToInsert);
                $importedItems[] = $storageItem;
            } catch (\Exception $e) {
                Log::error("Error importing record", ['error' => $e->getMessage(), 'record' => $record]);
            }
        }

        return response()->json([
            'success' => true,
            'imported_count' => count($importedItems),
            'data' => $importedItems
        ]);
    }
}
