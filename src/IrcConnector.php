<?php

namespace App;

use App\Model\Incident;

class IrcConnector
{
  /**
   * @var bool
   */
  private $silent;

  public function __construct(bool $silent)
  {
    $this->silent = $silent;
  }

  public function newIncident(Incident $incident): void
  {
    $this->sendIncident($incident);
  }

  public function updateIncident(Incident $incident): void
  {
    $this->sendIncident($incident);
  }

  private function sendIncident(Incident $incident): void
  {
    $this->send(sprintf('%s %s: %s [ %s ]',
        IrkerUtils::colorize('[TweakStatus]', IrkerUtils::COLOR_ORANGE),
        $this->colorHumanState($incident),
        $incident->getName(),
        IrkerUtils::colorize($incident->getPermalink(), IrkerUtils::COLOR_BLUE)
    ));
  }

  private function colorHumanState(Incident $incident): string
  {
    $color = IrkerUtils::COLOR_YELLOW;
    switch ($incident->getLatestStatus()) {
      case 1: // In onderzoek
        break;
      case 2: // Geïdentificeerd
        $color = IrkerUtils::COLOR_DARK_RED;
        break;
      case 3: // Aan het opvolgen
        $color = IrkerUtils::COLOR_GREEN;
        break;
      case 4: // Opgelost
        $color = IrkerUtils::COLOR_LIGHT_GREEN;
        break;
    }

    return IrkerUtils::colorize($incident->getLatestHumanStatus(), $color);
  }

  private function send(string $message): void
  {

    if ($this->silent) {
      return;
    }

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($socket, $_ENV['IRKER_SERVER'], $_ENV['IRKER_PORT']);
    // We cannot use JMS serializer here, as the JSON needs to be as clear as possible
    $data = json_encode([
        'to'      => $_ENV['IRC_ENDPOINT'],
        'privmsg' => $message,
    ]);
    socket_send($socket, $data, strlen($data), MSG_EOF);
    socket_close($socket);
  }
}
