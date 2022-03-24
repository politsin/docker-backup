<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Bluerhinos\phpMQTT;
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Echo.
 */
class S3Backup extends Command {

  /**
   * Config.
   */
  protected function configure() {
    $this
      ->setName('s3backup')
      ->setDescription('backup data to s3')
      ->setHelp('See Drupal\zapp_backup\accets\Backup')
      ->addArgument('text', InputArgument::OPTIONAL, 'Input text')
      ->addOption('commit');
  }

  /**
   * Exec.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $this->msg("hello 🐹 " . time());
    // $this->backup($output);
    $output->writeln($input->getArgument('text'));
    return 0;
  }
    /**
   * Common Sender.
   */
  public function msg($message, $type = 'telega', $error = FALSE) {
    $result = FALSE;
    switch ($type) {

      case 'slack':
        $result = $this->slack([
          'text' => $message,
        ]);
        break;

      case 'telega':
      default:
        $result = $this->telega($message);
        break;
    }

    return $result;
  }

  /**
   * Mattermost / slack Guzzle.
   */
  private function telega(string $message) {
    $client = new Client([
      'base_uri' => 'https://api.telegram.org',
      'timeout'  => 0.1,
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
