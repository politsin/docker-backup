<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Untar backup.
 */
class DownloadBackupUntarStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg(sprintf(
      'Step: Untar "%s"', $this->command->localTarballPath
    ));

    $tarOptsRestore = $_ENV['BACKUP_TAR_OPTION_RESTORE'] ?? '';

    $cmd = sprintf(
      'tar xzf %s -C / %s',
      $this->command->localTarballPath, $tarOptsRestore
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
