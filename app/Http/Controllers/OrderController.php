<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /** Shown right after checkout, only in the browser that placed the order. */
    public function thanks(Order $order): View|RedirectResponse
    {
        if (! in_array($order->number, session('placed_orders', []), true)) {
            return redirect()->route('order.track', ['number' => $order->number]);
        }

        return view('orders.thanks', ['order' => $order->load('items')]);
    }

    /** Order tracking: order number + the phone used at checkout. */
    public function track(Request $request): View
    {
        $order = null;
        $error = null;
        $number = strtoupper(trim((string) $request->query('number', '')));
        $phone = preg_replace('/\D/', '', (string) $request->query('phone', ''));
        $phone = preg_replace('/^880?(?=1)/', '0', $phone);

        if ($number !== '' && $phone !== '') {
            $order = Order::where('number', $number)->where('phone', $phone)->with(['items', 'histories'])->first();
            if (! $order) {
                $error = 'We could not find an order with that number and phone. Please check both and try again.';
            }
        } elseif ($request->has('phone')) {
            $error = 'Enter both your order number and phone number.';
        }

        return view('orders.track', compact('order', 'error', 'number'));
    }
}
