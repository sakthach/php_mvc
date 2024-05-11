<?php 


function basePath($path = ''){
  return __DIR__. '/'.$path;
}


function loadView($name, $data = []){
  $viewPath = basePath("App/views/{$name}.view.php");
  if(file_exists($viewPath)){
    extract($data);
    require $viewPath;
  } else {
    echo "No {$viewPath}";
  }
}


function loadPartial($name){
  $partialPath = basePath("App/views/partials/{$name}.php");
  if(file_exists($partialPath)){
    require $partialPath;
  } else {
    echo "No {$partialPath}";
  }
}
function inspect($value){
  echo '<pre>';
    var_dump($value);
  echo '<pre>';
}

function inspecAndDie($value){
  echo '<pre>';
    die(var_dump($value));
  echo '<pre>';
}


function sanitize($value){
  return  filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);
}

function redirect($url){
  header("Location: {$url}");
  exit;
}