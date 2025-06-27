# Usage

## As a bin script

### Display man page

In a terminal, execute:

```sh
php vendor/bin/php-css-lint
```

Result:

```
===========================================================

  ____  _              ____ ____ ____    _     _       _
 |  _ \| |__  _ __    / ___/ ___/ ___|  | |   (_)_ __ | |_
 | |_) | '_ \| '_ \  | |   \___ \___ \  | |   | | '_ \| __|
 |  __/| | | | |_) | | |___ ___) |__) | | |___| | | | | |_
 |_|   |_| |_| .__/   \____|____/____/  |_____|_|_| |_|\__|
             |_|

===========================================================

Usage:
------

  php-css-lint [--options='{ }'] [--formatter=name] [--formatter=name:path] input_to_lint

Arguments:
----------

  --options
    Options (optional), must be a json object:
     * "allowedIndentationChars" => [" "] or ["\t"]: will override the current property
     * "constructors": { "property" => bool }: will merge with the current property
     * "standards": { "property" => bool }: will merge with the current property
     * "nonStandards": { "property" => bool }: will merge with the current property
    Example: --options='{ "constructors": {"o" : false}, "allowedIndentationChars": ["\t"] }'

  --formatter
    The formatter(s) to be used. Can be specified multiple times.
    Format: --formatter=name (output to stdout) or --formatter=name:path (output to file)
    If not specified, the default formatter will output to stdout.
    Available formatters: plain, gitlab-ci, github-actions
    Examples:
      output to stdout: --formatter=plain
      output to file: --formatter=plain:report.txt
      multiple outputs: --formatter=plain --formatter=gitlab-ci:report.json

  input_to_lint
    The CSS file path (absolute or relative)
    a glob pattern of file(s) to be linted
    or a CSS string to be linted
    Example:
      "./path/to/css_file_path_to_lint.css"
      "./path/to/css_file_path_to_lint/*.css"
      ".test { color: red; }"

Examples:
---------

  Lint a CSS file:
    php-css-lint "./path/to/css_file_path_to_lint.css"

  Lint a CSS string:
    php-css-lint ".test { color: red; }"

  Lint with only tabulation as indentation:
    php-css-lint --options='{ "allowedIndentationChars": ["\t"] }' ".test { color: red; }"

  Output to a file:
    php-css-lint --formatter=plain:output.txt ".test { color: red; }"

  Generate GitLab CI report:
    php-css-lint --formatter=gitlab-ci:report.json "./path/to/css_file.css"

  Multiple outputs (console and file):
    php-css-lint --formatter=plain --formatter=gitlab-ci:ci-report.json ".test { color: red; }"
```

### Lint a file

In a terminal, execute:

```sh
php vendor/bin/php-css-lint "/path/to/not_valid_file.css"
```

Result:

```
# Lint CSS file "/path/to/not_valid_file.css"...
 => CSS file "/path/to/not_valid_file" is not valid:

    - Unknown CSS property "bordr-top-style" (line: 8, char: 20)
    - Unterminated "selector content" (line: 17, char: 0)
```

### Lint file(s) matching a glob pattern

See <https://www.php.net/manual/en/function.glob.php> for supported patterns.

In a terminal, execute:

```sh
php vendor/bin/php-css-lint "/path/to/*.css"
```

Result:

```
# Lint CSS file "/path/to/not_valid_file.css"...
 => CSS file "/path/to/not_valid_file" is not valid:

    - Unknown CSS property "bordr-top-style" (line: 8, char: 20)
    - Unterminated "selector content" (line: 17, char: 0)

# Lint CSS file "/path/to/valid_file.css"...
 => CSS file "/path/to/valid_file" is valid
```

### Lint a css string

In a terminal, execute:

```sh
php vendor/bin/php-css-lint ".test { color: red; fail }"
```

Result:

```
# Lint CSS string...
 => CSS string is not valid:

    - Unexpected property name token "}" (line: 1, char: 26)
    - Unterminated "property name" (line: 1, char: 26)
```

## Customize linter properties

### Allowed indentation chars

By default indentation must be spaces, you can change it to accept another chars (tabulation by example)

```sh
php vendor/bin/php-css-lint --options='{"allowedIndentationChars": ["\t"]}' ".test { color: red; }"
```

## In a php script

### Composer autoloading

```php
// Composer autoloading
if (!file_exists($sComposerAutoloadPath = __DIR__ . '/vendor/autoload.php')) {
    throw new \RuntimeException('Composer autoload file "' . $sComposerAutoloadPath . '" does not exist');
}
if (false === (include $sComposerAutoloadPath)) {
    throw new \RuntimeException('An error occured while including composer autoload file "' . $sComposerAutoloadPath . '"');
}
```

### Initialize Css Linter

Linter is returning a [`Generator`](https://www.php.net/manual/en/class.generator.php) object for performance reasons, so you can iterate over it to get errors.
It is recommended to use a `foreach` loop to iterate over the errors. If no errors are found, the generator will be empty, and the CSS is valid.

It is possible to use `iterator_to_array()` to convert the generator to an array,
but it is counterproductive as it will parse the full content and load all errors in memory at once,
which is not recommended for large CSS files.

```php
$cssLinter = new \CssLint\Linter();
```

### Lint string

```php

/** @var Generator $errors **/
$errors = $cssLinter->lintString('.button.dropdown::after {
    display: block;
    width: 0;
}');

$hasError = $false;
foreach ($errors as $error) {
    $hasError = true;
    echo $error->__toString() . PHP_EOL;
}

if (!$hasError) {
    echo 'Valid!';
} else {
    echo 'Not Valid :(';
}
```

### Lint file

```php

/** @var Generator $errors **/
$errors = $cssLinter->lintFile('path/to/css/file.css');

$hasError = $false;
foreach ($errors as $error) {
    $hasError = true;
    echo $error->__toString() . PHP_EOL;
}

if (!$hasError) {
    echo 'Valid!';
} else {
    echo 'Not Valid :(';
}
```

## Customize linter properties

### Allowed indentation chars

By default indentation must be spaces, you can change it to accept another chars (tabulation by example)

```php
$cssLinter = new \CssLint\Linter();

// Set linter must accept only tabulation as indentation
$cssLinter->getCssLintProperties()->setAllowedIndentationChars(["\t"]);

$errors = $cssLinter->lintString('.button.dropdown::after {
' . "\t" . 'display: block;
}');

var_dump(iterator_to_array($errors)); // Empty array, no errors

$errors = $cssLinter->lintString('.button.dropdown::after {
  display: block;
}');

var_dump(iterator_to_array($errors)); // Array with errors
```
