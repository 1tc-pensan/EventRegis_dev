<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;

class RegistrationController extends Controller
{
    /**
     * Admin check
     */
    protected function checkAdmin(Request $request)
    {
        return $request->user()->is_admin ?? false;
    }

    /**
     * User regisztrál az eseményre
     */
    public function register(Request $request, Event $event)
    {
        $user = $request->user();

        // Már regisztrált?
        if ($event->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Már regisztráltál erre az eseményre.'], 400);
        }

        $event->users()->attach($user->id, [
            'status' => 'pending',
            'registered_at' => now(),
        ]);

        return response()->json(['message' => 'Sikeres regisztráció']);
    }

    /**
     * User törli a saját regisztrációját (soft delete)
     */
    public function unregister(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$event->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Nincs regisztrációd erre az eseményre'], 404);
        }

        $event->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

        return response()->json(['message' => 'Regisztráció törölve']);
    }

    /**
     * Admin törli bármelyik felhasználót az eseményről (soft delete)
     */
    public function adminRemoveUser(Request $request, Event $event, User $user)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$event->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Ez a felhasználó nincs regisztrálva erre az eseményre'], 404);
        }

        $event->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

        return response()->json(['message' => 'Felhasználó eltávolítva az eseményről']);
    }
}