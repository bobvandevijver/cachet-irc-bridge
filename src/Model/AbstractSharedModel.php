<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractSharedModel
{
  protected static PropertyAccessor $accessor;

  public function __construct(protected readonly array $data)
  {
    static::$accessor ??= new PropertyAccessor();
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

  public function getMessage(): string
  {
    return static::$accessor->getValue($this->data, '[message]');
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
}
