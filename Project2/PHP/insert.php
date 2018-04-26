<?php

while (true) {
	
	if (isset($_POST['string')) {
		echo("String: $_POST['string']\n");
	}
	unset($_POST['string']);
}

?>