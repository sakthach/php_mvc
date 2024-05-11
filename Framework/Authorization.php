<?php 

namespace Framework;
use Framework\Session;

class Authorization{

  

  public static function isOwner($resouce_id){
    $resouce_id = (int) $resouce_id;
    $session_user = Session::get('user');
    
    if($session_user !== null && isset($session_user['id'])){
      $session_user_id = (int) $session_user['id'];
      return $session_user_id === $resouce_id;

      return false;
    }
  }




}