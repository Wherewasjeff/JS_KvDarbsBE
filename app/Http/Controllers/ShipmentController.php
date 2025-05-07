<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\Storage as StorageModel;

class ShipmentController extends Controller
{
// GET /api/shipments
public function index()
{
    $shipments = Shipment::with('items.product')
        ->where('store_id', auth()->user()->store_id)
        ->orderBy('shipment_date')
        ->get()
        ->map(function($s) {
            return [
                'id'            => $s->id,
                'shipment_date' => $s->shipment_date,
                'products'      => $s->items->map(function($i) {
                    return [
                        'product_name' => $i->product->product_name,
                        'amount'       => $i->amount,
                    ];
                })->toArray(),
            ];
        });

    return response()->json($shipments);
}

// POST /api/shipments
public function store(Request $r)
{
    $data = $r->validate([
        'shipment_date'       => 'nullable|date',
        'products'            => 'required|array|min:1',
        'products.*.id'       => 'required|exists:storage,id',
        'products.*.amount'   => 'required|integer|min:1',
    ]);

    // If the shipment is "immediate", just bump the storage qty and return
    if (is_null($data['shipment_date'])) {
        foreach ($data['products'] as $p) {
            $product = StorageModel::findOrFail($p['id']);
            $product->increment('quantity_in_storage', $p['amount']);
        }
        return response()->json(['success' => true]);
    }

    // Otherwise it's a future-dated shipment: create the shipment record
    $s = Shipment::create([
        'shipment_date' => $data['shipment_date'],
        'store_id'      => auth()->user()->store_id,
    ]);

    // And its line-items
    foreach ($data['products'] as $p) {
        $s->items()->create([
            'product_id' => $p['id'],
            'amount'     => $p['amount'],
        ]);
    }

    return response()->json(['success' => true]);
}

    // POST /api/shipments/process
    // Call this daily via cron to actually add stock on the shipment date
    public function process()
    {
        $today = now()->toDateString();
        $shipments = Shipment::with('items')
            ->where('shipment_date', $today)
            ->get();

        foreach ($shipments as $s) {
            foreach ($s->items as $item) {
                StorageModel::find($item->product_id)
                    ->increment('quantity_in_storage', $item->amount);
            }
        }

        return response()->json([
            'processed' => $shipments->count(),
        ]);
    }
    public function destroy($id)
{
    $shipment = Shipment::with('items')->findOrFail($id);

    // delete child items first (if you didn't set up cascade in DB)
    $shipment->items()->delete();

    // then delete the shipment itself
    $shipment->delete();

    return response()->json([
        'success' => true,
    ]);
}
public function destroyByDate($date)
{
    // Only remove shipments for this store, on that date
    Shipment::where('store_id', auth()->user()->store_id)
            ->where('shipment_date', $date)
            ->delete();

    return response()->json([
        'success' => true,
    ]);
}
}
