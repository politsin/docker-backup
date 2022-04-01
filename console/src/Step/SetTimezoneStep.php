<?php

namespace App\Step;

/**
 * Set timezone.
 */
class SetTimezoneStep extends StepBase {

  const DEFAULT_TIMEZONE = 'Europe/Moscow';

  /**
   * Run.
   */
  public function run() : bool {
    $timeZone = $_ENV['TIMEZONE'] ?? self::DEFAULT_TIMEZONE;

    $this->command->msg(
      sprintf('Step: Set timezone to "%s"', $timeZone)
    );

    $cmd = sprintf('echo "%s" > /etc/timezone', $timeZone);

    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      '"/etc/timezone" set successfully',
      $result['error'] ?? '"/etc/timezone" set error'
    );
    if (empty($result['success'])) {
      return FALSE;
    }

    $cmd = sprintf('cp /usr/share/zoneinfo/%s /etc/localtime', $timeZone);

    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      '"/etc/localtime" set successfully',
      $result['error'] ?? '"/etc/localtime" set error'
    );
    return $result['success'] ?? FALSE;
  }

}
