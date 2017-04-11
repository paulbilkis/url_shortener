<?php
  // Подключаемый файл функций


function get_from_user_urls($mysqli, $url) // для получения строчки из таблицы user_urls с user_url=$url
{
 if (!($stmt = $mysqli->prepare("SELECT * FROM user_urls WHERE user_url LIKE ?"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

 if (!$stmt->bind_param("s", $url)) {
   echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
 }

 if (!$stmt->execute()) {
   echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
 }

 return $stmt->get_result();
 
}

function get_from_urls_table($mysqli, $id) // для получения строчки из таблиув urls_table с id=$id
{
 if (!($stmt = $mysqli->prepare("SELECT * FROM urls_table WHERE id=?"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

 if (!$stmt->bind_param("i", $id)) {
   echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
 }

 if (!$stmt->execute()) {
   echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
 }

 return $stmt->get_result();
 
}

function url_to_id($url) // для преобразования строчки символов в число (строчка воспринимается как число с основанием 62)
{
  $map_val = array_flip(array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9)));
  $id = 0;
  $url = array_reverse(str_split($url));
  foreach ($url as $key => $val){
    $id += $map_val[$val]*pow(62, $key);
  }
  return $id;
}

function map_hash($i) // вспомогательная для соответствия между десятичным чилосо 0-61 и соответствующим знаком
{
  $map_val =  array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
  return $map_val[$i];
}

function id_to_url($id) // для перевода десятичного числа в основание 62 (кодируется символами а-z, A-Z, 0-9)
{
  $digits = array();
  while ($id > 0) {
    $r = $id % 62;
    array_push($digits, $r);
    $id = intval($id / 62);
  }

  $digits = array_reverse($digits);
  $url = array_map("map_hash", $digits);
  return $url;
}

?>