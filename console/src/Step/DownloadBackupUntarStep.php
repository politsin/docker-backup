<?php

namespace App\Step;

/**
 * Untar backup.
 */
class DownloadBackupUntarStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $cmd = sprintf(
      'tar xzf %s -C / %s',
      $this->command->local_tarball_path, $_ENV['BACKUP_TAR_OPTION_RESTORE'] ?? ''
    );
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Unpack backup',
      $result['error'] ?? 'Failed to unpack Last Backup'
    );

    return $result['success'] ?? FALSE;
  }

}
