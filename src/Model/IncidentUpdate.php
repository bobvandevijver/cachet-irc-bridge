<?php

namespace App\Model;

use RuntimeException;

class IncidentUpdate extends AbstractSharedModel
{
  public function getName(): string
  {
    throw new RuntimeException('Name does not exists on an IncidentUpdate');
  }
}
