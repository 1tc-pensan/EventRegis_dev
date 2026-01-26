# User Tábla - Módosítás Védelem (Mass Assignment Protection)

## Bevezetés

A Laravel keretrendszerben a **Mass Assignment Protection** (tömeges hozzárendelés védelme) egy biztonsági mechanizmus, amely megakadályozza, hogy rosszindulatú felhasználók nem kívánt adatbázis mezőket módosítsanak.

## User Modell Struktúra

### Adatbázis Séma

A `users` tábla az alábbi mezőket tartalmazza:

```sql
- id (Primary Key)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- phone (string, nullable)
- is_admin (boolean, default: false)
- password (string)
- remember_token (string)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable - SoftDeletes)
```

## Védett Attribútumok

### Fillable (Tömeges Módosításra Engedélyezett)

A User modellben az alábbi mezők vannak **explicit engedélyezve** a tömeges hozzárendeléshez:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'is_admin',
];
```

#### Engedélyezett Műveletek

Ezeket a mezőket használhatjuk biztonságosan `create()` és `update()` metódusokban:

```php
// Példa - Új felhasználó létrehozása
User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('secret'),
    'phone' => '+36301234567',
    'is_admin' => false,
]);

// Példa - Felhasználó frissítése
$user->update([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'phone' => '+36309876543',
]);
```

### Hidden (Rejtett Attribútumok)

Az alábbi mezők rejtettek a JSON/array konverzióknál (API válaszoknál):

```php
protected $hidden = [
    'password',
    'remember_token',
];
```

Ez biztosítja, hogy érzékeny információk (jelszó, emlékezz rám token) soha ne kerüljenek ki az API válaszokban.

## Védelem Működése

### Mit Véd?

A mass assignment protection **megakadályozza** a következő mezők jogosulatlan módosítását:

- ❌ `id` - Elsődleges kulcs nem módosítható
- ❌ `email_verified_at` - Csak email verifikációs folyamatokon keresztül
- ❌ `remember_token` - Laravel által automatikusan kezelt
- ❌ `created_at` - Automatikusan generált
- ❌ `updated_at` - Automatikusan frissül
- ❌ `deleted_at` - Csak SoftDeletes trait-en keresztül

### Példa Támadási Szcenárió (VÉDVE)

**Rosszindulatú kísérlet:**

```php
// Támadó megpróbálja admin jogosultságot adni magának
$request->all(); // ['name' => 'Hacker', 'email' => 'hack@evil.com', 'is_admin' => true, 'id' => 1]

User::create($request->all());
```

**Védelem:**
- Az `is_admin` mező **fillable**, így figyelmesen kell kezelni
- Soha ne használjunk `$request->all()` közvetlenül!
- Használjunk **Form Request** validációt

### Biztonságos Gyakorlat

```php
// Biztonságos - Explicit mezők
User::create([
    'name' => $request->validated('name'),
    'email' => $request->validated('email'),
    'password' => Hash::make($request->validated('password')),
    'phone' => $request->validated('phone'),
    'is_admin' => false, // Mindig explicit false új regisztrációnál
]);

// Admin jogosultság beállítása csak külön logikával
if (auth()->user()->is_admin) {
    $user->is_admin = true;
    $user->save();
}
```

## Castolások

A User modell automatikusan castol bizonyos mezőket:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

- **email_verified_at**: Automatikusan `Carbon` datetime objektumra konvertálódik
- **password**: Automatikusan hash-elődik mentéskor

## Kapcsolatok

### Registrations (Egy-Sok)

```php
$user->registrations(); // Egy felhasználó több regisztrációval rendelkezhet
```

### Events (Sok-Sok)

```php
$user->events(); // Pivot tábla: registrations
```

## Biztonsági Ajánlások

1. ✅ **Soha ne használd** `$guarded = []` vagy `protected $guarded = ['*']` nélkül megfelelő validációt
2. ✅ **Mindig használj** Form Request osztályokat validációra
3. ✅ **is_admin** mezőt külön logikával kezeld, ne `fillable`-en keresztül
4. ✅ **Hash-eld** a jelszavakat mentés előtt
5. ✅ **Validálj** minden bemenetet
6. ✅ **Használd** a `$hidden` attribútumot érzékeny mezőkhöz

## Összefoglalás

A User modell védelme három szinten működik:

1. **Fillable védelem**: Csak meghatározott mezők módosíthatók tömeges hozzárendeléssel
2. **Hidden védelem**: Érzékeny adatok nem kerülnek ki JSON-ban
3. **Castolás**: Automatikus típuskonverziók és hash-elés

Ez a többrétegű védelem biztosítja az alkalmazás biztonságát és adatintegritását.
