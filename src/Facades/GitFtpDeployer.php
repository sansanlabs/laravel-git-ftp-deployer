<?php

namespace SanSanLabs\GitFtpDeployer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SanSanLabs\GitFtpDeployer\GitFtpDeployer
 */
class GitFtpDeployer extends Facade {
  protected static function getFacadeAccessor() {
    return \Sansanlabs\GitFtpDeployer\GitFtpDeployer::class;
  }
}
