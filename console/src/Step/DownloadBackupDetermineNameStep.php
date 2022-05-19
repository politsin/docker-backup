<?php

namespace App\Step;

/**
 * Determine backup name.
 */
class DownloadBackupDetermineNameStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $bucket = $_ENV['AWS_BUCKET'] ?? '';
    $params = $_ENV['AWS_CLI_PARAMS'] ?? '';
    $app_key = $_ENV['APP_KEY'] ?? '';

    $cmd = sprintf(
      'aws s3 ls s3://%s %s | grep %s | sort -r | head -n1',
      $bucket, $params, $app_key
    );
    $result = $this->command->runProcess($cmd);
    $this->command->backup_file_name = $this->parseFileNameFromResponse($result);
    if (empty($this->command->backup_file_name)) {
      $this->command->logExecute(
        FALSE,
        'Last Backup Name: xxx',
        'Failed to get Last Backup Name',
      );
      return FALSE;
    }

    $this->command->logExecute(
      TRUE,
      sprintf('Last Backup Name: %s', $this->command->backup_file_name),
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
    if (empty($result['output'])) {
      return NULL;
    }
    $parts = explode(' ', $result['output']);
    return trim(
      end($parts)
    );
  }

}
