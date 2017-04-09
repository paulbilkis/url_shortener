<!DOCTYPE html>
<html>
<head>
<title>URL shortener</title>
</head>
<body>

<h1>URL shortener</h1>

<?php
function map_hash ($i) {
  $map_val =  array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
  return $map_val[$i];
}

function url_to_id ($url) {
  $map_val =  array_flip(array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9)));
  $id = 0;
  $url = array_reverse($url);
  foreach ($url as $key => $val){
    $id += $map_val[$val]*pow(62, $key);
  }
  return $id;
}

function id_to_url ($id) {
  $digits = array();
  while ($id > 0){
    $r = $id % 62;
    array_push($digits, $r);
    $id = intval($id / 62);
  }

  $digits = array_reverse($digits);
  $url = array_map("map_hash", $digits);
  return $url;
}

if ($url = filter_input(INPUT_GET, 'url')){
  // регулярное выражение для проверки корректности URL
  $regex_url = "((https?|ftp)\:\/\/)?";
  $regex_url .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; 
  $regex_url .= "([a-z0-9-.]*)\.([a-z]{2,3})";
  $regex_url .= "(\:[0-9]{2,5})?";
  $regex_url .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; 
  $regex_url .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; 
  $regex_url .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";
  
  if (preg_match("/^$regex_url$/", $url)){
    
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
    
    $ur = implode(id_to_url($mysqli->insert_id));

    echo <<<HTML
      <a href="/url/$ur">http://cr08919.tmweb.ru/url/$ur</a>
HTML;
    
  }else{
    echo <<<HTML
      <p>You have entered an invalid URL. Abort.</p>
HTML;
  }
  
}
?>

<p>Enter an URL:</p>
<form method="get" action="index.php">
	<input type="text" name="url"/>
	<input type="submit" value="Send"/>
</form>
</body>
</html>