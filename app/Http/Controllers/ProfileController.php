<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Update basic profile fields
        $user->fill($request->validated());

        // Update additional fields
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->neighborhood = $request->neighborhood;

        // Handle location data
        if ($request->has('latitude') && $request->has('longitude')) {
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
        } elseif ($request->address && empty($user->latitude)) {
            // Geocode address if no coordinates provided but address is given
            $coordinates = $this->locationService->getCoordinatesFromAddress($request->address);
            if ($coordinates) {
                $user->latitude = $coordinates['latitude'];
                $user->longitude = $coordinates['longitude'];

                // Update neighborhood if not set
                if (empty($user->neighborhood) && isset($coordinates['neighborhood'])) {
                    $user->neighborhood = $coordinates['neighborhood'];
                }
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update only location via AJAX
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = $request->user();
        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
        ]);
    }
}
