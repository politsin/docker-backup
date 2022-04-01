<?php

namespace App\Step;

/**
 * Upload the tarball to S3.
 */
class ArchiveUploadTarballStep extends StepBase {

  const CONSOLE_PATHS = '/var/www';
  const AWS_DEFAULT_REGION = 'ru-central1';

  /**
   * Run.
   */
  public function run() : bool {
    $tarball = $this->command->tarball;

    $this->command->msg(
      sprintf('Step: Upload tarball "%s"', $tarball)
    );

    if (empty($_ENV['AWS_ACCESS_KEY_ID']) || empty($_ENV['AWS_SECRET_ACCESS_KEY'])) {
      $this->command->msg('Failed with empty key or secret');
      return FALSE;
    }

    $localTarballPath = implode('/', [
      $_ENV['CONSOLE_PATHS'] ?? self::CONSOLE_PATHS,
      $tarball,
    ]);
    $bucket = $_ENV['AWS_BUCKET'] ?? '';
    $awsTarballPath = sprintf('s3://%s/%s', $bucket, $tarball);
    $awsRegion = $_ENV['AWS_DEFAULT_REGION'] ?? self::AWS_DEFAULT_REGION;
    $params = $_ENV['AWS_CLI_PARAMS'] ?? '';

    $cmd = sprintf(
      'aws s3 cp %s %s --region "%s" %s',
      $localTarballPath, $awsTarballPath, $awsRegion, $params
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      's3 upload',
      $result['error'] ?? 'Failed to upload backup to AWS'
    );

    return $result['success'] ?? FALSE;
  }

}
