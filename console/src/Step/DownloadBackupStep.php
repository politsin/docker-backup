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
    $this->command->sendMqttMessage('START', 'DownloadBackupStep');
    $this->command->sendMessage('Download backup');

    if (!(new DownloadBackupDetermineNameStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new DownloadBackupDownloadStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new DownloadBackupUntarStep($this->command))->run()) {
      return FALSE;
    }

    $this->command->sendMqttMessage('FINISH', 'DownloadBackupStep');
    return TRUE;
  }

}
