<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Process\Process;

/**
 * Default StoreTemplate.
 */
class CommandBase extends Command {

  const CHANNELS_FOR_TYPES = [
    'OK' => ['console'],
    'FAIL' => ['console', 'telega', 'slack'],
    'START' => ['console', 'telega'],
    'STOP' => ['console', 'telega', 'slack'],
  ];
  const EMOJI_FOR_CHANELS = [
    'console' => [
      'OK' => NULL,
      'FAIL' => "🔥",
      'START' => "🚀",
      'STOP' => "☘️",
    ],
    'slack' => [
      'OK' => NULL,
      'FAIL' => ':fire: @all',
      'START' => ':rocket:',
      'STOP' => ':shamrock:',
    ],
    'telega' => [
      'OK' => NULL,
      'FAIL' => "🔥",
      'START' => "🚀",
      'STOP' => "☘️",
    ],
  ];

  /**
   * Exec log.
   */
  public function logExecute(
    bool $success,
    string $success_message,
    string $error_message
  ) : void {
    if ($success) {
      $this->sendMessage(
        sprintf('SUCCESS: "%s"', trim($success_message))
      );
    }
    else {
      $this->sendMessage(
        sprintf('ERROR: "%s"', trim($error_message)), 'FAIL'
      );
    }
  }

  /**
   * Run Command.
   *
   * @param string $cmd
   *   Command for exec.
   * @param int $timeout
   *   Timeout.
   */
  public function runProcess(string $cmd, int $timeout = 60000) {
    $process = Process::fromShellCommandline($cmd, NULL, $_ENV);
    $process->setTimeout($timeout);
    $process->start();
    $process->wait();

    return [
      'success' => $process->isSuccessful(),
      'output' => $process->getOutput(),
      'error' => $process->getErrorOutput(),
    ];
  }

  /**
   * Send message.
   */
  public function sendMessage(string $message, string $type = NULL) {
    if (empty(self::CHANNELS_FOR_TYPES[$type])) {
      $channel = $_ENV['MESSAGE_CHANNEL'] ?? 'console';
      $this->msg(
        sprintf('[%s] %s', $_ENV['BACKUP_NAME'], $message), $channel
      );
      return;
    }
    foreach (self::CHANNELS_FOR_TYPES[$type] as $channel) {
      $emoji = self::EMOJI_FOR_CHANELS[$channel][$type] ?? NULL;
      $this->msg(
        sprintf('%s[%s] %s', $emoji, $_ENV['BACKUP_NAME'], $message), $channel
      );
    }
  }

  /**
   * Common Sender.
   */
  private function msg(string $message, string $channel) {
    $result = FALSE;
    switch ($channel) {

      case 'console':
        $date_time_zone = new \DateTimeZone($_ENV['TIMEZONE']);
        $date_time = new \DateTime('now', $date_time_zone);
        $date_time_line = $date_time->format('d.m.Y H:i:s');
        $result = $this->io->text(
          implode(' | ', [$date_time_line, $_ENV['APP_KEY'], $_ENV['APP_TEMPLATE'], $message])
        );
        break;

      case 'slack':
        $result = $this->slack([
          'text' => $message,
        ]);
        break;

      case 'telega':
        $result = $this->telega($message);
        break;

      default:
    }

    return $result;
  }

  /**
   * Mattermost / slack Guzzle.
   */
  private function telega(string $message) {
    $client = new Client([
      'base_uri' => 'https://api.telegram.org',
      'timeout'  => 1,
    ]);
    $data = [
      'text' => $message,
      'chat_id' => "{$_ENV['TELEGA_CHANNEL']}",
    ];
    $query = http_build_query($data);
    try {
      $response = $client->get("/bot{$_ENV['TELEGA_TOKEN']}/sendMessage?$query");
      $result = $response->getBody()->getContents();
    }
    catch (ClientException $e) {
      $result = $e->getMessage();
    }
    catch (ConnectException $e) {
      $result = $e->getMessage();
    }
    return $result;
  }

  /**
   * Mattermost / slack Guzzle.
   */
  private function slack(array $payload) : string {
    $webhook = "{$_ENV['MATTERMOST_HOST']}/{$_ENV['MATTERMOST_HOOK']}";
    $payload['text'] = str_replace("%", "%25", $payload['text']);
    $payload['text'] = str_replace("&", "%26", $payload['text']);
    $client = new Client(['timeout' => 1]);
    try {
      $response = $client->post($webhook, [
        'json' => $payload,
      ]);
      $result = $response->getBody()->getContents();
    }
    catch (ClientException $e) {
      $result = $e->getMessage();
    }
    catch (ConnectException $e) {
      $result = $e->getMessage();
    }
    return $result;
  }


}
