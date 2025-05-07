<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomWorkingHour;

class CustomWorkingHoursController extends Controller
{
  public function store(Request $request)
  {
    $validated = $request->validate([
      'date' => 'required|date',
      'opening_time' => 'required|date_format:H:i',
      'closing_time' => [
      'required',
      'date_format:H:i',
      function($attribute, $value, $fail) use ($request) {
          if ($request->input('opening_time') === '00:00'
              && $value === '00:00') {
              return;
          }
          if (strtotime($value) <= strtotime($request->input('opening_time'))) {
              $fail('The closing time must be after the opening time.');
          }
      },
  ],
      'comment' => 'nullable|string',
      'store_id' => 'required|exists:stores,id'
    ]);

    $entry = CustomWorkingHour::updateOrCreate(
       [
         'date'     => $validated['date'],
         'store_id' => $validated['store_id']
       ],
       [
         'opening_time' => $validated['opening_time'],
         'closing_time' => $validated['closing_time'],
         'comment'      => $validated['comment']
       ]
    );

    return response()->json($entry);
  }
    public function all(Request $request)
  {
      $validated = $request->validate([
          'store_id' => 'required|exists:stores,id',
      ]);

      $entries = CustomWorkingHour::where('store_id', $validated['store_id'])
                  // ->whereMonth('date', now()->month)
                  // ->whereYear('date', now()->year)
                  ->get();

      return response()->json($entries);
  }
  public function index(Request $request)
  {
      $validated = $request->validate([
        'date'     => 'required|date',
        'store_id' => 'required|exists:stores,id'
      ]);

      $entry = CustomWorkingHour::where('date', $validated['date'])
               ->where('store_id', $validated['store_id'])
               ->first();

      if (! $entry) {
          return response()->json(null, 204);
      }

      return response()->json($entry);
  }
}