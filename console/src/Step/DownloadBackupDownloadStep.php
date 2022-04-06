<?php

namespace App\Step;

/**
 * Download backup.
 */
class DownloadBackupDownloadStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    if (empty($_ENV['AWS_ACCESS_KEY_ID']) || empty($_ENV['AWS_SECRET_ACCESS_KEY'])) {
      $this->command->msg('Failed with empty key or secret');
      return FALSE;
    }

    $aws_tarball_path = sprintf(
      's3://%s/%s', $_ENV['AWS_BUCKET'] ?? '', $this->command->backup_file_name
    );
    $this->command->local_tarball_path = sprintf(
      'backup_%s.tar.gz',
      $this->command->app_key,
    );

    $cmd = sprintf(
      'aws s3 cp %s %s %s',
      $aws_tarball_path, $this->command->local_tarball_path, $_ENV['AWS_CLI_PARAMS'] ?? ''
    );
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Download Last Backup from AWS',
      $result['error'] ?? 'Failed to download last backup from AWS'
    );

    return $result['success'] ?? FALSE;
  }

}
