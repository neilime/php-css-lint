# Development

## Setup

_You can override default PHP version by setting `PHP_VERSION` as Makefile argument. Example: `make PHP_VERSION=8.2 setup`._

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
