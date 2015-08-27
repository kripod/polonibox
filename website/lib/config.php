<?php
	function decode_json($file) {
		$feed = file_get_contents($file);
		$json = json_decode($feed, true);
		return $json;
	}
?>
