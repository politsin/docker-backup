<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Archive.
 */
class ArchiveStep extends StepBase {

  const BACKUP_SUFFIX_MASK = 'Y-m-d-H-i-s';

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    parent::__construct($command);

    $this->command->tarball = $this->getTarballName(
      $_ENV['APP_ID'] ?? 0,
      $_ENV['APP_NAME'] ?? 'dockup-example'
    );
  }

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Step: Archive');

    if (!(new ArchiveCreateTarballStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new ArchiveUploadTarballStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new ArchiveRemoveTarballStep($this->command))->run()) {
      return FALSE;
    }
    return FALSE;
  }

  /**
   * Get archive name.
   */
  private function getTarballName(int $appId, string $appName) : string {
    return sprintf(
      'bcp-d-%d-%s.%s.tar.gz', $appId, $appName, date(self::BACKUP_SUFFIX_MASK)
    );
  }

}
