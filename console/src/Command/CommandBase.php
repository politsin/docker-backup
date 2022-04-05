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

  /**
   * Exec log.
   */
  public function logExecute(
    bool $success,
    string $successMessage,
    string $errorMessage
  ) : void {
    if ($success) {
      $this->msg(
        sprintf('OK: "%s"', trim($successMessage))
      );
    }
    else {
      $this->msg(
        sprintf('ERROR: "%s"', trim($errorMessage))
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
   * Common Sender.
   */
  public function msg($message, $type = 'telega', $error = FALSE) {
    $result = FALSE;
    switch ($type) {

      case 'console':
        $result = $this->io->text($message);
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
    $client = new Client(['timeout' => 0.1]);
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
