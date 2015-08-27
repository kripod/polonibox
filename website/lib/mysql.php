<?php
	function get_mysqli() {
		$output = new mysqli(
			'p:localhost',
			'root',
			'root',
			'polonibox'
		);
		$output->set_charset('utf8mb4');

		if(mysqli_connect_errno()) {
			die('Failed to connect to MySQL.');
		}

		return $output;
	}
?>
