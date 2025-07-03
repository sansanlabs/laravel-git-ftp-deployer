# Laravel Git-FTP Deployer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sansanlabs/laravel-git-ftp-deployer.svg?style=flat-square)](https://packagist.org/packages/sansanlabs/laravel-git-ftp-deployer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sansanlabs/laravel-git-ftp-deployer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sansanlabs/laravel-git-ftp-deployer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/sansanlabs/laravel-git-ftp-deployer/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/sansanlabs/laravel-git-ftp-deployer/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sansanlabs/laravel-git-ftp-deployer.svg?style=flat-square)](https://packagist.org/packages/sansanlabs/laravel-git-ftp-deployer)

A Laravel package for deploying applications via FTP with git status checking and build process integration.

## Features

- ✅ Check git status before deployment
- ✅ Prevent deployment with uncommitted changes
- ✅ Prevent deployment with unpushed commits
- ✅ Run build commands (npm, yarn, etc.) before deployment
- ✅ Support multiple environments (staging, production)
- ✅ Beautiful tree view for uncommitted changes
- ✅ Cross-platform support (Windows, Linux, macOS)
- ✅ Configurable git-ftp options

## Requirements

- PHP ^8.2
- Laravel ^10.0|^11.0|^12.0
- git-ftp installed on your system

## Installation

You can install the package via composer:

```bash
composer require sansanlabs/laravel-git-ftp-deployer
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="git-ftp-deployer-config"
```

This is the contents of the published config file:

```php
return [
  /*
    |--------------------------------------------------------------------------
    | Git-FTP Deploy Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Git-FTP deployment package
    |
    */
  "environments" => [
    "staging" => [
      "host" => env("STAGING_FTP_HOST"),
      "username" => env("STAGING_FTP_USERNAME"),
      "password" => env("STAGING_FTP_PASSWORD"),
      "path" => env("STAGING_FTP_PATH", "/website/"),
    ],
    "production" => [
      "host" => env("PRODUCTION_FTP_HOST"),
      "username" => env("PRODUCTION_FTP_USERNAME"),
      "password" => env("PRODUCTION_FTP_PASSWORD"),
      "path" => env("PRODUCTION_FTP_PATH", "/website/"),
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Build Command
    |--------------------------------------------------------------------------
    |
    | The command to run before deployment
    |
    */
  "build_command" => env("GIT_FTP_BUILD_COMMAND", "npm run build"),

  /*
    |--------------------------------------------------------------------------
    | Git Bash Path
    |--------------------------------------------------------------------------
    |
    | Path to Git Bash executable (default on Windows)
    |
    */
  "git_bash_path" => env("GIT_BASH_PATH", "C:\\Program Files\\Git\\bin\\bash.exe"),

  /*
    |--------------------------------------------------------------------------
    | Git-FTP Options
    |--------------------------------------------------------------------------
    |
    | Additional options for git-ftp command
    |
    */
  "git_ftp_options" => [
    "force" => env("GIT_FTP_FORCE", true),
    "verbose" => env("GIT_FTP_VERBOSE", true),
    "auto_init" => env("GIT_FTP_AUTO_INIT", true),
  ],
];
```

## Configuration

Add your FTP credentials to your .env file:

```php
# Staging Environment
STAGING_FTP_HOST=ftp.yoursite.com
STAGING_FTP_USERNAME=your-username
STAGING_FTP_PASSWORD=your-password
STAGING_FTP_PATH=/public_html/staging/

# Production Environment
PROD_FTP_HOST=ftp.yoursite.com
PROD_FTP_USERNAME=your-username
PROD_FTP_PASSWORD=your-password
PROD_FTP_PATH=/public_html/

# Build Command (optional)
FTP_BUILD_COMMAND="npm run build"

# Git Bash Path
GIT_BASH_PATH="C:\Program Files\Git\bin\bash.exe"
```

## Usage

### Basic Deployment

#### Deploy to staging:

```bash
php artisan deploy:ftp --env=staging
```

#### Deploy to production:

```bash
php artisan deploy:ftp --env=production
```

#### Interactive environment selection:

```bash
php artisan deploy:ftp
```

### Skip Build Process

#### Skip the build command:

```bash
php artisan deploy:ftp --skip-build
```

## Configuration Options

The package configuration file config/ftp-deploy.php allows you to customize:

- Environments: Define multiple deployment environments
- Build Command: Command to run before deployment (default: npm run build)
- Git Bash Path: Path to Git Bash executable (Windows only)
- Git FTP Options: Additional options for git-ftp command

## Git Status Checking

The package will check for:

1. Uncommitted Changes: Files that have been modified but not committed
2. Unpushed Commits: Commits that exist locally but haven't been pushed to origin

If either condition is found, deployment will be aborted with a detailed report.

## Build Process

Before deployment, the package will run your configured build command (default: npm run build). This ensures your assets are compiled and ready for production.

## FTP Deployment

The package uses git-ftp to efficiently deploy only changed files to your FTP server. It supports:

- Force push
- Verbose output
- Auto-initialization of git-ftp
- Custom sync roots and paths

## Testing

Upcoming ...

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Edi kurniawan](https://github.com/edikurniawan-dev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
