<?php

namespace App\Step;

/**
 * Remove tarball.
 */
class RemoveTrashStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $what_about_trash = $_ENV['WHAT_ABOUT_TRASH'] ?? '';
    if ($what_about_trash == 'save') {
      $this->command->sendMessage('Trash saved');
      return TRUE;
    }
    $commands = [
      'rm -r /var/www/console',
      'rm -f /var/www/html/adminer.php',
      'rm -f /var/www/html/adminer.sql',
    ];
    foreach ($commands as $command) {
      $this->command->runProcess($command);
    }
    return TRUE;
  }

}
