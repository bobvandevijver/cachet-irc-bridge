#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\DbConnector;
use App\IrcConnector;
use App\Parser\ComponentParser;
use App\Parser\IncidentParser;
use App\Parser\ScheduleParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyAccess\PropertyAccessor;

(new SingleCommandApplication())
    ->setName('Cachet IRC Bridge')
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

      // Create HTTP client
      $httpClient = HttpClient::create([
          'timeout' => $_ENV['HTTP_TIMEOUT']
      ]);

      // Retrieve components
      if (Command::SUCCESS !== $result = (new ComponentParser($console, $db, $irc, $httpClient, $accessor))()) {
        return $result;
      }

      // Retrieve incidents
      if (Command::SUCCESS !== $result = (new IncidentParser($console, $db, $irc, $httpClient, $accessor))()) {
        return $result;
      }

      // Retrieve schedules
      if (Command::SUCCESS !== $result = (new ScheduleParser($console, $db, $irc, $httpClient, $accessor))()) {
        return $result;
      }

      $console->success('Done!');

      return Command::SUCCESS;
    })
    ->run();
