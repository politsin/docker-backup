<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Process\Process;
use Bluerhinos\phpMQTT;

/**
 * Default StoreTemplate.
 */
class CommandBase extends Command {

  const CHANNELS_FOR_TYPES = [
    'OK' => ['console'],
    'FAIL' => ['console', 'telega', 'slack'],
    'START' => ['console'],
    'STOP' => ['console', 'slack'],
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
      'code' => $process->getExitCode(),
    ];
  }

  /**
   * Send message.
   */
  public function sendMessage(string $message, string $type = 'OK') {
    if (empty(self::CHANNELS_FOR_TYPES[$type])) {
      $channel = $_ENV['MESSAGE_CHANNEL'] ?: 'console';
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

  /**
   * PhpMQTT.
   */
  public function sendMqttMessage(string $message, string $step = NULL) {
    $client_id = 'phpMQTT-client' . $_ENV['APP_KEY'];
    $mqtt = @(new phpMQTT($_ENV['MQTT_HOST'], $_ENV['MQTT_PORT'], $client_id));
    if ($mqtt->connect(TRUE, NULL, $_ENV['MQTT_USER'], $_ENV['MQTT_PASS'])) {
      $qos = 0;
      $retain = FALSE;
      $mqtt->publish(
        $this->getTopicForMqtt($step), $message, $qos, $retain
      );
      $mqtt->close();
    }
    else {
      $this->msg("Can't connect to mqtt", 'console');
    }
  }

  /**
   * MQTT topic.
   */
  private function getTopicForMqtt(string $step = NULL) : string {
    $period = 'period';
    if (preg_match('/bcp-([a-z])-/', $_ENV['BACKUP_NAME'], $matches)) {
      $period = $matches[1];
    }
    $topic_parts = array_filter([
      'backup', $_ENV['SERVER_NID'], $_ENV['APP_KEY'], $period, $step,
    ]);
    return implode('/', $topic_parts);
  }

}
