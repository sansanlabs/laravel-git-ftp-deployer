<?php

namespace Sansanlabs\GitFtpDeployer\Services;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GitFtpDeployService {
  public function validateEnvironment(string $environment): bool {
    $environments = config("git-ftp-deployer.environments");

    if (!isset($environments[$environment])) {
      return false;
    }

    $config = $environments[$environment];

    return !empty($config["host"]) && !empty($config["username"]) && !empty($config["password"]);
  }

  public function deploy(string $environment, Command $command): bool {
    $config = config("git-ftp-deployer.environments.{$environment}");

    $command->info("Deploying to {$environment} environment...");
    $command->line("• Host: <fg=cyan>{$config["host"]}</>");
    $command->line("• Path: <fg=cyan>{$config["path"]}</>");
    $command->line("");

    $gitFtpCommand = $this->buildGitFtpCommand($config);
    $process = $this->createFtpProcess($gitFtpCommand);

    $process->run(function ($type, $buffer): void {
      echo $buffer;
    });

    if (!$process->isSuccessful()) {
      $command->error("Git-FTP deployment failed.");
      $command->line("");
      $command->line("Common issues:");
      $command->line("• Make sure git-ftp is installed on your system");
      $command->line("• Verify your FTP credentials are correct");
      $command->line("• Check if the remote path exists and is writable");
      $command->line("• Ensure your git repository has at least one commit");

      return false;
    }

    $command->info("Git-FTP deployment completed successfully");

    return true;
  }

  protected function buildGitFtpCommand(array $config): string {
    $options = config("git-ftp-deployer.git_ftp_options");
    $host = $config["host"];
    $username = $config["username"];
    $password = $config["password"];
    $path = $config["path"] ?? "/website/";

    $command = "git-ftp push";

    if ($options["force"] ?? false) {
      $command .= " --force";
    }

    if ($options["verbose"] ?? false) {
      $command .= " --verbose";
    }

    if ($options["auto_init"] ?? false) {
      $command .= " --auto-init";
    }

    $command .= " --user \"$username\"";
    $command .= " --passwd \"$password\"";
    $command .= " --syncroot . $host$path";

    return $command;
  }

  protected function createFtpProcess(string $gitFtpCommand): Process {
    $gitBashPath = config("git-ftp-deployer.git_bash_path");
    $gitBashPath = '"' . $gitBashPath . '"';

    return Process::fromShellCommandline("{$gitBashPath} -c '$gitFtpCommand'");
  }
}
