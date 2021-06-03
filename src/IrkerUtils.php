<?php

namespace App;

class IrkerUtils
{
  /**
   * 0  White  (255,255,255)
   * 1  Black  (0,0,0)
   * 2  Blue  (0,0,127)
   * 3  Green  (0,147,0)
   * 4  Light Red  (255,0,0)
   * 5  Brown  (127,0,0)
   * 6  Purple  (156,0,156)
   * 7  Orange  (252,127,0)
   * 8  Yellow  (255,255,0)
   * 9  Light Green  (0,252,0)
   * 10  Cyan  (0,147,147)
   * 11  Light Cyan  (0,255,255)
   * 12  Light Blue  (0,0,252)
   * 13  Pink  (255,0,255)
   * 14  Grey  (127,127,127)
   * 15  Light Grey  (210,210,210)
   */
  const COLOR_WHITE = 0;
  const COLOR_BLACK = 1;
  const COLOR_BLUE = 2;
  const COLOR_GREEN = 3;
  const COLOR_LIGHT_RED = 4;
  const COLOR_DARK_RED = 5;
  const COLOR_PURPLE = 6;
  const COLOR_ORANGE = 7;
  const COLOR_YELLOW = 8;
  const COLOR_LIGHT_GREEN = 9;
  const COLOR_CYAN = 10;
  const COLOR_LIGHT_CYAN = 11;
  const COLOR_LIGHT_BLUE = 12;
  const COLOR_PINK = 13;
  const COLOR_GREY = 14;
  const COLOR_LIGHT_GREY = 15;

  static function colorize(string $message, int $color): string
  {
    if ($color < 10) {
      return sprintf('%s0%s%s%s', chr(03), $color, $message, chr(03));
    } else {
      return sprintf('%s%s%s%s', chr(03), $color, $message, chr(03));
    }
  }
}
