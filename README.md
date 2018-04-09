# Php CSS Lint

[![Build Status](https://travis-ci.org/neilime/php-css-lint.svg?branch=master)](https://travis-ci.org/neilime/php-css-lint)
[![Latest Stable Version](https://poser.pugx.org/neilime/php-css-lint/v/stable.svg)](https://packagist.org/packages/neilime/php-css-lint)
[![Total Downloads](https://poser.pugx.org/neilime/php-css-lint/downloads.svg)](https://packagist.org/packages/neilime/php-css-lint)
[![Coverage Status](https://coveralls.io/repos/github/neilime/php-css-lint/badge.svg?branch=master)](https://coveralls.io/github/neilime/php-css-lint?branch=master)

_Php CSS Lint_ is a php script that lint css files and strings :

```
===========================================================

  ____  _              ____ ____ ____    _     _       _
 |  _ \| |__  _ __    / ___/ ___/ ___|  | |   (_)_ __ | |_
 | |_) | '_ \| '_ \  | |   \___ \___ \  | |   | | '_ \| __|
 |  __/| | | | |_) | | |___ ___) |__) | | |___| | | | | |_
 |_|   |_| |_| .__/   \____|____/____/  |_____|_|_| |_|\__|
             |_|

===========================================================

# Lint file "/path/to/css/file.css"...
 => File "/path/to/css/file.css" is not valid :

    - Unknown CSS property "bordr-top-style" (line: 8, char: 20)
    - Unexpected char ":" (line: 15, char: 5)
```

# Helping Project

If this project helps you reduce time to develop and/or you want to help the maintainer of this project, you can make a donation, thank you.

<a href='https://pledgie.com/campaigns/33252'><img alt='Click here to lend your support to: php-css-lint and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/33252.png?skin_name=chrome' border='0' ></a>

# Contributing

If you wish to contribute to this project, please read the [CONTRIBUTING.md](CONTRIBUTING.md) file.
NOTE : If you want to contribute don't hesitate, I'll review any PR.

# Requirements

Name | Version
-----|--------
[php](https://secure.php.net/) | >=5.3.3

# Installation

## Main Setup

### With composer (the faster way)

```bash
    $ php composer.phar install neilime/php-css-lint
```

### By cloning project (manual)

1. Clone this project into your `./vendor/` directory.

# Usage

## As a bin script

### Display man page

In a terminal, execute :

```bash
$ php vendor/bin/php-css-lint
```

Result :

```
===========================================================

  ____  _              ____ ____ ____    _     _       _
 |  _ \| |__  _ __    / ___/ ___/ ___|  | |   (_)_ __ | |_
 | |_) | '_ \| '_ \  | |   \___ \___ \  | |   | | '_ \| __|
 |  __/| | | | |_) | | |___ ___) |__) | | |___| | | | | |_
 |_|   |_| |_| .__/   \____|____/____/  |_____|_|_| |_|\__|
             |_|

===========================================================

Usage :
------------------------------------------------------------
Lint a CSS file :
bin/php-css-lint css_file_path_to_lint.css

Lint a CSS string :
scripts/php-css-lint ".test { color: red; }"
------------------------------------------------------------
```

### Lint a file

In a terminal, execute :

```bash
$ bin/php-css-lint /path/to/css/file.css
```

Result :

```
===========================================================

  ____  _              ____ ____ ____    _     _       _
 |  _ \| |__  _ __    / ___/ ___/ ___|  | |   (_)_ __ | |_
 | |_) | '_ \| '_ \  | |   \___ \___ \  | |   | | '_ \| __|
 |  __/| | | | |_) | | |___ ___) |__) | | |___| | | | | |_
 |_|   |_| |_| .__/   \____|____/____/  |_____|_|_| |_|\__|
             |_|

===========================================================

# Lint file "/path/to/css/file.css"...
 => File "/path/to/css/file.css" is not valid :

    - Unknown CSS property "bordr-top-style" (line: 8, char: 20)
    - Unexpected char ":" (line: 15, char: 5)
```

### Lint a css string

In a terminal, execute :

```bash
$ bin/php-css-lint ".test { color: red; fail }"
```

Result :

```
===========================================================

  ____  _              ____ ____ ____    _     _       _
 |  _ \| |__  _ __    / ___/ ___/ ___|  | |   (_)_ __ | |_
 | |_) | '_ \| '_ \  | |   \___ \___ \  | |   | | '_ \| __|
 |  __/| | | | |_) | | |___ ___) |__) | | |___| | | | | |_
 |_|   |_| |_| .__/   \____|____/____/  |_____|_|_| |_|\__|
             |_|

===========================================================

# Lint css string...
 => Css string is not valid :

    - Unexpected property name token "}" (line: 1, char: 26)
    - Unterminated "property name" (line: 1, char: 26)
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
