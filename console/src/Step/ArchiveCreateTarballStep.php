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
      'tar -czf /var/www/%s %s %s', $this->command->tarball, $tar_options, $backup_path
    );
    $result = $this->command->runProcess($cmd);

    $work_result = $result['success'] || $result['code'] == 1;
    $success_message = ($result['code'] == 1) ? $result['error'] : 'tar files';

    $this->command->logExecute(
      $work_result,
      $success_message,
      $result['error'] ?? 'Failed to create tar files'
    );

    return $work_result;
  }

}
