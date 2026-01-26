<x-app-layout>
<div class="container">
    <h1 class="mb-4">Új felhasználó létrehozása</h1>

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

    {{-- létrehozó űrlap --}}
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Név</label>
            <input
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Jelszó</label>
            <input
                type="password"
                name="password"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Jelszó megerősítése</label>
            <input
                type="password"
                name="password_confirmation"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3 form-check">
            <input
                type="checkbox"
                name="is_admin"
                class="form-check-input"
                id="is_admin"
                value="1"
                {{ old('is_admin') ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_admin">
                Admin jogosultság
            </label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                ➕ Létrehozás
            </button>

            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                ↩️ Vissza
            </a>
        </div>
    </form>
</div>
</x-app-layout>
