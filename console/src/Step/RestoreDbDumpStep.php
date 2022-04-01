<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Restore db dump.
 */
class RestoreDbDumpStep extends StepBase {

  const DEFAULT_DBRESTORE_TYPE = 'mysql';

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    parent::__construct($command);

    $this->command->dbrestore = $_ENV['DBRESTORE'] ?? self::DEFAULT_DBRESTORE_TYPE;
  }

  /**
   * Run.
   */
  public function run() : bool {
    $dbrestore = $this->command->dbrestore;

    $this->command->msg(sprintf('Step: Restore "%s" dump', $dbrestore));

    $result = FALSE;
    if ($dbrestore == 'drush') {
      return (new RestoreDbDumpDrushStep($this->command))->run();
    }
    elseif ($dbrestore == 'mysql') {
      return (new RestoreDbDumpMysqlStep($this->command))->run();
    }
    elseif ($dbrestore == 'postgre') {
      return (new RestoreDbDumpPostgreStep($this->command))->run();
    }

    return FALSE;
  }

}
