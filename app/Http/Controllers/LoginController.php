<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Worker;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required', // Can be email (for owners) or username (for workers)
            'password' => 'required',
        ]);

        // Check if the identifier is an email (Owner login)
        if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->identifier)->with(['userAndStore:id,user_id,store_id'])->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $response = $user->toArray();
            $response['store_id'] = $user->userAndStore ? $user->userAndStore->store_id : null;
            unset($response['user_and_stores']);

            $token = $user->createToken('login_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $response,
                'role' => 'owner' // Indicate the user role
            ]);
        }

        // Otherwise, attempt worker login using username
        $worker = Worker::where('username', $request->identifier)->first();

        if (!$worker || !Hash::check($request->password, $worker->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $worker->createToken('worker_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $worker,
            'role' => 'worker' // Indicate the user role
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke all tokens for the user
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
