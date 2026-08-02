# Managing

Managing is the content-management (CMS) surface of the Smart Responsor platform. Built on top of EasyAdmin, it defines the base layout, field visibility profiles, permissions, and CRUD behaviors for business-focused content resources.

This bundle is **not** a system diagnostics viewer or runtime console logs portal. It is strictly dedicated to managing domain content entities (like Products, Carts, and Users).

## Current Posture

### What the component already does
- Provides `AbstractManageContentCrudController` to unify and standardize EasyAdmin screens.
- Filters out system metadata (e.g. component keys, provider names) from user views.
- Enforces custom field visibility profiles (e.g. read/write permissions based on the active manager context).
- Standardizes common row/batch actions (publish, unpublish, delete).
- Leverages Symfony UX Live Component and Turbo for snappy admin dashboards.

### What this repository does not claim yet
- Database migrations monitoring or runtime health checks.
- Direct API server testing interfaces.

## Runtime Surface & Entrypoints

The admin panel routes are centralized under:
- `/manage` - General CMS entry point.
- `src/Controller/` - CMS controllers and entity CRUD configuration.
- `src/Service/` - Field visibility profiles and access-visibility checkers.
- `App\Managing\ManagingBundle` - Bundle loader.

## Local Setup

Install dependencies:
```bash
composer install
```

Run tests:
```bash
vendor/bin/phpunit
```

## Local Composer Path Installation

To import Managing into your Symfony host application:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../Managing",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "managing/manage": "*@dev"
  }
}
```

## Documentation Map

- [Content CRUD Baseline Overview](docs/manage-content-crud.adoc)
- [Field Access & Visibility Readiness](docs/manage-field-access-visibility-readiness.adoc)
- [External Rolling Access Adapter](docs/manage-field-external-rolling-access.adoc)
- [Field View Profile Storage Enablement](docs/manage-field-view-profile-storage.adoc)
- [Field View Profile Host Activation Checklist](docs/manage-field-view-profile-host-activation.adoc)
- [Field Visibility Inspection](docs/manage-field-visibility-inspection.adoc)
- [Service Layer Cleanup](docs/manage-final-service-layer-cleanup.adoc)
