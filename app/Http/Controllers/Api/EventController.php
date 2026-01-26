<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Ellenőrzi, hogy az aktuális user admin-e
     */
    protected function checkAdmin(Request $request)
    {
        return $request->user()->is_admin ?? false;
    }

    /**
     * Listázás: user = saját események, admin = minden esemény
     */
    public function index(Request $request)
    {
        $query = $this->checkAdmin($request) ? Event::query() : $request->user()->events();
        $events = $query->withPivot('status', 'registered_at')->get();
        return response()->json($events);
    }

    /**
     * Jövőbeli események
     */
    public function upcoming(Request $request)
    {
        $query = $this->checkAdmin($request) ? Event::query() : $request->user()->events();
        $events = $query->where('date', '>=', now())->withPivot('status', 'registered_at')->get();
        return response()->json($events);
    }

    /**
     * Elmúlt események
     */
    public function past(Request $request)
    {
        $query = $this->checkAdmin($request) ? Event::query() : $request->user()->events();
        $events = $query->where('date', '<', now())->withPivot('status', 'registered_at')->get();
        return response()->json($events);
    }

    /**
     * Filter: státusz és dátum szerint
     * Query param-ok: status=pending/approved/rejected, from=YYYY-MM-DD, to=YYYY-MM-DD
     */
    public function filter(Request $request)
    {
        $query = $this->checkAdmin($request) ? Event::query() : $request->user()->events();

        if ($request->has('status')) {
            $status = $request->status;
            $query->whereHas('users', fn($q) => $q->wherePivot('status', $status));
        }
        if ($request->has('from')) {
            $query->where('date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('date', '<=', $request->to);
        }

        return response()->json($query->withPivot('status', 'registered_at')->get());
    }

    /**
     * Admin: új esemény létrehozása
     */
    public function store(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'required|string',
            'max_attendees' => 'required|integer|min:1',
        ]);

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    /**
     * Admin: esemény módosítása
     */
    public function update(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'date' => 'sometimes|required|date',
            'location' => 'sometimes|required|string',
            'max_attendees' => 'sometimes|required|integer|min:1',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Admin: esemény törlése
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);
        $event->delete(); // soft delete az Event modellen

        return response()->json(['message' => 'Event deleted']);
    }
}