# HTTP Basis

[![License](https://poser.pugx.org/httpsoft/http-basis/license)](https://packagist.org/packages/httpsoft/http-basis)
[![Latest Stable Version](https://poser.pugx.org/httpsoft/http-basis/v)](https://packagist.org/packages/httpsoft/http-basis)
[![Total Downloads](https://poser.pugx.org/httpsoft/http-basis/downloads)](https://packagist.org/packages/httpsoft/http-basis)
[![GitHub Build Status](https://github.com/httpsoft/http-basis/workflows/build/badge.svg)](https://github.com/httpsoft/http-basis/actions)
[![GitHub Static Analysis Status](https://github.com/httpsoft/http-basis/workflows/static/badge.svg)](https://github.com/httpsoft/http-basis/actions)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/httpsoft/http-basis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-basis/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/httpsoft/http-basis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/httpsoft/http-basis/?branch=master)

This package is a simple and fast HTTP microframework implementing PHP standards recommendations.

* [PSR-3 Logger](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md).
* [PSR-7 HTTP Message](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md).
* [PSR-11 Container](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md).
* [PSR-12 Coding Style](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md).
* [PSR-15 HTTP Server](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md).
* [PSR-17 HTTP Factories](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-17-http-factory.md).

This package helps you quickly create simple but high-quality web applications and APIs.

## Documentation

* [In English language](https://httpsoft.org/docs/basis).
* [In Russian language](https://httpsoft.org/ru/docs/basis).

## Installation

This package requires PHP version 7.4 or later.

To create the project you can use a ready-made [application template](https://github.com/httpsoft/http-app):

```bash
composer create-project --prefer-dist httpsoft/http-app <app-dir>
```

or install a microframework:

```bash
composer require httpsoft/http-basis
```

and configure everything manually, see the [documentation](https://httpsoft.org/docs/basis) for more details.
