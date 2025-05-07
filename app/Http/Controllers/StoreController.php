<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\UserAndStore; // Import the UserAndStore model
use App\Models\WorkingHours;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // Method to store the new store information
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'storename' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'backroom' => 'required|boolean',
            'user_id' => 'required|exists:users,id', // Validate user exists
            'workingHours' => 'required|array',
        ]);

        // Create a new store record
        $store = Store::create([
            'storename' => $validatedData['storename'],
            'address' => $validatedData['address'],
            'category' => $validatedData['category'],
            'backroom' => $validatedData['backroom'],
        ]);

        // Update the user's store_id directly
        $user = User::find($validatedData['user_id']);
        $user->store_id = $store->id;
        $user->save();

        // Create a record in the user_and_stores table
        UserAndStore::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        // Create working hours entries
        foreach ($validatedData['workingHours'] as $day => $hours) {
            WorkingHours::create([
                'store_id' => $store->id,
                'opening_time' => $hours['startTime'],
                'closing_time' => $hours['endTime'],
                'day' => $day,
            ]);
        }

        return response()->json([
            'message' => 'Store created successfully',
            'store' => $store,
            'user' => $user  // This now includes store_id
        ], 201);
    }

    public function show($store_id)
    {
      $store = Store::with('workingHours')->findOrFail($store_id);
      return response()->json($store);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'storename' => 'required|string|max:255',
            'address' => 'required|string',
            'category' => 'required|string',
            'backroom' => 'required|boolean',
        ]);

        $store = Store::findOrFail($id);
        $store->update($request->only(['storename', 'address', 'category', 'backroom']));

        return response()->json($store, 200);
    }

}
