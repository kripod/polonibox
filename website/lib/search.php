<?php
	$searchParams = array();

	$paramUsername = $_POST['username'];
	if ($paramUsername != null) $searchParams['username'] = $paramUsername;

	$paramMessageText = $_POST['messageText'];
	if ($paramMessageText != null) $searchParams['messageText'] = $paramMessageText;

	$paramSearchCondition = $_POST['searchCondition'];
	if ($paramSearchCondition == 'or') $searchParams['searchCondition'] = $paramSearchCondition;

	header('Location: http://' . $_SERVER['HTTP_HOST'] . '/?' . http_build_query($searchParams), true, 301);
	die();
?>
