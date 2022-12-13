<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;

class TransaksiController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if($id)
        {
            $transaksi = Transaksi::with('items.product')->find($id);
        }

        if($transaksi)
        {
            return ResponseFormatter::success(
                $transaksi,
                'Data transaksi behasil diambil'
            );
        }
        else
        {
            return ResponseFormatter::error(
            null,
                'Data transaksi tidak ada',
                404
            );
        }

        $transaksi = Transaksi::with(['items.product'])->where('users_id', Auth::user()->id);

        if ($status)
        {
            $transaksi->where('status', $status);
        }

        return ResponseFormatter::success(
            $transaksi->paginate($limit),
            'Data list transaksi berhasil diambil',
        );

    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED'
        ]);

        $transaksi = Transaksi::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status,
        ]);

        foreach ($request->items as $product) {
            TransaksiItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transaksi_id' => $transaksi->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success($transaksi->load('items.product'), 'Transaksi berhasil');
    }
}
