<?php

namespace App\Step;

/**
 * Create dump.
 */
class CreateDbDumpMysqlStep extends StepBase {

  const SITE_ROOT = '/var/www/html';
  const DUMP_FILE_NAME = '.db.sql';
  const DBUSER = 'drupal';
  const DBHOST = 'localhost';
  const DBNAME = 'drupal';

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg(sprintf('Dump: "%s"', $this->command->dbdump));

    $dbuser = $_ENV['DBUSER'] ?? self::DBUSER;
    $dbpass = $_ENV['DBPASS'] ?? '';
    $dbhost = $_ENV['DBHOST'] ?? self::DBHOST;
    $dbname = $_ENV['DBNAME'] ?? self::DBNAME;
    $dbfile = $_ENV['DBFILE'] ?? implode('/', [self::SITE_ROOT, self::DUMP_FILE_NAME]);

    $cmd = sprintf(
      'mysqldump --column-statistics=0 -u%s -p%s -h%s %s > %s',
      $dbuser, $dbpass, $dbhost, $dbname, $dbfile
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'MySQL db-dump',
      $result['error'] ?? 'Failed to create MySQL db-dump'
    );
    return $result['success'] ?? FALSE;
  }

}
