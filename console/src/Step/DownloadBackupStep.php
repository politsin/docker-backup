<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Download backup.
 */
class DownloadBackupStep extends StepBase {

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    parent::__construct($command);

    $this->command->backupName = $_ENV['BACKUP_NAME'] ?? '';
  }

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Step: Download backup');

    if (!(new DownloadBackupDetermineNameStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new DownloadBackupDownloadStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new DownloadBackupUntarStep($this->command))->run()) {
      return FALSE;
    }

    return TRUE;
  }

}
