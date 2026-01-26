<x-app-layout>
<div class="container">
    
    @if(auth()->user()->is_admin)
        <h1 class="mb-4">Események kezelése</h1>
    @else
        <h1 class="mb-4">Események</h1>
    @endif


    {{-- sikerüzenet --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- új esemény gomb csak adminnak --}}
    @if(auth()->user()->is_admin)
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary mb-3">
            ➕ Új esemény
        </a>
    @endif

    {{-- esemény lista --}}
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cím</th>
                <th>Dátum</th>
                <th>Helyszín</th>
                <th>Leírás</th>
                <th>Max. résztvevők</th>
                @if(auth()->user()->is_admin)
                    <th style="width: 200px">Műveletek</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->title }}</td>
                <td>{{ $event->date }}</td>
                <td>{{ $event->location }}</td>
                <td>{{ $event->description }}</td>
                <td>{{ $event->max_attendees }}</td>
                {{-- szerkesztés / törlés gombok csak adminnak --}}
                @if(auth()->user()->is_admin)
                    <td>
                        <a href="{{ route('admin.events.edit', $event) }}"
                           class="btn btn-sm btn-warning">
                            ✏️ Szerkesztés
                        </a>

                        <form action="{{ route('admin.events.destroy', $event) }}"
                              method="POST"
                              style="display:inline-block"
                              onsubmit="return confirm('Biztosan törlöd?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                🗑️ Törlés
                            </button>
                        </form>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">
                    Nincs még esemény az adatbázisban.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>