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
    $this
      ->setName('s3backup')
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

    $this->msg(
      $this->getHelloMessage("🐹")
    );

    (new SetTimezoneStep($this))->run();
    (new CreateDbDumpStep($this))->run();
    (new ArchiveStep($this))->run();
    (new RemoveDumpFileStep($this))->run();

    $this->msg('Парам парам пам!');

    return 0;
  }

}
