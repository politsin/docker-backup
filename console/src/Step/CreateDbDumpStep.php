<?php

namespace App\Step;

/**
 * Create dump.
 */
class CreateDbDumpStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    if (empty($_ENV['DBDUMP'])) {
      $this->command->sendMessage('Without dbdump');
      return TRUE;
    }

    $this->command->sendMessage(
      sprintf('Create "%s" dump', $_ENV['DBDUMP'])
    );

    $result = FALSE;
    if ($_ENV['DBDUMP'] == 'drush') {
      $result = (new CreateDbDumpDrushStep($this->command))->run();
    }
    elseif ($_ENV['DBDUMP'] == 'mysql') {
      $result = (new CreateDbDumpMysqlStep($this->command))->run();
    }
    elseif ($_ENV['DBDUMP'] == 'postgre') {
      $result = (new CreateDbDumpPostgreStep($this->command))->run();
    }
    if ($result) {
      return (new CreateDbDumpChownStep($this->command))->run();
    }
    return FALSE;
  }

}
