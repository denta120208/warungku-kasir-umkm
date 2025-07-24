<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with('user')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::create([
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil ditambahkan',
            'data' => $sale->load('user')
        ], 201);
    }

    public function show(Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $sale->load('user')
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sale->update([
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $request->quantity * $request->price,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil diperbarui',
            'data' => $sale->load('user')
        ]);
    }

    public function destroy(Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil dihapus'
        ]);
    }

    public function today(Request $request)
    {
        $sales = Sale::where('user_id', $request->user()->id)
            ->whereDate('created_at', Carbon::today())
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSales = $sales->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales,
                'total_amount' => $totalSales,
                'total_items' => $sales->count()
            ]
        ]);
    }
}
