<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Create dump.
 */
class CreateDbDumpStep extends StepBase {

  const DEFAULT_DUMP_TYPE = 'mysql';

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    parent::__construct($command);

    $this->command->dbdump = $_ENV['DBDUMP'] ?? self::DEFAULT_DUMP_TYPE;
  }

  /**
   * Run.
   */
  public function run() : bool {
    $dbdump = $this->command->dbdump;
    $this->command->msg(
      sprintf('Step: Create "%s" dump', $dbdump)
    );

    $result = FALSE;
    if ($dbdump == 'drush') {
      $result = (new CreateDbDumpDrushStep($this->command))->run();
    }
    elseif ($dbdump == 'mysql') {
      $result = (new CreateDbDumpMysqlStep($this->command))->run();
    }
    elseif ($dbdump == 'postgre') {
      $result = (new CreateDbDumpPostgreStep($this->command))->run();
    }
    if ($result) {
      return (new CreateDbDumpChownStep($this->command))->run();
    }
    return FALSE;
  }

}
