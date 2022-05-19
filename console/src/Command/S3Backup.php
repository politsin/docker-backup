<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Step\SetTimezoneStep;
use App\Step\CreateDbDumpStep;
use App\Step\ArchiveStep;
use App\Step\RemoveDumpFileStep;

/**
 * Echo.
 */
class S3Backup extends CommandBase implements CommandInterface {

  /**
   * Config.
   */
  protected function configure() {
    $this->setName('s3backup')
      ->setDescription('backup data to s3')
      ->setHelp('See Drupal\backup\Service\Backup');
  }

  /**
   * Exec.
   */
  protected function execute(
    InputInterface $input,
    OutputInterface $output
  ) : int {
    $this->io = new SymfonyStyle($input, $output);
    $this->sendMqttMessage('START', 'start');
    $this->sendMessage('Start backup', 'START');

    if (!(new SetTimezoneStep($this))->run()) {
      $this->sendMqttMessage('ERROR', 'SetTimezoneStep');
      return 101;
    }
    elseif (!(new CreateDbDumpStep($this))->run()) {
      $this->sendMqttMessage('ERROR', 'CreateDbDumpStep');
      return 102;
    }
    elseif (!(new ArchiveStep($this))->run()) {
      $this->sendMqttMessage('ERROR', 'ArchiveStep');
      (new RemoveDumpFileStep($this))->run();
      return 103;
    }
    elseif (!(new RemoveDumpFileStep($this))->run()) {
      $this->sendMqttMessage('ERROR', 'RemoveDumpFileStep');
      return 104;
    }

    $this->sendMessage('Finish backup', 'STOP');
    $this->sendMqttMessage('FINISH', 'finish');
    return 0;
  }

}
