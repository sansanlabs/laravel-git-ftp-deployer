<?php

namespace Sansanlabs\GitFtpDeployer\Services;

use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Process;

class GitStatusService {
  public function getUncommittedChanges(): array {
    $statusProcess = new Process(["git", "status", "--porcelain"]);
    $statusProcess->run();

    if (!$statusProcess->isSuccessful()) {
      throw new RuntimeException("Failed to run git status. Make sure you are in a git repository.");
    }

    $output = trim($statusProcess->getOutput());

    if (empty($output)) {
      return [];
    }

    return $this->parseGitStatusOutput($output);
  }

  public function getCurrentBranch(): string {
    $branchProcess = new Process(["git", "rev-parse", "--abbrev-ref", "HEAD"]);
    $branchProcess->run();

    if (!$branchProcess->isSuccessful()) {
      throw new RuntimeException("Failed to get current branch.");
    }

    return trim($branchProcess->getOutput());
  }

  public function getUnpushedCommitsCount(): int {
    $branch = $this->getCurrentBranch();
    $countProcess = new Process(["git", "rev-list", "--count", "origin/{$branch}..HEAD"]);
    $countProcess->run();

    if (!$countProcess->isSuccessful()) {
      // If the command fails, it might be because there's no remote tracking branch
      // In this case, we'll assume there are unpushed commits
      return 1;
    }

    return (int) trim($countProcess->getOutput());
  }

  public function displayChangesTree(array $changes, Command $command): void {
    $tree = $this->buildFileTree($changes["files"]);
    $treeLines = $this->flattenTree($tree);
    $this->displayTree($treeLines, $changes["statuses"], $command);
  }

  protected function parseGitStatusOutput(string $output): array {
    $filePaths = [];
    $fileStatuses = [];
    $lines = explode("\n", $output);

    foreach ($lines as $line) {
      if (empty(trim($line))) {
        continue;
      }

      $status = substr($line, 0, 2);
      $path = trim(substr($line, 3));
      $path = str_replace("\\", "/", $path);

      $fileStatuses[$path] = trim($status) === "??" ? "untracked" : "modified";

      if (is_dir($path)) {
        $this->addDirectoryFiles($path, $filePaths, $fileStatuses);
      } else {
        $filePaths[] = $path;
      }
    }

    return [
      "files" => $filePaths,
      "statuses" => $fileStatuses,
    ];
  }

  protected function addDirectoryFiles(string $path, array &$filePaths, array &$fileStatuses): void {
    try {
      $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS));

      foreach ($iterator as $file) {
        if ($file->isFile()) {
          $fullPath = str_replace("\\", "/", $file->getPathname());
          $filePaths[] = $fullPath;
          $fileStatuses[$fullPath] = $fileStatuses[$path] ?? "modified";
        }
      }
    } catch (\Exception $e) {
      // If we can't read the directory, just add the directory itself
      $filePaths[] = $path;
    }
  }

  protected function buildFileTree(array $filePaths): array {
    $tree = [];
    foreach ($filePaths as $filePath) {
      $parts = explode("/", $filePath);
      $current = &$tree;
      foreach ($parts as $part) {
        if (!isset($current[$part])) {
          $current[$part] = [];
        }
        $current = &$current[$part];
      }
      $current = true;
    }

    return $tree;
  }

  protected function flattenTree(array $tree): array {
    $treeLines = [];
    $counter = 1;

    $flattenTree = function ($tree, $prefix = "", &$counter = 1, $pathParts = []) use (&$flattenTree, &$treeLines) {
      $total = count($tree);
      $i = 0;
      foreach ($tree as $key => $value) {
        $i++;
        $isLast = $i === $total;
        $connector = $isLast ? "└── " : "├── ";
        $nextPrefix = $prefix . ($isLast ? "    " : "│   ");
        $fullPath = implode("/", array_merge($pathParts, [$key]));

        if ($value === true) {
          $treeLines[] = [
            "label" => $prefix . $connector . $key,
            "is_file" => true,
            "number" => $counter++,
            "raw_path" => $fullPath,
          ];
        } else {
          $treeLines[] = [
            "label" => $prefix . $connector . $key,
            "is_file" => false,
          ];
          $flattenTree($value, $nextPrefix, $counter, array_merge($pathParts, [$key]));
        }
      }
    };

    $flattenTree($tree, "", $counter);

    return $treeLines;
  }

  protected function displayTree(array $treeLines, array $fileStatuses, Command $command): void {
    if (empty($treeLines)) {
      return;
    }

    $maxLabelLength = max(array_map(fn($line) => $line["is_file"] ? mb_strwidth($line["label"]) : 0, $treeLines));
    $totalWidth = max($maxLabelLength + 10, 50);

    foreach ($treeLines as $line) {
      if ($line["is_file"]) {
        $label = $line["label"];
        $labelLength = mb_strwidth($label);
        $number = str_pad((string) $line["number"], 2, " ", STR_PAD_LEFT);
        $dotsCount = $totalWidth - $labelLength - mb_strwidth($number);
        $dots = str_repeat(".", max(1, $dotsCount));

        $rawPath = $line["raw_path"] ?? "";
        $status = $fileStatuses[$rawPath] ?? "modified";
        $color = $status === "untracked" ? "green" : "yellow";

        $command->line("<fg={$color}>{$label} {$dots} {$number} {$status}</>");
      } else {
        $command->line($line["label"]);
      }
    }
  }
}
