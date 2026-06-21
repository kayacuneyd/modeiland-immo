# CekirdekCMS Agent Start

Read this first, then open detailed docs only when needed.

## Stack

- PHP 8.2+, CodeIgniter 4, SQLite
- Tailwind CSS v3 + DaisyUI v4
- Admin auth/RBAC, settings, media and setup live in `app/Core/`
- Project features live in `app/Modules/`

## Rules

- New site/app work should stay under `app/Modules/`.
- Every module has `module.json`.
- Do not edit central route, autoload, composer or admin layout files for a normal new module.
- Admin POST forms must include `csrf_field()`.
- Models extend `App\Core\Models\BaseModel`.
- Admin controllers extend `App\Core\Controllers\BaseAdminController`.
- Web controllers extend `App\Core\Controllers\BaseWebController`.

## Module Contract

```text
app/Modules/MyModule/
|-- module.json
|-- Config/Routes.php
|-- Controllers/
|-- Models/
|-- Database/Migrations/
`-- Views/
```

`module.json` controls route discovery and admin menu discovery. Use `routePriority: 990` only for catch-all routes like Pages.

## Verify

```bash
composer validate --strict
composer test
php spark migrate:status --all
```
