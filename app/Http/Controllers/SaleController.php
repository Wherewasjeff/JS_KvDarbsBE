<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Storage as StorageModel;

class SaleController extends Controller
{
public function store(Request $req)
{
    // 1) Validate incoming payload:
    $data = $req->validate([
        'store_id'                  => 'required|exists:stores,id',
        'items'                     => 'required|array|min:1',
        'items.*.product_id'        => 'required|exists:storage,id',
        'items.*.sold'              => 'required|integer|min:1',
        'items.*.discount_percent'  => 'nullable|numeric|min:0|max:100',
        'items.*.discount_reason'   => 'nullable|string|max:255',
        'total_discount.percent'    => 'nullable|numeric|min:0|max:100',
        'total_discount.reason'     => 'nullable|string|max:255',
    ]);

    DB::beginTransaction();
    try {
        // pull out the total discount once
        $totalDiscount = $data['total_discount'] ?? null;
        $totalPct      = $totalDiscount['percent'] ?? null;
        $totalReason   = $totalDiscount['reason']  ?? null;

        foreach ($data['items'] as $line) {
            // a) decrement stock
            $updated = StorageModel::where('id', $line['product_id'])
                ->where('quantity_in_salesfloor', '>=', $line['sold'])
                ->decrement('quantity_in_salesfloor', $line['sold']);
            if (! $updated) {
                throw new \Exception("Insufficient stock for product {$line['product_id']}");
            }

            $storage = StorageModel::findOrFail($line['product_id']);
            $actor   = Auth::user();

            // compute unit price after item‐level discount (if any)
            $unitPrice = (float)$storage->price;
            if (! empty($line['discount_percent'])) {
                $unitPrice *= (1 - $line['discount_percent']/100);
            }
            // then apply total‐level discount (if any)
            if ($totalPct !== null) {
                $unitPrice *= (1 - $totalPct/100);
            }

            // build the row payload
            $saleData = [
                'store_id'                  => $data['store_id'],
                'product_id'                => $line['product_id'],
                'price'                     => round($unitPrice, 2),
                'sold'                      => $line['sold'],
                'date'                      => today()->toDateString(),
                'time'                      => now()->toTimeString(),
                'discount_percent'          => $line['discount_percent'] ?? null,
                'discount_reason'           => $line['discount_reason']  ?? null,
                'total_discount_percent'    => $totalPct,
                'total_discount_reason'     => $totalReason,
            ];

            // detect user vs worker
            if ($actor instanceof \App\Models\Worker) {
                $saleData['worker_id'] = $actor->id;
            } elseif ($actor instanceof \App\Models\User) {
                $saleData['user_id']   = $actor->id;
            } else {
                throw new \Exception("No authenticated actor for sale");
            }

            Sale::create($saleData);
        }

        DB::commit();
        return response()->json(['success' => true], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Sale processing failed: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}
    public function index(Request $req)
    {
        $storeId = $req->query('store_id');
        if (! $storeId) {
            return response()->json(['message' => 'store_id query param required'], 400);
        }

        $sales = Sale::with(['product', 'user', 'worker'])
            ->where('store_id', $storeId)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get()
            ->map(function($sale) {
                return [
                    'id'             => $sale->id,
                    'date'           => $sale->date,
                    'time'           => $sale->time,
                    'sold'           => $sale->sold,
                    'price'          => (float)$sale->price,
                    'product_id'     => $sale->product_id,
                    'product_name'   => $sale->product->product_name,
                    'seller_name'    => $sale->seller_name,
                    'discount_percent' => $sale->discount_percent,
                    'discount_reason'  => $sale->discount_reason,
                    'total_discount_percent' => $sale->total_discount_percent,
                    'total_discount_reason'  => $sale->total_discount_reason,
                ];
            });

        return response()->json($sales);
    }
}
