#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\DbConnector;
use App\IrcConnector;
use App\Model\Incident;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyAccess\PropertyAccessor;

(new SingleCommandApplication())
    ->setName('TweakStatus IRC Bridge')
    ->setVersion('1.0.0')
    ->addOption('no-irc', NULL, InputOption::VALUE_NONE, 'Disable IRC output')
    ->setCode(function (InputInterface $input, ConsoleOutput $output) {
      $console  = new SymfonyStyle($input, $output);
      $accessor = new PropertyAccessor();
      $varDir   = __DIR__ . DIRECTORY_SEPARATOR . 'var';

      // Load env settings
      $dotenv = new Dotenv();
      $dotenv->loadEnv(__DIR__ . '/.env');

      // Open database
      $db  = new DbConnector($varDir);
      $irc = new IrcConnector($input->getOption('no-irc') === true);

      // Load status information
      $httpClient = HttpClient::create();
      $response   = $httpClient->request('GET', $_ENV['API_ENDPOINT']);
      if ($response->getStatusCode() !== 200) {
        $console->error([
            'HTTP request failed for:',
            $_ENV['API_ENDPOINT'],
            'The error message was:',
            json_encode($response->getInfo()),
        ]);

        return 1;
      }

      foreach ($accessor->getValue($response->toArray(), '[data]') as $apiItem) {
        $apiIncident = new Incident($apiItem);
        $dbIncident  = $db->getIncident($apiIncident->getId());

        if (!$dbIncident) {
          $console->text(sprintf('New incident! [%s] %s',
              $apiIncident->getLatestHumanStatus(), $apiIncident->getName()));
          $irc->newIncident($apiIncident);

          $db->storeIncident($apiIncident);
        } else {
          /** @noinspection PhpNonStrictObjectEqualityInspection */
          if ($dbIncident->getUpdatedAt() == $apiIncident->getUpdatedAt()) {
            // No updates
            continue;
          }

          $console->text(sprintf('Updated incident! [%s] %s',
              $apiIncident->getLatestHumanStatus(), $apiIncident->getName()));
          $irc->updateIncident($apiIncident);

          $db->updateIncident($apiIncident);
        }
      }

      $console->success('Done!');

      return 0;
    })
    ->run();
