# Email Verification API Dokumentáció

## Áttekintés

Az email verification funkció lehetővé teszi a felhasználók számára, hogy megerősítsék az email címüket a regisztráció után. A rendszer egy aláírt linket küld, amely nem igényel bejelentkezést.

## Működés

### 1. Regisztráció

Amikor egy felhasználó regisztrál (`POST /api/register`):
- Létrejön egy új User rekord az adatbázisban
- Az `email_verified_at` mező `NULL` értéket kap
- A rendszer kiváltja a `Registered` eventet
- A rendszer automatikusan küld egy verification emailt

### 2. Verification Email

Az email egy aláírt (signed) URL-t tartalmaz:
```
GET /api/email/verify/{id}/{hash}?expires={timestamp}&signature={signature}
```

**Paraméterek:**
- `{id}` - A felhasználó ID-ja
- `{hash}` - SHA1 hash az email címből
- `expires` - Lejárati időbélyeg (query parameter)
- `signature` - Laravel signed URL aláírás (query parameter)

### 3. Email Verification Route

**Endpoint:** `GET /api/email/verify/{id}/{hash}`

**Middleware:** `signed` (ellenőrzi az URL aláírást és lejáratot)

**Folyamat:**
1. Megkeresi a felhasználót az ID alapján
2. Ellenőrzi a hash-t (SHA1 az email címből)
3. Ellenőrzi, hogy már nincs-e verifikálva
4. Beállítja az `email_verified_at` mezőt az aktuális időpontra
5. Kiváltja a `Verified` eventet
6. JSON választ ad vissza

**Válaszok:**

✅ **Sikeres verifikáció (200):**
```json
{
    "message": "Email verified successfully. You can now log in."
}
```

⚠️ **Már verifikált (200):**
```json
{
    "message": "Email already verified."
}
```

❌ **Érvénytelen link (403):**
```json
{
    "message": "Invalid verification link."
}
```

❌ **Felhasználó nem található (404):**
```json
{
    "message": "Not Found"
}
```

❌ **Érvénytelen vagy lejárt aláírás (403):**
```json
{
    "message": "Invalid signature."
}
```

## Implementáció részletei

### User Model

A `User` model implementálja a `MustVerifyEmail` interfészt:

```php
class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Route Definition

```php
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link.'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.'], 200);
    }

    if ($user->markEmailAsVerified()) {
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    return response()->json(['message' => 'Email verified successfully. You can now log in.'], 200);
})->middleware(['signed'])->name('verification.verify');
```

## Biztonsági megfontolások

1. **Signed URL:** A link aláírt, nem lehet módosítani a paramétereket
2. **Hash ellenőrzés:** A hash SHA1 értéke az email címnek, nem lehet hamisítani
3. **Timing-safe comparison:** `hash_equals()` függvény használata timing attack ellen
4. **Lejárat:** Az URL időkorláttal rendelkezik (alapértelmezetten 60 perc)
5. **Nincs authentikáció szükséges:** A felhasználó nem kell bejelentkezve legyen

## Hibaelhárítás

### "Call to a member function getKey() on null" hiba

**Ok:** A route `auth:sanctum` middleware-t használt, de a felhasználó nincs bejelentkezve.

**Megoldás:** A middleware eltávolítása és egyedi verification logika implementálása (jelenlegi verzió).

### Email nem frissül verified-re

**Ellenőrizendők:**
1. Az `email_verified_at` mező létezik-e a `users` táblában
2. A `User` model implementálja-e a `MustVerifyEmail` interfészt
3. A hash megfelelően generált-e
4. A `markEmailAsVerified()` metódus lefut-e

## Környezeti változók

Az `.env` fájlban szükséges beállítások:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Tesztelés

### Mailhog használata

A fejlesztési környezetben Mailhog-ot használunk:
- Web interface: http://localhost:8025
- SMTP port: 1025

### Manuális tesztelés

1. Regisztrálj egy új felhasználót
2. Nyisd meg a Mailhog interfészt
3. Kattints a verification linkre az emailben
4. Ellenőrizd a választ
5. Ellenőrizd az adatbázisban az `email_verified_at` mezőt

### API tesztelés Postman/Insomnia-val

```
POST http://localhost:8000/api/register
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Ezután kattints a linkre az emailben vagy másold be böngészőbe.

## Changelog

### 2026-02-02
- ✅ Eltávolítva az `auth:sanctum` middleware a verification route-ból
- ✅ Implementálva egyedi verification logika
- ✅ Javítva a "getKey() on null" hiba
- ✅ Hozzáadva megfelelő hash ellenőrzés
- ✅ Hozzáadva `Verified` event
