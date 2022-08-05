<?php

namespace App;

use App\Model\Component;
use App\Model\Incident;
use App\Model\Schedule;
use BobV\IrkerUtils\Colorize;
use BobV\IrkerUtils\Connector;

class IrcConnector
{
  /** @var Connector|null */
  private $connector;

  public function __construct(bool $silent)
  {
    if ($silent) {
      return;
    }

    $this->connector = new Connector($_ENV['IRKER_SERVER'], $_ENV['IRKER_PORT']);
  }

  public function newIncident(Incident $incident): void
  {
    $this->sendIncident('nieuw', $incident);
  }

  public function newSchedule(Schedule $schedule): void
  {
    $this->sendSchedule('nieuw', $schedule);
  }

  public function updateComponent(Component $component, ?string $groupName): void
  {
    $this->send(sprintf('%s %s%s',
        Colorize::colorize(sprintf('[%s - %s%s]',
            $_ENV['COMPONENT_PREFIX'],
            $groupName ? ($groupName . '/') : '',
            $component->getName()
        ), Colorize::COLOR_ORANGE),
        $this->colorizedComponentState($component),
        $component->getLink() ? sprintf(' [ %s ]', Colorize::colorize($component->getLink(), Colorize::COLOR_BLUE)) : ''
    ));
  }

  public function updateIncident(Incident $incident): void
  {
    $this->sendIncident('update', $incident);
  }

  public function updateSchedule(Schedule $schedule): void
  {
    $this->sendSchedule('update', $schedule);
  }

  private function sendIncident(string $type, Incident $incident): void
  {
    $this->send(sprintf('%s %s: %s [ %s ]',
        Colorize::colorize(sprintf('[%s - %s]', $_ENV['STATUS_PREFIX'], ucfirst($type)), Colorize::COLOR_ORANGE),
        $this->colorizedIncidentState($incident),
        $incident->getName(),
        Colorize::colorize($incident->getPermalink(), Colorize::COLOR_BLUE)
    ));
  }

  private function sendSchedule(string $type, Schedule $schedule): void
  {
    $this->send(sprintf('%s %s: %s [ %s ]',
        Colorize::colorize(sprintf('[%s - %s]', $_ENV['SCHEDULE_PREFIX'], ucfirst($type)), Colorize::COLOR_ORANGE),
        $this->colorizedScheduleState($schedule),
        $schedule->getName(),
        Colorize::colorize($_ENV['FRONTEND_HOST'] . 'schedules/' . $schedule->getId(), Colorize::COLOR_BLUE)
    ));
  }

  private function colorizedComponentState(Component $component): string
  {
    $color = Colorize::COLOR_YELLOW;
    switch ($component->getStatus()) {
      case 0: // Unknown
      case 2: // Performance issue
        break;
      case 1: // Operational
        $color = Colorize::COLOR_GREEN;
        break;
      case 3: // Partial outage
        $color = Colorize::COLOR_LIGHT_RED;
        break;
      case 4: // Major outage
        $color = Colorize::COLOR_DARK_RED;
        break;
    }

    return Colorize::colorize($component->getStatusName(), $color);
  }

  private function colorizedIncidentState(Incident $incident): string
  {
    $color = Colorize::COLOR_YELLOW;
    switch ($incident->getLatestStatus()) {
      case 1: // Investigating
        break;
      case 2: // Identified
        $color = Colorize::COLOR_DARK_RED;
        break;
      case 3: // Watching
        $color = Colorize::COLOR_GREEN;
        break;
      case 4: // Fixed
        $color = Colorize::COLOR_LIGHT_GREEN;
        break;
    }

    return Colorize::colorize($incident->getLatestHumanStatus(), $color);
  }

  private function colorizedScheduleState(Schedule $schedule): string
  {
    $color = Colorize::COLOR_YELLOW;
    switch ($schedule->getStatus()) {
      case 0: // Upcoming
        break;
      case 1: // In progress
        $color = Colorize::COLOR_DARK_RED;
        break;
      case 2: // Complete
        $color = Colorize::COLOR_LIGHT_GREEN;
        break;
    }

    return Colorize::colorize($schedule->getHumanStatus(), $color);
  }

  private function send(string $message): void
  {
    if (!$this->connector) {
      return;
    }

    $this->connector->send($_ENV['IRC_ENDPOINT'], $message);
  }
}
