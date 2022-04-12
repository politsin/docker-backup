<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Step\DownloadBackupStep;
use App\Step\WriteSettingsStep;
use App\Step\RestoreDbDumpStep;
use App\Step\RemoveDumpFileStep;

/**
 * S3 Restore.
 */
class S3Restore extends CommandBase implements CommandInterface {

  const CODE_DOWNLOAD_ERROR = 201;

  /**
   * Config.
   */
  protected function configure() {
    $this->setName('s3restore')
      ->setDescription('restore data from s3')
      ->setHelp('See Drupal\backup\Service\BackupRestore');
  }

  /**
   * Exec.
   */
  protected function execute(
    InputInterface $input,
    OutputInterface $output
  ) : int {

    $this->io = new SymfonyStyle($input, $output);

    $this->sendMessage('Start restore', 'START');

    if (!(new DownloadBackupStep($this))->run()) {
      return self::CODE_DOWNLOAD_ERROR;
    }
    elseif (!(new WriteSettingsStep($this))->run()) {
      return 202;
    }
    elseif (!(new RestoreDbDumpStep($this))->run()) {
      return 203;
    }
    elseif (!(new RemoveDumpFileStep($this))->run()) {
      return 204;
    }
    $this->sendMessage('Finish restore', 'STOP');
    return 0;
  }

}
