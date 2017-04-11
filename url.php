<?php

include "functions.php";

if (!($url = filter_input(INPUT_GET, 'url'))) {
  echo "Error with URL.";
}

if (!($action = filter_input(INPUT_GET, 'action'))) {
  echo "Error action.";
}

if ($action == "get"){
  
  $regex_encoded_url = "([a-zA-Z0-9]+)";

  if (preg_match("/^$regex_encoded_url$/", $url)) {
    
    $mysqli = new mysqli("localhost", "cr08919_database", "test12345", "cr08919_database");

    if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    $result = get_from_user_urls($mysqli, $url);
    
    if ($result->num_rows){
      $user_url_row = $result->fetch_assoc();
      $id = $user_url_row['pointer'];
    } else {
      $id = url_to_id($url);
    }

    $result = get_from_urls_table($mysqli, $id);

    if ($row = $result->fetch_assoc()) {

      $url = $row['url'];

      if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
	$url = "http://".$url;
      }

      header("Location: ".$url, true, 301);
      exit;
    } else {
      echo "<p>Sorry, there's no such url.</p>";
    }
  }
} elseif ($action == "add") {
  // регулярное выражение для проверки корректности URL
  $regex_url = "((https?|ftp)\:\/\/)?";
  $regex_url .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; 
  $regex_url .= "([a-z0-9-.]*)\.([a-z]{2,3})";
  $regex_url .= "(\:[0-9]{2,5})?";
  $regex_url .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; 
  $regex_url .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; 
  $regex_url .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";
  
  if (preg_match("/^$regex_url$/", $url)) {
    
    $mysqli = new mysqli("localhost", "cr08919_database", "test12345", "cr08919_database");
    
    if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    if (!$mysqli->query("CREATE TABLE IF NOT EXISTS urls_table(id INT AUTO_INCREMENT, url TEXT, PRIMARY KEY (id))")) {
      echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    if (!($stmt = $mysqli->prepare("INSERT INTO urls_table(url) VALUES (?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }


    if (!$stmt->bind_param("s", $url)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    $last_id = $mysqli->insert_id;
    $ur = implode(id_to_url($last_id));

    $user_url = filter_input(INPUT_GET, 'user_url');
    
    
    if ($user_url != "") {
      
      if (preg_match("/^([a-zA-Z0-9]+)$/", $user_url) && strlen($user_url) <= 100) {
	
	if (!$mysqli->query("CREATE TABLE IF NOT EXISTS user_urls(user_url CHAR(100), pointer INT)")) {
	  echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}

	// проверка на существование введенной пользователем метки


	$result = get_from_user_urls($mysqli, $user_url);

	if (!$result->num_rows) {
	
	  if (!($stmt = $mysqli->prepare("INSERT INTO user_urls(user_url,pointer) VALUES (?,?)"))) {
	    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	  }
	
	  if (!$stmt->bind_param("si", $user_url, $last_id)) {
	    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	  }

	  if (!$stmt->execute()) {
	    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  }

	  $ur = $user_url;
	  
	} else {
	  echo "<p>User URL label exists, label will be generated.</p>";
	}
      } else {
	echo "<p>User URL label is not correct, label will be generated.</p>";
      }
    }
    
   
    echo <<<HTML
      <a href="/url/$ur">http://cr08919.tmweb.ru/url/$ur</a>
HTML;
    
  } else {
    echo <<<HTML
      <p>You have entered an invalid URL. Abort.</p>
HTML;
  }

} else {
  echo "Wrong action.";
}
?>