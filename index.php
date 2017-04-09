<!DOCTYPE html>
<html>
<body>

<h1>URL shortener</h1>

<?php

if (isset($_GET['url']) && !empty($_GET['url']){

echo <<<HTML

<p>Enter an URL:</p>
<form method="get" action="index.php">
	<input type="text" name="url" value="URL"/>
	<input type="submit" value="Send"/">
</form>

HTML;

}else{
	echo $_GET['url'];
}

?>
</body>
</html>