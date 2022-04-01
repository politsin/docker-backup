<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Determine backup name.
 */
class DownloadBackupDetermineNameStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Step: Determine backup name');

    $bucket = $_ENV['AWS_BUCKET'] ?? '';
    $backupName = $this->command->backupName;
    $params = $_ENV['AWS_CLI_PARAMS'] ?? '';

    $cmd = sprintf(
      'aws s3 ls s3://%s %s | grep %s | sort -r | head -n1',
      $bucket, $params, $backupName
    );
    $result = $this->command->runProcess($cmd);

    $this->command->backupFileName = $this->parseFileNameFromResponse($result);
    if (empty($this->command->backupFileName)) {
      $this->command->logExecute(
        FALSE,
        'Last Backup Name: xxx',
        'Failed to get Last Backup Name',
      );
      return FALSE;
    }

    $this->command->logExecute(
      TRUE,
      sprintf('Last Backup Name: %s', $this->command->backupFileName),
      $result['error'] ?? 'Failed to get Last Backup Name'
    );
    return TRUE;
  }

  /**
   * Get file name.
   */
  private function parseFileNameFromResponse(array $result) :? string {
    if (empty($result['success'])) {
      return NULL;
    }
    $pattern = sprintf('/\s(%s[\d\S]+)$/', $this->command->backupName);
    if (preg_match($pattern, $result['output'] ?? '', $matches)) {
      return $matches[1] ?? NULL;
    }
    return NULL;
  }

}
