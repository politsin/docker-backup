<?php

namespace App\Step;

/**
 * Archive.
 */
class ArchiveStep extends StepBase {

  const BACKUP_SUFFIX_MASK = 'Y-m-d-H-i-s';

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->sendMqttMessage('START', 'ArchiveStep');
    $this->command->sendMessage('Archive');

    $this->command->tarball = $this->getTarballName(
      $_ENV['BACKUP_NAME'] ?? 'bcp-d-0-dockup-example'
    );

    (new RemoveTrashStep($this->command))->run();

    if (!(new ArchiveCreateTarballStep($this->command))->run()) {
      (new ArchiveRemoveTarballStep($this->command))->run();
      return FALSE;
    }
    elseif (!(new ArchiveUploadTarballStep($this->command))->run()) {
      (new ArchiveRemoveTarballStep($this->command))->run();
      return FALSE;
    }
    elseif (!(new ArchiveRemoveTarballStep($this->command))->run()) {
      return FALSE;
    }
    $this->command->sendMqttMessage('FINISH', 'ArchiveStep');
    return TRUE;
  }

  /**
   * Get archive name.
   */
  private function getTarballName(string $backup_name) : string {
    return sprintf(
      '%s.%s.tar.gz', $backup_name, date(self::BACKUP_SUFFIX_MASK)
    );
  }

}
