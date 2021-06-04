<?php

namespace App;

use App\Model\Incident;
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

  public function updateIncident(Incident $incident): void
  {
    $this->sendIncident('update', $incident);
  }

  private function sendIncident(string $type, Incident $incident): void
  {
    $this->send(sprintf('%s %s: %s [ %s ]',
        Colorize::colorize(sprintf('[TweakStatus - %s]', ucfirst($type)), Colorize::COLOR_ORANGE),
        $this->colorHumanState($incident),
        $incident->getName(),
        Colorize::colorize($incident->getPermalink(), Colorize::COLOR_BLUE)
    ));
  }

  private function colorHumanState(Incident $incident): string
  {
    $color = Colorize::COLOR_YELLOW;
    switch ($incident->getLatestStatus()) {
      case 1: // In onderzoek
        break;
      case 2: // Geïdentificeerd
        $color = Colorize::COLOR_DARK_RED;
        break;
      case 3: // Aan het opvolgen
        $color = Colorize::COLOR_GREEN;
        break;
      case 4: // Opgelost
        $color = Colorize::COLOR_LIGHT_GREEN;
        break;
    }

    return Colorize::colorize($incident->getLatestHumanStatus(), $color);
  }

  private function send(string $message): void
  {
    if (!$this->connector) {
      return;
    }

    $this->connector->send($_ENV['IRC_ENDPOINT'], $message);
  }
}
