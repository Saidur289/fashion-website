<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, Order $order)
    {
        abort_unless($request->user()?->isAdmin() && ! $request->user()?->is_blocked, 403);

        $order->load(['items', 'histories.changer']);

        return view('admin.invoice', ['order' => $order]);
    }
}
