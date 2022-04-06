<?php

namespace App\Step;

/**
 * Create dump.
 */
class CreateDbDumpPostgreStep extends StepBase {

  const SITE_ROOT = '/var/www/html';
  const DUMP_FILE_NAME = '.db.sql';
  const DBHOST = 'localhost';
  const DBUSER = 'drupal';
  const DBPASS = 'drupal';
  const DBNAME = 'drupal';

  /**
   * Run.
   */
  public function run() : bool {
    $dbuser = $_ENV['DBUSER'] ?? self::DBUSER;
    $dbhost = $this->getDbHost($_ENV['DBHOST'] ?? self::DBHOST);
    $dbname = $_ENV['DBNAME'] ?? self::DBNAME;
    $dbfile = $_ENV['DBFILE'] ?? implode('/', [self::SITE_ROOT, self::DUMP_FILE_NAME]);

    $cmd = sprintf(
      'pg_dump -U %s %s %s > %s',
      $dbuser, $dbhost, $dbname, $dbfile
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'PostgreSQL db-dump',
      $result['error'] ?? 'Failed to create PostgreSQL db-dump'
    );
    return $result['success'] ?? FALSE;
  }

  /**
   * Run.
   */
  private function getDbHost(string $dbhost) : string {
    if ($dbhost != self::DBHOST) {
      return sprintf('-h %s', $dbhost);
    }
    return $dbhost;
  }

}
