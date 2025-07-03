<?php

namespace SanSanLabs\GitFtpDeployer;

class GitFtpDeployer {
  public function version(): string {
    return "1.0.0";
  }

  public function getAvailableEnvironments(): array {
    return array_keys(config("git-ftp-deployer.environments", []));
  }

  public function getEnvironmentConfig(string $environment): ?array {
    return config("git-ftp-deployer.environments.{$environment}");
  }
}
