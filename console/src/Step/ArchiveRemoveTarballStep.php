<?php

namespace App\Step;

/**
 * Remove tarball.
 */
class ArchiveRemoveTarballStep extends StepBase {

  const CONSOLE_PATHS = '/var/www';

  /**
   * Run.
   */
  public function run() : bool {
    $tarball = $this->command->tarball;

    $this->command->msg(
      sprintf('Step: Remove tarball "%s"', $tarball)
    );

    $consolePath = $_ENV['CONSOLE_PATHS'] ?? self::CONSOLE_PATHS;

    $cmd = sprintf(
      'rm -f %s', implode('/', [$consolePath, $tarball])
    );
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Removed old backup file',
      $result['error'] ?? 'Failed to remove old backup file'
    );

    return $result['success'] ?? FALSE;
  }

}
