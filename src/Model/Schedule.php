<?php

namespace App\Model;

class Schedule extends AbstractSharedModel
{

  public function getStatus(): int
  {
    return static::$accessor->getValue($this->data, '[status]');
  }

  public function getHumanStatus(): string
  {
    return static::$accessor->getValue($this->data, '[human_status]');
  }
}
