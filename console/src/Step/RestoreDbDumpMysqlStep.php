<?php

namespace App\Step;

/**
 * Restore dump.
 */
class RestoreDbDumpMysqlStep extends StepBase {

  const SITE_ROOT = '/var/www/html';
  const DUMP_FILE_NAME = '.db.sql';
  const DBUSER = 'drupal';
  const DBPASS = 'drupal';
  const DBNAME = 'drupal';

  /**
   * Run.
   */
  public function run() : bool {
    $dbuser = $_ENV['DBUSER'] ?? self::DBUSER;
    $dbpass = $_ENV['DBPASS'] ?? self::DBPASS;
    $dbname = $_ENV['DBNAME'] ?? self::DBNAME;
    $dbfile = $_ENV['DBFILE'] ?? implode('/', [self::SITE_ROOT, self::DUMP_FILE_NAME]);

    $cmd = sprintf(
      'mysql -u %s -p%s %s < %s', $dbuser, $dbpass, $dbname, $dbfile
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Restore DB dump',
      $result['error'] ?? 'Failed to restore DB dump'
    );
    return $result['success'] ?? FALSE;
  }

}
