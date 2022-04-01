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
      'Step: Download "%s"', $this->command->backupFileName
    ));

    if (empty($_ENV['AWS_ACCESS_KEY_ID']) || empty($_ENV['AWS_SECRET_ACCESS_KEY'])) {
      $this->command->msg('Failed with empty key or secret');
      return FALSE;
    }

    $bucket = $_ENV['AWS_BUCKET'] ?? '';
    $awsTarballPath = sprintf(
      's3://%s/%s', $bucket, $this->command->backupFileName
    );
    $this->command->localTarballPath = sprintf(
      'backup_%s.tar.gz',
      $this->command->backupName,
    );
    $params = $_ENV['AWS_CLI_PARAMS'] ?? '';

    $cmd = sprintf(
      'aws s3 cp %s %s %s',
      $awsTarballPath, $this->command->localTarballPath, $params
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
