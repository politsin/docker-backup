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
    $this->command->sendMqttMessage('START', 'RestoreDbDumpStep');
    $this->command->sendMessage(
      sprintf('Restore "%s" dump', $_ENV['DBDUMP'])
    );

    $result = FALSE;
    if ($_ENV['DBDUMP'] == 'drush') {
      $result = (new RestoreDbDumpMysqlStep($this->command))->run();
    }
    elseif ($_ENV['DBDUMP'] == 'mysql') {
      $result = (new RestoreDbDumpMysqlStep($this->command))->run();
    }
    elseif ($_ENV['DBDUMP'] == 'postgre') {
      $result = (new RestoreDbDumpPostgreStep($this->command))->run();
    }

    if ($result) {
      $this->command->sendMqttMessage('FINISH', 'RestoreDbDumpStep');
      return TRUE;
    }
    return FALSE;
  }

}
