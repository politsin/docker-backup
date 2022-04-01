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
    $this->command->msg(sprintf('Restore: "%s"', $this->command->dbrestore));

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Restore DB dump',
      $result['error'] ?? 'Function is not ready yet, TODO'
    );
    return $result['success'] ?? FALSE;
  }

}
