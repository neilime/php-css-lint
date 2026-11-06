# Development

## Setup

Local builds select the highest stable PHP release allowed by `composer.json` using the
shared Hoverkraft resolver. Docker is the only runtime required on the host. The
resolver revision comes from the existing `ci-github-php` workflow pins, so dependency
updates also apply to local builds. A checkout with consistent workflow pins is required
for automatic selection. Override the selection with `make PHP_VERSION=8.2 setup`; use
the same override for later commands. Without an override, commands use the local `dev`
image created by setup. Rebuild it to pick up newly available compatible releases. CI
automatically tests every compatible stable minor.

```sh
make setup
```

## Running tests

```sh
make test
```

## Fix code linting

```sh
make lint-fix
```

## Running CI scripts

```sh
make ci
```

## CSS reference data

Regenerate the checked-in CSS references from W3C and MDN data with:

```sh
composer generate-css-referentials
```

MDN property aliases are included when at least one browser reports support without a removal version. Aliases retain their own classification when available; otherwise, they enter the non-standard compatibility list. Property value aliases are not imported as property names.

Maintain manual non-standard property and at-rule additions in [`css-referential-overrides.php`](../css-referential-overrides.php) at the repository root. The generator rejects entries already supplied by W3C or MDN, so remove an override when it becomes available upstream.
