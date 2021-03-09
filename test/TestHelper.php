<?php

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable($_ENV['PWD']);
$dotenv->load();

require_once 'src/BasicObject.php';
require_once 'src/BasicModel.php';

$con = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if (mysqli_connect_errno()) die("Connect failed: ".mysqli_connect_error());


$res = mysqli_query($con, "DROP TABLE posts");
if(!$res) var_dump($con);
$query = <<<SQL
CREATE TABLE posts (
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255),
  description VARCHAR(255)
);
SQL;

$res = mysqli_query($con, $query);
if(!$res) var_dump($con);

use Codehit\BasicModel\BasicModel;

BasicModel::init(array(
  'DB_HOST' => $_ENV['DB_HOST'],
  'DB_USER' => $_ENV['DB_USERNAME'],
  'DB_PASS' => $_ENV['DB_PASS'],
  'DB_NAME' => $_ENV['DB_NAME'],
));

class Post extends BasicModel {
  protected $id;
  protected $title;
  protected $description;
}