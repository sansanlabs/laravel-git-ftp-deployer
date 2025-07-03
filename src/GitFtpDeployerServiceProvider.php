<?php

namespace SanSanLabs\GitFtpDeployer;

use SanSanLabs\GitFtpDeployer\Commands\GitFtpDeployerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GitFtpDeployerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */

        $package->name('laravel-git-ftp-deployer')->hasConfigFile()->hasCommand(GitFtpDeployerCommand::class);
    }
}
