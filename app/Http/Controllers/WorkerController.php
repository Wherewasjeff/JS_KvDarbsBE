<?php
namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerController extends Controller {
    public function index(Request $request) {
        $workers = Worker::where('store_id', $request->store_id)->get();
        return response()->json($workers);
    }

    public function store(Request $request)
    {
        \Log::info("Received request:", $request->all()); // Debugging

        $request->validate([
            'name' => 'required|string',
            'lastname' => 'required|string',
            'age' => 'required|integer',
            'address' => 'required|string',
            'salary' => 'required|numeric',
            'position' => 'required|string',
            'username' => 'required|string|unique:workers',
            'password' => 'required|string|min:6',
            'store_id' => 'required|exists:stores,id'
        ]);

        $worker = Worker::create([
            'store_id' => $request->store_id,
            'name' => $request->name,
            'lastname' => $request->lastname,
            'age' => $request->age,
            'address' => $request->address,
            'salary' => $request->salary,
            'position' => $request->position,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        return response()->json($worker, 201);
    }

    public function update(Request $request, Worker $worker) {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'age' => 'sometimes|integer|min:18',
            'address' => 'sometimes|string',
            'salary' => 'sometimes|numeric|min:0',
            'position' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|unique:workers,username,' . $worker->id,
            'password' => 'nullable|string|min:6'
        ]);

        $worker->update([
            'name' => $request->name ?? $worker->name,
            'lastname' => $request->lastname ?? $worker->lastname,
            'age' => $request->age ?? $worker->age,
            'address' => $request->address ?? $worker->address,
            'salary' => $request->salary ?? $worker->salary,
            'position' => $request->position ?? $worker->position,
            'username' => $request->username ?? $worker->username,
            'password' => $request->password ? Hash::make($request->password) : $worker->password,
        ]);

        return response()->json(['worker' => $worker, 'message' => 'Worker updated successfully']);
    }

    public function destroy(Worker $worker) {
        $worker->delete();
        return response()->json(['message' => 'Worker deleted successfully']);
    }
}
