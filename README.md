# BasicModel, Basic PHP Object-relational mapping (ORM)#

## Getting Started ##

### Installation ###

The recommended way to install BasicModel is through [Composer](http://getcomposer.org):
```
$ composer require codehit/basicmodel:dev-master
```

### Examples ###

```php
<?php 

// Ensure you have included composer's autoloader  
require_once __DIR__ . '/vendor/autoload.php';

use Codehit\BasicModel\BasicModel;

BasicModel::init(array(
  'DB_HOST' => 'localhost:8889',
  'DB_USER' => 'root',
  'DB_PASS' => 'root',
  'DB_NAME' => 'db_name',
));

class WpUser extends BasicModel {
  protected $primary_key = 'ID';

  protected $ID;
  protected $user_login;
  protected $user_pass;
  protected $user_nicename;
  protected $user_email;
  protected $user_url;
  protected $user_registered;
  protected $user_activation_key;
  protected $user_status;
  protected $display_name;
}

// Find user by id
$user = WpUser::find(1);

// Find all users
$users = WpUser::all();
```