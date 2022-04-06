<?php

namespace App\Step;

/**
 * Create dump.
 */
class CreateDbDumpChownStep extends StepBase {

  const SITE_ROOT = '/var/www/html';
  const DUMP_FILE_NAME = '.db.sql';

  /**
   * Run.
   */
  public function run() : bool {
    $dbfile = $_ENV['DBFILE'] ?? implode('/', [self::SITE_ROOT, self::DUMP_FILE_NAME]);

    $cmd = sprintf('chown www-data:www-data %s', $dbfile);
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'CHOWN www-data',
      $result['error'] ?? 'Failed to CHOWN www-data'
    );
    return $result['success'] ?? FALSE;
  }

}
