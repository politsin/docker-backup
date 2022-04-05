<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Download backup.
 */
class DownloadBackupDownloadStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg(sprintf(
      'Step: Download "%s"', $this->command->backup_file_name
    ));

    if (empty($_ENV['AWS_ACCESS_KEY_ID']) || empty($_ENV['AWS_SECRET_ACCESS_KEY'])) {
      $this->command->msg('Failed with empty key or secret');
      return FALSE;
    }

    $bucket = $_ENV['AWS_BUCKET'] ?? '';
    $aws_tarball_path = sprintf(
      's3://%s/%s', $bucket, $this->command->backup_file_name
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
