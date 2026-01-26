<x-app-layout>
<div class="container">
    <h1 class="mb-4">Esemény szerkesztése</h1>

    {{-- validációs hibák --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- szerkesztő űrlap --}}
    <form action="{{ route('admin.events.update', $event) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Esemény címe</label>
            <input
                type="text"
                name="title"
                class="form-control"
                value="{{ old('title', $event->title) }}"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Dátum</label>
            <input
                type="datetime-local"
                name="date"
                class="form-control"
                value="{{ old('date', $event->date->format('Y-m-d\TH:i')) }}"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Helyszín</label>
            <input
                type="text"
                name="location"
                class="form-control"
                value="{{ old('location', $event->location) }}"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Leírás</label>
            <textarea
                name="description"
                class="form-control"
                rows="4"
            >{{ old('description', $event->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Maximális résztvevők száma</label>
            <input
                type="number"
                name="max_attendees"
                class="form-control"
                value="{{ old('max_attendees', $event->max_attendees) }}"
                min="0"
            >
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                💾 Mentés
            </button>

            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                ↩️ Vissza
            </a>
        </div>
    </form>
</div>
</x-app-layout>