<?php

// config for SanSanLabs/GitFtpDeployer
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
