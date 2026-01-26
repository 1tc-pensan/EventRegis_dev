<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'max_attendees' => 'nullable|integer|min:0',
        ]);

        Event::create($request->all());
        return redirect()->route('admin.events.index')->with('success','Event created.');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'max_attendees' => 'nullable|integer|min:0',
        ]);

        $event->update($request->all());
        return redirect()->route('admin.events.index')->with('success','Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success','Event deleted.');
    }
}