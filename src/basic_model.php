<?php

namespace BasicModel;

abstract class BasicModel extends BasicObject {
  protected $primary_key = 'id';
  protected $db_name = DB_NAME;

  public function __call($name, $args) {
    if (strpos( $name , 'set_' ) === 0) {
      $this->{str_replace('set_', '', $name)} = $args[0];
    } else if(strpos( $name , 'get_' ) === 0) {
      return $this->{str_replace('get_', '', $name)};
    }
  }

  public function save() {
    $con = self::db_conn();
    $table_name = self::table_name();
    $key_value_pairs = [];
    foreach (get_object_vars($this) as $key => $val)
      if ($key != $this->primary_key && $key != 'primary_key' && $key != 'db_name') array_push($key_value_pairs, "$table_name.$key = '$val'");
    $key_value_pairs = implode($key_value_pairs, ', ');
    $id = call_user_func([$this, "get_".$this->primary_key]);
    $query = <<<SQL
      UPDATE $table_name SET $key_value_pairs WHERE $table_name.$this->primary_key = $id
    SQL;
    mysqli_query($con, $query);
    mysqli_close($con);
  }

  public static function init($conf) {
    foreach ($conf as $key => $val) define($key, $val);
  }

  public static function find($id) {
    $con = self::db_conn();
    $table_name = self::table_name();
    $klas = get_called_class();
    $primary_key = (new $klas)->primary_key;
    $query = <<<SQL
      SELECT * FROM $table_name WHERE $table_name.$primary_key = $id LIMIT 1
    SQL;
    $req = mysqli_query($con, $query);
    $data = NULL;
    $klas = get_called_class();
    while($res = mysqli_fetch_assoc($req)) {
      $obj = new $klas;
      foreach ($res as $key => $val) {
        if (property_exists($obj, $key)) call_user_func([$obj, "set_".$key], $val);
      }
      $data = $obj;
      break;
    }
    mysqli_close($con);
    return $data;
  }

  public static function all() {
    $con = self::db_conn();
    $req = mysqli_query($con, "SELECT * FROM ".self::table_name().";");
    $data = array();
    $klas = get_called_class();
    while($res = mysqli_fetch_assoc($req)) {
      $obj = new $klas;
      foreach ($res as $key => $val) {
        if (property_exists($obj, $key)) call_user_func([$obj, "set_".$key], $val);
      }
      array_push($data, $obj);
    }
    mysqli_close($con);
    return $data;
  }

  private static function table_name() {
    return self::pluralize(self::underscore(get_called_class()));
  }

  private static function db_conn() {
    $klas = get_called_class();
    $db_name = (new $klas)->db_name;
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, $db_name);
    if (mysqli_connect_errno()) die("Connect failed: ".mysqli_connect_error());
    return $con;
  }
}