<?php

namespace Codehit\BasicModel;

abstract class BasicModel extends BasicObject {
  protected $primary_key = 'id';
  protected $db_name = DB_NAME;

  public function __call($name, $args) {
    if (strpos( $name , 'set_' ) === 0) {
      $this->{str_replace('set_', '', $name)} = $args[0];
    } else if (strpos( $name , 'get_' ) === 0) {
      return $this->{str_replace('get_', '', $name)};
    }
  }

  public static function __callStatic($name, $args) {
    if (strpos( $name , 'find_by_' ) === 0) {
      $where = array(str_replace('find_by_', '', $name) => $args[0]);
      return self::where($where)[0];
    }
  }

  public function is_persisted() {
    $id = $this->_get_id();
    return isset($id);
  }

  public function save() {
    $this->is_persisted() ? $this->_update() : $this->_create();
  }

  public function destroy() {
    if ($this->is_persisted()) $this->_destroy();
  }

  public static function init($conf) {
    foreach ($conf as $key => $val) define($key, $val);
  }

  public static function where($where) {
    $con = self::db_conn();
    $table_name = self::table_name();
    $where_clause = array();
    foreach ($where as $key => $val) {
      array_push($where_clause, "$table_name.$key = '".mysqli_real_escape_string($con, $val)."'");
    }
    $where_clause = implode($where_clause, ' AND ');
    $query = <<<SQL
      SELECT * FROM $table_name WHERE $where_clause
    SQL;
    $req = mysqli_query($con, $query);
    $data = array();
    $klas = get_called_class();
    while($res = mysqli_fetch_assoc($req)) {
      $obj = new $klas;
      foreach ($res as $key => $val) {
        if (property_exists($obj, $key)) call_user_func([$obj, "set_".$key], $val);
      }
      array_push($data, $obj);
      break;
    }
    mysqli_close($con);
    return $data;
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
    $klas = get_called_class();
    $custom = (new $klas)->table_name;
    if (isset($custom)) {
      return $custom;
    }
    return self::pluralize(self::underscore(array_reverse(explode("\\", get_called_class()))[0]));
  }

  private static function db_conn() {
    $klas = get_called_class();
    $db_name = (new $klas)->db_name;
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, $db_name);
    if (mysqli_connect_errno()) die("Connect failed: ".mysqli_connect_error());
    return $con;
  }

  private function _update() {
    $con = self::db_conn();
    $table_name = self::table_name();
    $key_value_pairs = [];
    foreach (get_object_vars($this) as $key => $val)
      if ($this->is_fillable($key)) array_push($key_value_pairs, "$table_name.$key = '".mysqli_real_escape_string($con, $val)."'");
    $key_value_pairs = implode($key_value_pairs, ', ');
    $id = $this->_get_id();
    $query = <<<SQL
      UPDATE $table_name SET $key_value_pairs WHERE $table_name.$this->primary_key = $id
    SQL;
    mysqli_query($con, $query);
    mysqli_close($con);
  }

  private function _create() {
    $con = self::db_conn();
    $table_name = self::table_name();

    $columns = array();
    $values = array();
    foreach (get_object_vars($this) as $key => $val) {
      if ($this->is_fillable($key)) {
        array_push($columns, $key);
        array_push($values, "'".mysqli_real_escape_string($con, $val)."'");
      }
    }
    $columns = implode($columns, ', ');
    $values = implode($values, ', ');

    $query = <<<SQL
      INSERT INTO $table_name ($columns) VALUES ($values);
    SQL;
    mysqli_query($con, $query);
    mysqli_close($con);
  }

  private function _destroy() {
    $con = self::db_conn();
    $table_name = self::table_name();
    $klas = get_called_class();
    $primary_key = (new $klas)->primary_key;
    $id = $this->_get_id();
    $query = <<<SQL
      DELETE FROM $table_name WHERE $table_name.$primary_key = $id
    SQL;
    mysqli_query($con, $query);
    mysqli_close($con);
  }

  private function _get_id() {
    return call_user_func([$this, "get_".$this->primary_key]);
  }

  private function is_fillable($col) {
    return !in_array($col, ['table_name', 'primary_key', 'db_name', $this->primary_key]);
  }
}