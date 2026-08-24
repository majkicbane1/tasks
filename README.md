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

## Demo nalozi

- Super admin: `admin@uizzard.rs` / `password123`
- Klijent: `klijent@example.com` / `password123`

Super admin upravlja klijentima, projektima, poslovima i uplatama. Klijent vidi samo svoje projekte, radne stavke koje su označene kao vidljive i uplate/troškove koji su označeni kao vidljivi.

## Live migracije

Kada si ulogovan kao super admin, otvori:

```text
/migration
```

Ruta pokreće samo `php artisan migrate --force`. Ako nisi super admin, pristup nije dozvoljen.
