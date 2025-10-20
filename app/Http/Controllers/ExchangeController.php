<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'type' => 'required|in:free,paid,barter',
            'offer_price' => 'nullable|numeric|min:0'
        ]);

        // Check if user is not the product owner
        if ($product->user_id === Auth::id()) {
            return back()->with('error', 'You cannot request your own product.');
        }

        // Check if product is available
        if (!$product->is_available) {
            return back()->with('error', 'This product is no longer available.');
        }

        // Check if user already has a pending request for this product
        $existingExchange = Exchange::where('product_id', $product->id)
            ->where('from_user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($existingExchange) {
            return back()->with('error', 'You already have a pending request for this product.');
        }

        $exchange = Exchange::create([
            'product_id' => $product->id,
            'from_user_id' => Auth::id(),
            'to_user_id' => $product->user_id,
            'type' => $request->type,
            'agreed_price' => $request->offer_price,
            'message' => $request->message,
            'status' => 'pending',
            'exchange_date' => now()->addHours(24),
        ]);

        return redirect()->route('exchanges.my')
            ->with('success', 'Exchange request sent successfully! The seller has 24 hours to respond.');
    }

    public function accept(Exchange $exchange)
    {
        if ($exchange->to_user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($exchange->status !== 'pending') {
            return back()->with('error', 'This exchange request is no longer pending.');
        }

        $exchange->update([
            'status' => 'accepted',
            'exchange_date' => now()->addDays(1) // Give 24 hours to complete
        ]);

        // Mark product as unavailable
        $exchange->product->update(['is_available' => false]);

        // Reject all other pending requests for the same product
        Exchange::where('product_id', $exchange->product_id)
            ->where('id', '!=', $exchange->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Exchange request accepted! Please coordinate with the requester to complete the exchange.');
    }

    public function complete(Exchange $exchange)
    {
        if (!in_array(Auth::id(), [$exchange->from_user_id, $exchange->to_user_id])) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($exchange->status !== 'accepted') {
            return back()->with('error', 'Only accepted exchanges can be completed.');
        }

        $exchange->update(['status' => 'completed']);

        // Update user exchange counts
        $exchange->fromUser->increment('total_exchanges');
        $exchange->toUser->increment('total_exchanges');

        return back()->with('success', 'Exchange marked as completed! Thank you for using NeighborShare.');
    }

    public function cancel(Exchange $exchange)
    {
        if (!in_array(Auth::id(), [$exchange->from_user_id, $exchange->to_user_id])) {
            return back()->with('error', 'Unauthorized action.');
        }

        $previousStatus = $exchange->status;

        $exchange->update(['status' => 'cancelled']);

        // If the exchange was accepted, make the product available again
        if ($previousStatus === 'accepted') {
            $exchange->product->update(['is_available' => true]);
        }

        $message = $previousStatus === 'pending'
            ? 'Exchange request cancelled.'
            : 'Exchange cancelled.';

        return back()->with('success', $message);
    }

    public function myExchanges()
    {
        $sentExchanges = Exchange::where('from_user_id', Auth::id())
            ->with('product', 'toUser')
            ->latest()
            ->paginate(10, ['*'], 'sent_page');

        $receivedExchanges = Exchange::where('to_user_id', Auth::id())
            ->with('product', 'fromUser')
            ->latest()
            ->paginate(10, ['*'], 'received_page');

        return view('exchanges.my', compact('sentExchanges', 'receivedExchanges'));
    }

    public function show(Exchange $exchange)
    {
        // Check if current user is involved in exchange
        if (!in_array(Auth::id(), [$exchange->from_user_id, $exchange->to_user_id])) {
            abort(403, 'Unauthorized action.');
        }

        $exchange->load('product', 'fromUser', 'toUser');
        return view('exchanges.show', compact('exchange'));
    }
}
