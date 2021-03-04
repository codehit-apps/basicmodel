<?php

namespace BasicModel;

abstract class BasicObject {
  public static function underscore($str) {
    $string = [];
    foreach (str_split($str) as $key => $char) {
      if ($key != 0 && $char == strtoupper($char)) array_push($string, '_');
      array_push($string, $char);
    }
    return strtolower(implode($string, ''));
  }

  public static function pluralize($str) {
    $string = str_split($str);
    if (strtolower($string[count($string)-1]) == 'y') $string[count($string)-1] = 'ie';
    array_push($string, 's');
    return implode($string, '');
  }
}
