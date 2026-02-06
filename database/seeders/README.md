# Database Seeders

This directory contains seeders for populating the database with initial and test data.

## Seeder Structure

### 1. **CurrencySeeder** (Essential Data)
- Seeds all supported currencies (20 currencies including major global and African currencies)
- **Runs in:** Local + Production
- **When:** Always on `php artisan db:seed`

### 2. **DevelopmentSeeder** (Development Data)
- Creates a demo user, organization, clients, invoices, payments
- **Runs in:** Local only
- **When:** Automatically on `php artisan db:seed` in local environment
- **Demo credentials:** `demo@claude-voice.com` / `password`

### 3. **ProductionSeeder** (Production Setup)
- Interactive seeder for creating the first admin user and organization
- **Runs in:** Production only
- **When:** Manually via `php artisan db:seed --class=ProductionSeeder`
- **Note:** Will skip if users already exist

## Usage

### Local Development

```bash
# Fresh database with all development data
php artisan migrate:fresh --seed

# Or run development seeder separately
php artisan db:seed --class=DevelopmentSeeder
```

### Production

```bash
# Run migrations
php artisan migrate

# Seed currencies
php artisan db:seed --class=CurrencySeeder

# Create first admin user and organization (interactive)
php artisan db:seed --class=ProductionSeeder
```

## What Gets Seeded

### Local Environment
- ✅ 20 currencies (USD, EUR, GBP, ZAR, KES, NGN, etc.)
- ✅ 1 demo user (`demo@claude-voice.com` / `password`)
- ✅ 1 demo organization
- ✅ 15 clients
- ✅ ~35 invoices (draft, sent, paid, overdue)
- ✅ Multiple invoice items per invoice
- ✅ Payments for paid invoices
- ✅ Invoices in different currencies (ZAR, KES, NGN)

### Production Environment
- ✅ 20 currencies
- ✅ Interactive setup for admin user and organization

## Adding New Seeders

1. Create the seeder: `php artisan make:seeder YourSeeder`
2. Add to `DatabaseSeeder::run()` in the appropriate environment block
3. Document it in this README
