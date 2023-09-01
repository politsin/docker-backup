<?php

namespace App\Step;

/**
 * Restore dump.
 */
class RestoreDbDumpPostgreStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Restore DB dump',
      $result['error'] ?? 'Function is not ready yet, TODO: psql --username=drupal --dbname=drupal < ~/html/.db.sql'
    );
    return $result['success'] ?? FALSE;
  }

}
