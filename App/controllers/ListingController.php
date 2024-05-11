<?php 

namespace App\Controllers;
use Framework\Database;
use Framework\Session;
use Framework\Validation;
use Framework\Authorization;

class ListingController
{
  protected $db;
  public function __construct()
  {
    $config = require basePath('config/db.php');
    $this-> db = new Database($config);
  }
  public function index(){
    $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC')->fetchAll();

    loadView('listings/index', ['listings' => $listings]);
  }


  public function create(){
    loadView('listings/create', []);
  }

  public function show($params){
   

    $id = $params['id'] ?? '';
    $params = [
      'id' => $id
    ];

    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();
    
    if(!$listing){
      ErrorController::notFound();
      return;
    }
    
    loadView('listings/show', ['listing' => $listing ] );
  }


  public function store(){
    $allowedFields = [
      'title', 'description', 'salary','tags', 'company','address', 'city', 'state','phone', 'email', "requirements", 'benefits'
    ];
    $newListingData = array_intersect_key($_POST, array_flip($allowedFields));
    $newListingData['user_id'] = Session::get('user')['id'];
    $newListingData= array_map('sanitize', $newListingData);

    $requireFields = ["title", "description", "requirements",  "salary", "email", "city", "state"];
    $errors = [];

    foreach($requireFields as $field){
      if(empty($newListingData[$field]) || !Validation::string($newListingData[$field])){
        $errors[$field] = ucfirst($field). ' is required';
      }
    }
  
    if(!empty($errors)){
        
        loadView('listings/create', ['errors' => $errors, "listings" => $newListingData]);
    } else{
      
        $fields = [];
        foreach ($newListingData as $field => $value) {
          $fields[] = $field;
        }


        $fields = implode(', ', $fields);

        $values = [];
        foreach($newListingData as $field => $value){
          if($value === ''){
              $newListingData[$field] = null;
          }

          $values[] = ':'.$field;
        }

        $values = implode(', ', $values);
        $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";
        $this->db->query($query, $newListingData );


        redirect("/");



        
    }


  }


  public function destroy($params){
    $id = $params['id'];
    $params = [
      'id' => $id
    ];
  
    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();
    if(!$listing){
      ErrorController::notFound('Listing not found');
      return;
    }

    // authorize
    
    if(!Authorization::isOwner($listing->user_id)){
      
      $_SESSION['error_message'] =" Not authorized ";
      
      return redirect('/listings/'.$listing->id);
    }



    $this->db->query('DELETE FROM listings WHERE id = :id', $params);
    // set flash message
    Session::setFlashMessage('error_message','Listing Delete success' );
    redirect('/listings');
  }


  public function edit($params){
    $id = $params['id'] ?? '';
    $params = [
      'id' => $id
    ];

    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();
    
    if(!$listing){
      ErrorController::notFound();
      return;
    }

    
    
    loadView('listings/edit', ['listing' => $listing ] );
  }

  public function update($params){

    $id = $params['id'] ?? '';
    $params = [
      'id' => $id
    ];

    $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();
    
    if(!$listing){
      ErrorController::notFound();
      return;
    }

    $allowedFields = [
      'title', 'description', 'salary','tags', 'company','address', 'city', 'state','phone', 'email', "requirements", 'benefits'
    ];

    $updateValues = array_intersect_key($_POST, array_flip($allowedFields));
    $updateValues = array_map('sanitize', $updateValues);
    $requireFields = ["title", "description", "requirements",  "salary", "email", "city", "state"];

    $errors = [];
    foreach($requireFields as $field){
      if(empty($updateValues[$field])  || !Validation::string($updateValues[$field])){
        $errors[$field] = ucfirst($field). 'is required';
      }
    }

    if(!empty($errors)){
      loadView('listings/edit', [
        'listing' => $listing,
        'errors' => $errors
      ]);
    }else{
      $updateFields = [];
      foreach(array_keys($updateValues) as $field){
        $updateFields[] = "{$field} = :{$field}";
      }

      $updateFields = implode(', ', $updateFields);
      $updateValues['id'] = $id;
      $updateQuery = "UPDATE listings SET $updateFields WHERE id = :id";
      $this->db->query($updateQuery, $updateValues);
      Session::setFlashMessage('sucess_message','Listing Delete success' );
      redirect('/listings/'.$id );

    }

  
  }



}