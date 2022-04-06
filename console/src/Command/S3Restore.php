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

  const CODE_DOWNLOAD_ERROR = 10;

  /**
   * Config.
   */
  protected function configure() {
    $this
      ->setName('s3restore')
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

    $this->msg(
      $this->getHelloMessage("🐼")
    );

    if (!(new DownloadBackupStep($this))->run()) {
      $this->msg('Download error');
      return self::CODE_DOWNLOAD_ERROR;
    }
    (new WriteSettingsStep($this))->run();
    (new RestoreDbDumpStep($this))->run();
    (new RemoveDumpFileStep($this))->run();

    $this->msg('Парам парам пам!');
    
    return 0;
  }

}
