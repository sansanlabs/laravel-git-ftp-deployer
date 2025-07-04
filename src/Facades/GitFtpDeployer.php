<?php

namespace SanSanLabs\GitFtpDeployer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SanSanLabs\GitFtpDeployer\GitFtpDeployer
 */
class GitFtpDeployer extends Facade {
  protected static function getFacadeAccessor() {
    return \SanSanLabs\GitFtpDeployer\GitFtpDeployer::class;
  }
}
