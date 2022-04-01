<?php

namespace App\Step;

/**
 * Create tarball.
 */
class ArchiveCreateTarballStep extends StepBase {

  const BACKUP_PATHS = '/var/www/html';

  /**
   * Run.
   */
  public function run() : bool {
    $tarball = $this->command->tarball;

    $this->command->msg(
      sprintf('Step: Create tarball "%s"', $tarball)
    );

    $backupPath = $_ENV['BACKUP_PATHS'] ?? self::BACKUP_PATHS;
    $tarOptions = $_ENV['BACKUP_TAR_OPTION'] ?? '';

    $cmd = sprintf('tar czf %s %s %s', '/var/www/' . $tarball, $tarOptions, $backupPath);
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'tar files',
      $result['error'] ?? 'Failed to create tar files'
    );

    return $result['success'] ?? FALSE;
  }

}
