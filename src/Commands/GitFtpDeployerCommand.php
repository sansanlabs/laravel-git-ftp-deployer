<?php

namespace SanSanLabs\GitFtpDeployer\Commands;

use Illuminate\Console\Command;
use Sansanlabs\GitFtpDeployer\Services\GitFtpDeployService;
use Sansanlabs\GitFtpDeployer\Services\GitStatusService;
use Symfony\Component\Process\Process;

class GitFtpDeployerCommand extends Command {
  public $signature = "git-ftp:deploy {--env= : Environment to deploy to} {--skip-build : Skip build process} {--force : Force deployment even with uncommitted changes}";

  public $description = "Deploy application using git-ftp with safety checks";

  protected GitStatusService $gitStatusService;

  protected GitFtpDeployService $ftpDeployService;

  public function __construct(GitStatusService $gitStatusService, GitFtpDeployService $ftpDeployService) {
    parent::__construct();
    $this->gitStatusService = $gitStatusService;
    $this->ftpDeployService = $ftpDeployService;
  }

  public function handle(): int {
    $this->info("Starting Git-FTP deployment process...");

    // Check git status
    if (!$this->checkGitStatus()) {
      return Command::FAILURE;
    }

    // Check unpushed commits
    if (!$this->checkUnpushedCommits()) {
      return Command::FAILURE;
    }

    // Run build command
    if (!$this->option("skip-build") && !$this->runBuildCommand()) {
      return Command::FAILURE;
    }

    // Deploy via FTP
    if (!$this->deployViaFtp()) {
      return Command::FAILURE;
    }

    $this->info("Deployment completed successfully!");

    return Command::SUCCESS;
  }

  protected function checkGitStatus(): bool {
    $changes = $this->gitStatusService->getUncommittedChanges();

    if (empty($changes)) {
      $this->info("Git status is clean");

      return true;
    }

    $this->warn('There are changes that haven\'t been committed/pushed yet:');
    $this->gitStatusService->displayChangesTree($changes, $this);
    $this->error("Deployment aborted. Please commit and push your changes first.");
    $this->line("");
    $this->line("Use --force flag to deploy anyway (not recommended)");

    return false;
  }

  protected function checkUnpushedCommits(): bool {
    $unpushedCount = $this->gitStatusService->getUnpushedCommitsCount();

    if ($unpushedCount === 0) {
      $this->info("All commits are pushed to remote");

      return true;
    }

    $branch = $this->gitStatusService->getCurrentBranch();
    $this->warn("There are {$unpushedCount} commits that haven't been pushed to origin/{$branch}.");
    $this->error("Deployment aborted. Please push your changes first.");
    $this->line("");
    $this->line("Use --force flag to deploy anyway (not recommended)");

    return false;
  }

  protected function runBuildCommand(): bool {
    $buildCommand = config("git-ftp-deployer.build_command");
    $this->info("Running build command: {$buildCommand}");

    $process = Process::fromShellCommandline($buildCommand);
    $process->setTty(Process::isTtySupported());
    $process->run(function ($type, $buffer): void {
      echo $buffer;
    });

    if (!$process->isSuccessful()) {
      $this->error("Failed to run build command: {$buildCommand}");

      return false;
    }

    $this->info("Build completed successfully");

    return true;
  }

  protected function deployViaFtp(): bool {
    $environment = $this->getEnvironment();

    if (!$this->ftpDeployService->validateEnvironment($environment)) {
      $this->error("Invalid environment or missing FTP credentials for '{$environment}'.");
      $this->line("");
      $this->line("Please check your .env file and ensure the following variables are set:");
      $this->line("- " . strtoupper($environment) . "_FTP_HOST");
      $this->line("- " . strtoupper($environment) . "_FTP_USERNAME");
      $this->line("- " . strtoupper($environment) . "_FTP_PASSWORD");

      return false;
    }

    if (!$this->confirmDeployment($environment)) {
      $this->warn("Deployment cancelled by user.");

      return false;
    }

    return $this->ftpDeployService->deploy($environment, $this);
  }

  protected function getEnvironment(): string {
    if ($env = $this->option("env")) {
      return $env;
    }

    $environments = array_keys(config("git-ftp-deployer.environments"));

    return $this->choice("Select environment to deploy:", $environments);
  }

  protected function confirmDeployment(string $environment): bool {
    $this->line("");
    $this->line("Deployment Summary:");
    $this->line("• Environment: <fg=yellow>{$environment}</>");
    $this->line("• Host: <fg=cyan>" . config("git-ftp-deployer.environments.{$environment}.host") . "</>");
    $this->line("• Path: <fg=cyan>" . config("git-ftp-deployer.environments.{$environment}.path") . "</>");
    $this->line("• Username: <fg=cyan>" . config("git-ftp-deployer.environments.{$environment}.username") . "</>");
    $this->line("• Password: <fg=cyan>***************</>");
    $this->line("");

    return $this->confirm("Proceed with deployment?", false);
  }
}
