<?php 

namespace App\Controllers;
use Framework\Database;
use Framework\Validation;
use Framework\Session;

class UserController {

  protected $db;

  public function __construct()
  {
    $config = require basePath('config/db.php');
    $this->db = new Database($config);
  }

  public function create(){
    loadView('users/create');
  }


  public function login(){
    loadView('users/login');
  }

  public function store(){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    $errors = [];

    if(!Validation::email($email)){
      $errors['email'] = 'Please enter correct email address';
    }

    if(!Validation::string($name, 2, 50)){
      $errors['name'] = "Name must be between 2 and 50";
    }

    if(!Validation::string($password, 6, 50)){
      $errors['password'] = "Password at least 6";
    }

    if(!Validation::match($password, $password_confirmation)){
      $errors['password_confirmation'] = 'Password not match';
    }




    if(!empty($errors)){
      loadView('users/create', [
        'errors' => $errors,
        'user' => [
          'name'  => $name,
          'email' => $email,
          'city'  => $city,
          'state' => $state

        ]
      ]);
      exit;
    }

    $params = [
      'email' => $email
    ];

    $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();
    if($user){
      $errors['email'] = 'That email already exist';
      loadView('users/create', [
        'errors' => $errors,
        'user' => [
          'name'  => $name,
          'email' => $email,
          'city'  => $city,
          'state' => $state

        ]
      ]);
      exit;
    }

    $params = [
      'name'  => $name,
          'email' => $email,
          'city'  => $city,
          'state' => $state,
          'password' => password_hash($password, PASSWORD_DEFAULT)
    ];

    $this->db->query('INSERT INTO users (name, email, city, state, password) VALUES (:name, :email, :city, :state, :password)', $params);
    // get new user id
    $userId = $this->db->conn->lastInsertId();
    Session::set('user', [
      'id' => $userId,
      'name'  => $name,
      'email' => $email,
      'city'  => $city,
      'state' => $state

    ]);
    redirect('/');

  }


  public function logout(){
    Session::clearAll();
    $params = session_get_cookie_params();
    setcookie('PHPSESSID','', time() - 86400, $params['path'], $params['domain'] );
    redirect('/');
  }

  public function authenticate(){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $errors = [];

    if(!Validation::email($email)){
      $errors['email'] = 'Invalid email';
    }
    if(!Validation::string($password, 6, 50)){
      $errors['password'] = 'al least 6';
    }

    if(!empty($errors)){
      loadView('users/login', [
        'errors' => $errors,
        'email' => $email
      ]);
      exit;
    }
    $params = [
      'email' => $email
    ];
    $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

    if(!$user){
      $errors['email'] ='Invalid Credential';
      loadView('users/login', [
        'errors' => $errors,
        'email' => $email
      ]);
      exit;
    }

    if(!password_verify($password, $user->password)){
      $errors['email'] ='Invalid Credential';
      loadView('users/login', [
        'errors' => $errors,
        'email' => $email
      ]);
      exit;
    }

    Session::set('user', [
      'id'    => $user->id,
      'name'  => $user->name,
      'email' => $user->$email,
      'city'  => $user->city,
      'state' => $user->state

    ]);
    redirect('/');

  }

}