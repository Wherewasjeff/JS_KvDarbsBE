<?php
namespace App\Http\Controllers;

use App\Models\User; // Import the User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Function to show user details
    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
//        Log::info("User ID received", ['id' => $request]);
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,',
        ]);

        // Get the authenticated user
        $user = $request->user();
        Log::info($request->user());
        // Update user data
        $user->update($request->only(['name', 'last_name', 'email']));

        return response()->json($user, 200);
    }

}
