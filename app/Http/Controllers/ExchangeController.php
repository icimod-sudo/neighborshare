<?php

namespace App\Http\Controllers;

use App\Models\Exchange;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ExchangeRequestNotification;
use App\Notifications\ExchangeAcceptedNotification;
use App\Notifications\ExchangeCompletedNotification;
use App\Notifications\ExchangeCancelledNotification; // Add this import

class ExchangeController extends Controller
{
    public function __construct()
    {
        // Share pending exchanges count with all views
        $this->sharePendingExchangesCount();
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'type' => 'required|in:free,paid,barter',
            'offer_price' => 'nullable|numeric|min:0',
            'contact_info' => 'required|string|max:255'
        ]);

        // ... existing validation ...

        $exchange = Exchange::create([
            'product_id' => $product->id,
            'from_user_id' => Auth::id(),
            'to_user_id' => $product->user_id,
            'type' => $request->type,
            'agreed_price' => $request->offer_price,
            'message' => $request->message,
            'contact_info' => $request->contact_info,
            'status' => 'pending',
            'exchange_date' => now()->addHours(24),
        ]);

        // Send notification to product owner
        $productOwner = User::find($product->user_id);
        $productOwner->notify(new ExchangeRequestNotification($exchange));

        // Send browser notification if enabled
        $this->sendBrowserNotification($productOwner, $exchange);

        return redirect()->route('exchanges.my')
            ->with('success', 'Exchange request sent successfully! The seller has 24 hours to respond.');
    }

    private function sendBrowserNotification($user, $exchange)
    {
        // This would typically be handled via WebSockets in a real app
        // For now, we'll rely on the frontend polling
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

        // Send notification to requester
        $requester = User::find($exchange->from_user_id);
        $requester->notify(new ExchangeAcceptedNotification($exchange));

        // Trigger real-time update for badge (count decreases)
        $this->broadcastExchangeCountUpdate(Auth::id());

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

        // Send notifications to both users
        $exchange->fromUser->notify(new ExchangeCompletedNotification($exchange, 'requester'));
        $exchange->toUser->notify(new ExchangeCompletedNotification($exchange, 'provider'));

        return back()->with('success', 'Exchange marked as completed! Thank you for using NeighborShare.');
    }

    public function cancel(Exchange $exchange)
    {
        if (!in_array(Auth::id(), [$exchange->from_user_id, $exchange->to_user_id])) {
            return back()->with('error', 'Unauthorized action.');
        }

        $previousStatus = $exchange->status;
        $cancelledBy = Auth::id() === $exchange->from_user_id ? 'requester' : 'provider';

        $exchange->update(['status' => 'cancelled']);

        // If the exchange was accepted, make the product available again
        if ($previousStatus === 'accepted') {
            $exchange->product->update(['is_available' => true]);
        }

        // Send notification to the other user
        $otherUserId = Auth::id() === $exchange->from_user_id ? $exchange->to_user_id : $exchange->from_user_id;
        $otherUser = User::find($otherUserId);
        $otherUser->notify(new ExchangeCancelledNotification($exchange, $cancelledBy));

        // Trigger real-time update if it was a pending exchange being cancelled by receiver
        if ($previousStatus === 'pending' && $cancelledBy === 'provider') {
            $this->broadcastExchangeCountUpdate($otherUserId);
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

        // Mark notifications as read when viewing exchanges
        Auth::user()->unreadNotifications()
            ->whereIn('type', [
                'App\Notifications\ExchangeRequestNotification',
                'App\Notifications\ExchangeAcceptedNotification',
                'App\Notifications\ExchangeCompletedNotification',
                'App\Notifications\ExchangeCancelledNotification'
            ])
            ->update(['read_at' => now()]);

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

    /**
     * API endpoint to get current pending exchanges count
     */
    // public function getExchangesCount()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['count' => 0]);
    //     }

    //     $count = $this->getPendingExchangesCount();

    //     return response()->json([
    //         'count' => $count,
    //         'success' => true
    //     ]);
    // }

    /**
     * Get pending exchanges count for the authenticated user
     */
    private function getPendingExchangesCount()
    {
        if (Auth::check()) {
            return Exchange::where('to_user_id', Auth::id())
                ->where('status', 'pending')
                ->count();
        }

        return 0;
    }

    /**
     * Share pending exchanges count with all views
     */
    private function sharePendingExchangesCount()
    {
        view()->share('pendingExchangesCount', $this->getPendingExchangesCount());
    }

    /**
     * Broadcast exchange count update (for real-time functionality)
     */
    private function broadcastExchangeCountUpdate($userId)
    {
        // If using Laravel Echo/WebSockets, you would trigger an event here
        // For now, we'll rely on the frontend polling

        // Example WebSocket implementation (uncomment if you have Echo setup):
        /*
        event(new \App\Events\ExchangeCountUpdated(
            $userId,
            $this->getPendingExchangesCountForUser($userId)
        ));
        */
    }

    /**
     * Get pending exchanges count for specific user
     */
    private function getPendingExchangesCountForUser($userId)
    {
        return Exchange::where('to_user_id', $userId)
            ->where('status', 'pending')
            ->count();
    }

    // In ExchangeController
    public function getExchangesCount()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }

        $count = Exchange::where('to_user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'count' => $count,
            'success' => true
        ]);
    }
}
