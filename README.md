# Uizzard Poslovi

Laravel admin panel za evidenciju klijenata, projekata, radnih stavki, uplata i preostalog iznosa.

## Pokretanje

```bash
composer install
npm install
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Na Windows PowerShell-u, ako je `npm` blokiran:

```bash
npm.cmd install
npm.cmd run build
```

## Uloge i firme

- **Platform admin** (`platform_admin`) - vlasnik platforme. Upravlja firmama (freelancerima), podešavanjima mejla, Google ključevima, pravilima registracije i migracijama (`/platform`).
- **Admin firme** (`super_admin`) - freelancer koji upravlja svojim okruženjem: klijentima, projektima, poslovima, uplatama i odmorom. Svaka firma vidi samo svoje podatke.
- **Klijent** (`client`) - vidi samo svoje projekte i stavke označene kao vidljive.

Postojeći podaci pri migraciji postaju firma **Uizzard**.

### Prvi platform admin

```bash
php artisan platform:admin ti@domen.rs --name="Ime"
```

Ili pre prve migracije podesi `PLATFORM_ADMIN_EMAIL` i `PLATFORM_ADMIN_PASSWORD` u `.env`.

### Mejl, Google, 2FA

- Mejl (SMTP), Google Client ID/Secret i registracija/potvrda emaila podešavaju se u `/platform/settings` (tajne se čuvaju šifrovane).
- Google redirect URI: `{APP_URL}/auth/google/callback`.
- 2FA (Google Authenticator) svako uključuje u **Moj nalog**; uz kod važe i rezervni kodovi.

## Demo nalozi (`migrate:fresh --seed`)

- Platform admin: `platform@uizzard.rs` / `password123`
- Admin firme Uizzard: `admin@uizzard.rs` / `password123`
- Klijent: `klijent@example.com` / `password123`

## Live migracije

Kao platform admin otvori **Održavanje** (`/platform/maintenance`) i pokreni migracije.
