# PROJECT_SPEC

## Goal

CekirdekCMS is a reusable CodeIgniter 4 CMS core for building small and medium websites or web apps with low agent token cost.

## Operating Model

- Core services are stable and reusable.
- New project behavior should be implemented as modules.
- Module discovery is manifest based through `module.json`.
- Routes, admin navigation and CI4 module namespaces are discovered automatically.
- Setup must be safe to rerun on an existing project database.

## Non-Negotiables

- PHP requirement is PHP 8.2+.
- Admin and auth POST requests are CSRF protected.
- Local development must work without forced HTTPS.
- Seeders must not truncate roles or break user-role relationships.
- Media deletion must remove generated public files and soft-delete the DB record.

## First Files For Agents

1. `AGENTS.md`
2. `README.md`
3. `docs/ARCHITECTURE.md`
4. `docs/CONVENTIONS.md`
5. `docs/RECIPES.md`
