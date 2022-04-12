<?php

namespace App\Step;

/**
 * Download backup.
 */
class DownloadBackupStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->sendMessage('Download backup');
    $this->command->app_key = $_ENV['APP_KEY'] ?? '';

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
