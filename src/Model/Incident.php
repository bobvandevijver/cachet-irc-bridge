<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Incident
{
  /** @var PropertyAccessor */
  private static $accessor;

  /** @var array */
  private $data;

  public function __construct(array $data)
  {
    if (!static::$accessor) {
      static::$accessor = new PropertyAccessor();
    }

    $this->data = $data;
  }

  public function getData(): array
  {
    return $this->data;
  }

  public function getId(): int
  {
    return static::$accessor->getValue($this->data, '[id]');
  }

  public function getName(): string
  {
    return static::$accessor->getValue($this->data, '[name]');
  }

  public function getCreatedAt(): DateTimeImmutable
  {
    return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', static::$accessor->getValue($this->data, '[created_at]'));
  }

  public function getUpdatedAt(): ?DateTimeImmutable
  {
    if (!$value = static::$accessor->getValue($this->data, '[updated_at]')) {
      return NULL;
    }

    return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
  }

  public function getLatestHumanStatus(): string
  {
    return static::$accessor->getValue($this->data, '[latest_human_status]');
  }

  public function getLatestStatus(): int
  {
    return static::$accessor->getValue($this->data, '[latest_status]');
  }

  public function getPermaLink(): string
  {
    return static::$accessor->getValue($this->data, '[permalink]');
  }
}
