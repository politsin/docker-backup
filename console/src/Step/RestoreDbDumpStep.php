<?php

namespace App\Step;

/**
 * Restore db dump.
 */
class RestoreDbDumpStep extends StepBase {

  const DEFAULT_DBRESTORE_TYPE = 'mysql';

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->dbrestore = $_ENV['DBRESTORE'] ?: self::DEFAULT_DBRESTORE_TYPE;

    $this->command->msg(
      sprintf('Restore "%s" dump', $this->command->dbrestore)
    );

    $result = FALSE;
    if ($this->command->dbrestore == 'drush') {
      return (new RestoreDbDumpDrushStep($this->command))->run();
    }
    elseif ($this->command->dbrestore == 'mysql') {
      return (new RestoreDbDumpMysqlStep($this->command))->run();
    }
    elseif ($this->command->dbrestore == 'postgre') {
      return (new RestoreDbDumpPostgreStep($this->command))->run();
    }

    return FALSE;
  }

}
