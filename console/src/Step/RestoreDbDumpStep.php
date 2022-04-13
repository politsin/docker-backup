<?php

namespace App\Step;

/**
 * Restore db dump.
 */
class RestoreDbDumpStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    if (empty($_ENV['DBDUMP'])) {
      $this->command->sendMessage('Without dbdump');
      return TRUE;
    }

    $this->command->sendMessage(
      sprintf('Restore "%s" dump', $_ENV['DBDUMP'])
    );

    if ($_ENV['DBDUMP'] == 'drush') {
      return (new RestoreDbDumpDrushStep($this->command))->run();
    }
    elseif ($_ENV['DBDUMP'] == 'mysql') {
      return (new RestoreDbDumpMysqlStep($this->command))->run();
    }
    elseif ($_ENV['DBDUMP'] == 'postgre') {
      return (new RestoreDbDumpPostgreStep($this->command))->run();
    }

    return FALSE;
  }

}
