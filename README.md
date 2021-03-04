<p align="center">
  <a href="https://github.com/neilime/easy-win-setup" target="_blank"><img src="https://repository-images.githubusercontent.com/79255687/759bde80-eaaa-11e9-8919-6a8ad3b4a34d" width="600"></a>
</p>

[![Continuous integration](https://github.com/neilime/php-css-lint/workflows/Continuous%20integration/badge.svg)](https://github.com/neilime/php-css-lint/actions?query=workflow%3A%22Continuous+integration%22)
[![Coverage Status](https://codecov.io/gh/neilime/php-css-lint/branch/master/graph/badge.svg)](https://codecov.io/gh/neilime/php-css-lint)
[![Latest Stable Version](https://poser.pugx.org/neilime/php-css-lint/v/stable)](https://packagist.org/packages/neilime/php-css-lint)
[![Total Downloads](https://poser.pugx.org/neilime/php-css-lint/downloads)](https://packagist.org/packages/neilime/php-css-lint)
[![License](https://poser.pugx.org/neilime/php-css-lint/license)](https://packagist.org/packages/neilime/php-css-lint)
[![Sponsor](https://img.shields.io/badge/%E2%9D%A4-Sponsor-ff69b4)](https://github.com/sponsors/neilime)

📢 **Php CSS Lint** is a php script that lint css files and strings:

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

❤️ If this project helps you reduce time to develop and/or you want to help the maintainer of this project. You can [sponsor](https://github.com/sponsors/neilime) him. Thank you !

# Contributing

👍 If you wish to contribute to this project, please read the [CONTRIBUTING.md](CONTRIBUTING.md) file. Note: If you want to contribute don't hesitate, I'll review any PR.

# Documentation

1. [Installation](https://github.com/neilime/php-css-lint/wiki/Installation)
2. [Usage](https://github.com/neilime/php-css-lint/wiki/Usage)
3. [Code Coverage](https://codecov.io/gh/neilime/php-css-lint)
4. [PHP Doc](https://neilime.github.io/php-css-lint/phpdoc)

# Development

## Setup

```sh
docker build -t php-css-lint .
docker run --rm -it -v $(pwd):/app php-css-lint composer install
```

## Running tests

```sh
docker run --rm -it -v $(pwd):/app php-css-lint composer test
```

## Fix code linting

```sh
docker run --rm -it -v $(pwd):/app php-css-lint composer cbf
```

## Running CI scripts

```sh
docker run --rm -it -v $(pwd):/app php-css-lint composer ci
```
