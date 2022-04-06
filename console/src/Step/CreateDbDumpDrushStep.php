<?php

namespace App\Step;

/**
 * Create dump.
 */
class CreateDbDumpDrushStep extends StepBase {

  const SITE_ROOT = '/var/www/html';
  const DUMP_FILE_NAME = '.db.sql';

  /**
   * Run.
   */
  public function run() : bool {
    $drush = sprintf('/usr/local/bin/drush --root=%s', self::SITE_ROOT);
    $dbfile = $_ENV['DBFILE'] ?? implode('/', [self::SITE_ROOT, self::DUMP_FILE_NAME]);
    $dbskip = $_ENV['DBSKIP'] ?? '';

    $cmd = sprintf(
      '%s sql-dump  --extra-dump="--column-statistics=0" --result-file=%s %s', $drush, $dbfile, $dbskip
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'DRUSH db-dump',
      $result['error'] ?? 'Failed to create DRUSH db-dump'
    );
    return $result['success'] ?? FALSE;
  }

}
