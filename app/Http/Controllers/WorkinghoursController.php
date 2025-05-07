<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workinghours; // Make sure you have this model

class WorkingHoursController extends Controller // Fixed typo in "Controller"
{
public function update(Request $request, $id = null)
{
    try {
        $user = auth()->user();
        
        if (!$user->store_id) {
            return response()->json(['error' => 'User not associated with a store'], 403);
        }

        $validated = $request->validate([
            'day' => 'required|string|max:3',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    // Allow both to be 00:00 (closed day)
                    if ($request->input('opening_time') === '00:00' && 
                        $request->input('closing_time') === '00:00') {
                        return;
                    }
                    // Normal validation for other cases
                    if (strtotime($value) <= strtotime($request->input('opening_time'))) {
                        $fail('The closing time must be after opening time.');
                    }
                },
            ],
        ]);

        // Convert to HH:mm:ss format
        $validated['opening_time'] .= ':00';
        $validated['closing_time'] .= ':00';

        $workingHours = $id 
            ? WorkingHours::where('id', $id)
                ->where('store_id', $user->store_id)
                ->firstOrFail()
            : new WorkingHours();

        $workingHours->fill($validated);
        $workingHours->store_id = $user->store_id;
        $workingHours->save();

        return response()->json([
            'message' => 'Working hours saved successfully',
            'data' => $workingHours
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'Working hours record not found'], 404);
    } catch (\Exception $e) {
        \Log::error('Working hours save error: '.$e->getMessage());
        return response()->json(['error' => 'Server error: '.$e->getMessage()], 500);
    }
}
}