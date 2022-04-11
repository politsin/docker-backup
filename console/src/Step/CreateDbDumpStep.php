<?php

namespace App\Step;

/**
 * Create dump.
 */
class CreateDbDumpStep extends StepBase {

  const DEFAULT_DUMP_TYPE = 'mysql';

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->dbdump = $_ENV['DBDUMP'] ?: self::DEFAULT_DUMP_TYPE;

    $this->command->msg(
      sprintf('Create "%s" dump', $this->command->dbdump)
    );

    $result = FALSE;
    if ($this->command->dbdump == 'drush') {
      $result = (new CreateDbDumpDrushStep($this->command))->run();
    }
    elseif ($this->command->dbdump == 'mysql') {
      $result = (new CreateDbDumpMysqlStep($this->command))->run();
    }
    elseif ($this->command->dbdump == 'postgre') {
      $result = (new CreateDbDumpPostgreStep($this->command))->run();
    }
    if ($result) {
      return (new CreateDbDumpChownStep($this->command))->run();
    }
    return FALSE;
  }

}
