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

  php-css-lint [--options='{ }'] css_file_or_string_to_lint

Arguments:
----------

  --options
    Options (optional), must be a json object:
     * "allowedIndentationChars" => [" "] or ["\t"]: will override the current property
     * "constructors": { "property" => bool }: will merge with the current property
     * "standards": { "property" => bool }: will merge with the current property
     * "nonStandards": { "property" => bool }: will merge with the current property
    Example: --options='{ "constructors": {"o" : false}, "allowedIndentationChars": ["\t"] }'

  css_file_or_string_to_lint
    The CSS file path (absolute or relative) or a CSS string to be linted
    Example:
      ./path/to/css_file_path_to_lint.css
      ".test { color: red; }"

Examples:
---------

  Lint a CSS file:
    php-css-lint ./path/to/css_file_path_to_lint.css

  Lint a CSS string:
    php-css-lint ".test { color: red; }"

  Lint with only tabulation as indentation:
    php-css-lint --options='{ "allowedIndentationChars": ["\t"] }' ".test { color: red; }"
```

### Lint a file

In a terminal, execute:

```sh
php vendor/bin/php-css-lint /path/to/not_valid_file.css
```

Result:

```
# Lint CSS file "/path/to/not_valid_file.css"...
 => CSS file "/path/to/not_valid_file" is not valid:

    - Unknown CSS property "bordr-top-style" (line: 8, char: 20)
    - Unterminated "selector content" (line: 17, char: 0)
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

```php
$cssLinter = new \CssLint\Linter();
```

### Lint string

```php
if($cssLinter->lintString('
.button.drodown::after {
    display: block;
    width: 0;
}') === true){
   echo 'Valid!';
}
else {
     echo 'Not Valid :(';
     var_dump($cssLinter->getErrors());
}
```

### Lint file

```php
if($cssLinter->lintFile('path/to/css/file.css') === true){
   echo 'Valid!';
}
else {
     echo 'Not Valid :(';
     var_dump($cssLinter->getErrors());
}
```

## Customize linter properties

### Allowed indentation chars

By default indentation must be spaces, you can change it to accept another chars (tabulation by example)

```php
$cssLinter = new \CssLint\Linter();

// Set linter must accept only tabulation as indentation
$cssLinter->getCssLintProperties()->setAllowedIndentationChars(["\t"]);

$cssLinter->lintString('.button.dropdown::after {
' . "\t" . 'display: block;
}'); // true

$cssLinter->lintString('.button.dropdown::after {
  display: block;
}'); // false
```
