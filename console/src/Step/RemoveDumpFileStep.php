<?php

namespace App\Step;

/**
 * Remove dump.
 */
class RemoveDumpFileStep extends StepBase {

  const SITE_ROOT = '/var/www/html';
  const DUMP_FILE_NAME = '.db.sql';

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Remove dump file');

    $dbfile = $_ENV['DBFILE'] ?? implode('/', [self::SITE_ROOT, self::DUMP_FILE_NAME]);

    $cmd = sprintf('rm -f %s', $dbfile);
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      sprintf('rm %s', $dbfile),
      $result['error'] ?? sprintf('Failed to rm %s', $dbfile)
    );

    return $result['success'] ?? FALSE;
  }

}
