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
    $backup_path = $_ENV['BACKUP_PATHS'] ?? self::BACKUP_PATHS;
    $tar_options = $_ENV['BACKUP_TAR_OPTION'] ?? '';

    $cmd = sprintf(
      'tar czf /var/www/%s %s %s', $this->command->tarball, $tar_options, $backup_path
    );
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'tar files',
      $result['error'] ?? 'Failed to create tar files'
    );

    return $result['success'] ?? FALSE;
  }

}
