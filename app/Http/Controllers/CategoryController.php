<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Storage;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function destroy(Category $category)
{
    // Check ownership (optional)
    if ($category->store_id !== auth()->user()->store_id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Reset products' category_id to NULL
    Storage::where('category_id', $category->id)
        ->update(['category_id' => null]);

    // Delete the category
    $category->delete();

    return response()->json(['success' => true]);
}
}
