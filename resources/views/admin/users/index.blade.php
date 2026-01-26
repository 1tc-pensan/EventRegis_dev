<x-app-layout>
<div class="container">
    
    @if(auth()->user()->is_admin)
        <h1 class="mb-4">Felhasználók kezelése</h1>
    @else
        <h1 class="mb-4">Felhasználók</h1>
    @endif


    {{-- sikerüzenet --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- új felhasználó gomb csak adminnak --}}
    @if(auth()->user()->is_admin)
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-3">
            ➕ Új felhasználó
        </a>
    @endif

    {{-- felhasználó lista --}}
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Létrehozva</th>
                @if(auth()->user()->is_admin)
                    <th style="width: 200px">Műveletek</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->is_admin)
                        <span class="badge bg-success">Admin</span>
                    @else
                        <span class="badge bg-secondary">Felhasználó</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                {{-- szerkesztés / törlés gombok csak adminnak --}}
                @if(auth()->user()->is_admin)
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="btn btn-sm btn-warning">
                            ✏️ Szerkesztés
                        </a>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}"
                                  method="POST"
                                  style="display:inline-block"
                                  onsubmit="return confirm('Biztosan törlöd?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    🗑️ Törlés
                                </button>
                            </form>
                        @endif
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">
                    Nincs még felhasználó az adatbázisban.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>
