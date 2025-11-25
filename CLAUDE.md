# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package for integrating with DPD (a shipping/logistics provider). The package follows Spatie's Laravel package conventions and uses modern PHP 8.4+ features.

**Package namespace**: `SmartDato\Dpd`
**Package name**: `smart-dato/dpd-sdk`

## Development Commands

### Testing
```bash
# Run full test suite with Pest
composer test

# Run tests with coverage
composer test-coverage

# Run tests in CI mode (used by GitHub Actions)
vendor/bin/pest --ci
```

### Code Quality
```bash
# Run PHPStan static analysis (level 5)
composer analyse
# or directly:
vendor/bin/phpstan analyse

# Fix code style with Laravel Pint
composer format
# or directly:
vendor/bin/pint
```

### Installation
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

## Architecture

### Core Components

**Service Provider** (`src/DpdServiceProvider.php`)
- Extends `Spatie\LaravelPackageTools\PackageServiceProvider`
- Registers package configuration
- Package name: `dpd-sdk`

**Main Class** (`src/Dpd.php`)
- Core functionality class (currently minimal)
- Bound to Laravel's service container

**Facade** (`src/Facades/Dpd.php`)
- Laravel Facade for accessing `Dpd` class
- Registered in `composer.json` under `extra.laravel.aliases`

### Configuration
- Config file: `config/dpd-sdk.php` (currently empty)
- Published via: `php artisan vendor:publish --tag="dpd-sdk-config"`

### Testing Setup
- Uses **Pest PHP** as the testing framework
- Base test case: `tests/TestCase.php` extends `Orchestra\Testbench\TestCase`
- Test suite automatically loads `DpdServiceProvider`
- Database configured to use in-memory SQLite for testing
- Architecture tests defined in `tests/ArchTest.php`

## Laravel Version Support

Supports both Laravel 11 and Laravel 12:
- Laravel 11.* with Orchestra Testbench 9.*
- Laravel 12.* with Orchestra Testbench 10.*

PHP requirement: **^8.4** (strict requirement, no fallback to 8.3)

## CI/CD

GitHub Actions workflows:
1. **run-tests.yml** - Runs tests across matrix (PHP 8.3-8.4, Laravel 11-12, Ubuntu/Windows, prefer-lowest/prefer-stable)
2. **phpstan.yml** - Runs static analysis on PHP code changes
3. **fix-php-code-style-issues.yml** - Auto-fixes code style with Laravel Pint on push

## PHPStan Configuration

- Level: 5
- Paths analyzed: `src/`
- Baseline file: `phpstan-baseline.neon` (currently empty)
- Laravel-specific checks enabled:
  - `checkOctaneCompatibility: true`
  - `checkModelProperties: true`

## Package Structure

```
src/
├── DpdServiceProvider.php    # Service provider
├── Dpd.php                    # Main class
└── Facades/
    └── Dpd.php                # Facade

tests/
├── TestCase.php               # Base test case
├── Pest.php                   # Pest configuration
├── ArchTest.php               # Architecture tests
└── ExampleTest.php            # Example tests

config/
└── dpd-sdk.php                # Package configuration
```

## Dependencies

Key dependencies:
- `spatie/laravel-package-tools` - Package scaffolding
- `illuminate/contracts` - Laravel framework contracts

Dev dependencies:
- `pestphp/pest` - Testing framework
- `larastan/larastan` - PHPStan for Laravel
- `laravel/pint` - Code style fixer
- `orchestra/testbench` - Laravel package testing
