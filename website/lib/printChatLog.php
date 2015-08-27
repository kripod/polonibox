<?php
	require 'config.php';
	require 'mysql.php';

	function initialize_params($connection) {
		global $loadJsLive, $paramPageNumber, $paramPageMessageCount, $paramMessageNumber, $paramSearchCondition, $paramSearchSenderName, $paramSearchMessageText;
		$loadJsLive = true;

		$paramPageNumber = $_GET['page'];
		if ($paramPageNumber != null) {
			$paramPageNumber = $connection->real_escape_string($paramPageNumber);
			$loadJsLive = false;
		}
		if ($paramPageNumber <= 1) {
			$paramPageNumber = 1;
			$loadJsLive = true;
		}

		$paramPageMessageCount = $_GET['messagesPerPage'];
		if ($paramPageMessageCount != null) $paramPageMessageCount = $connection->real_escape_string($paramPageMessageCount);
		if ($paramPageMessageCount <= 0 || $paramPageMessageCount > 100) {
			$paramPageMessageCount = 100;
		}

		$paramMessageNumber = $_GET['messageId'];
		if ($paramMessageNumber != null) {
			$paramMessageNumber = $connection->real_escape_string($paramMessageNumber);
			$loadJsLive = false;
			return;
		}

		$paramSearchCondition = $_GET['searchCondition'];
		if ($paramSearchCondition != null) $paramSearchCondition = $connection->real_escape_string(strtoupper($paramSearchCondition));
		if ($paramSearchCondition != 'OR') {
			$paramSearchCondition = 'AND';
		};

		$paramSearchSenderName = $_GET['username'];
		if ($paramSearchSenderName != null) {
			$paramSearchSenderName = $connection->real_escape_string($paramSearchSenderName);
			$loadJsLive = false;
		}

		$paramSearchMessageText = $_GET['messageText'];
		if ($paramSearchMessageText != null) {
			$paramSearchMessageText = $connection->real_escape_string($paramSearchMessageText);
			$loadJsLive = false;
		}
	}

	// Connect to the DB, and then initialize parameters
	$connection = get_mysqli();
	initialize_params($connection);
	$pageNumberMinus1 = $paramPageNumber - 1;

	// Apply query conditions if needed
	$queryParameters = array();

	if ($paramMessageNumber != null) {
		$queryOrder = 'ASC';

		$queryParameters[] = 'messageNumber>' . ($paramMessageNumber - 20);
		if ($paramPageNumber == 1) {
			echo '<p class="text-center">Please scroll down to see the message highlighted in yellow!</p>';
		}

	} else {
		$queryOrder = 'DESC';

		if ($paramSearchSenderName != null) {
			$queryParameters[] = 'senderName="' . $paramSearchSenderName . '"';
		}
		if ($paramSearchMessageText != null) {
			$queryParameters[] = 'messageText LIKE "%' . $paramSearchMessageText . '%"';
		}
	}

	if (count($queryParameters) != 0) $queryConditions = ' WHERE ' . implode(' ' . $paramSearchCondition . ' ', $queryParameters);

	// Query messages and their count
	$result = $connection->multi_query(
		'SELECT senderName,senderReputation,messageNumber,messageDate,messageText FROM trollboxData' . $queryConditions . ' ORDER BY messageNumber ' . $queryOrder . ' LIMIT ' . $paramPageMessageCount . ' OFFSET ' . ($pageNumberMinus1 * $paramPageMessageCount) . ';' .
		'SELECT COUNT(*) FROM trollboxData' . $queryConditions
	);
	if(!$result) {
		echo '<p class="text-center">Error loading the list of messages, please try again later!</p>';
		return;
	}

	// Print messages
	if ($result = $connection->store_result()) {
		if ($result->num_rows == 0) {
			echo '<p class="text-center">No messages could be found.</p>';
			return;
		}

		$specialUsers = decode_json('/home/vsiaphpx/public_html/data/specialUsers.txt');

		$availableTimezones = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $_SERVER["HTTP_CF_IPCOUNTRY"]);
		$availableTimezoneMain = $availableTimezones[0];
		if ($availableTimezoneMain != null) {
			$clientTimezone = new DateTimeZone($availableTimezoneMain);
		} else {
			$clientTimezone = new DateTimeZone('UTC');
		}

		echo '<table class="table table-striped table-hover break-words"><thead><tr><th class="text-right">Sender</th><th>Message</th></tr></thead><tbody>';

		while ($row = $result->fetch_row()) {
			$senderName = htmlspecialchars($row[0]);
			$senderReputation = $row[1];
			$messageNumber = $row[2];
			$messageDate = $row[3];
			$messageText = htmlspecialchars($row[4]);

			$messageDateUtc = new DateTime($messageDate);
			$messageDateLocal = new DateTime($messageDate);
			$messageDateLocal->setTimezone($clientTimezone);

			switch ($senderName) {
				case 'Banhammer':
					$messageRowClass = ' class="danger"';
					break;
				case 'marking@poloniex':
					$messageRowClass = ' class="info"';
					break;
				default:
					$messageRowClass = '';
			}

			if ($messageNumber == $paramMessageNumber) {
				$messageRowClass = ' class="warning" id="windowDefaultTop"';
				$loadJsHighlightMessage = true;
			}

			switch ($specialUsers[$senderName]) {
				case 1:
					$senderBadgeCode = '<span title="Moderator" class="badge alert-info">M</span> ';
					break;
				case 2:
					$senderBadgeCode = '<span title="Engineer" class="badge alert-warning">E</span> ';
					break;
				case 3:
					$senderBadgeCode = '<span title="Administrator" class="badge alert-danger">A</span> ';
					break;
				default:
					$senderBadgeCode = '';
			}

			if ($senderReputation != null) {
				$senderBadgeCode .= '<span title="Reputation" class="badge">' . $senderReputation . '</span> ';
			}

			if (substr($messageText, 0, 3) == '/me') {
				if (substr($messageText, 4, 5) == '/tiny') {
					$messageText = '<i><small>' . substr($messageText, 10) . '</small></i>';
				} else {
					$messageText = '<i>' . substr($messageText, 4) . '</i>';
				}
			} else if (substr($messageText, 0, 5) == '/tiny') {
				$messageText = '<small>' . substr($messageText, 6) . '</small>';
			}

			echo '<tr' . $messageRowClass . '>' .
			'<td class="text-right">' .
			$senderBadgeCode . '<a href="?username=' . $senderName . '"><b>' . $senderName . '</b></a><br>' .
			'<small><a href="?messageId=' . $messageNumber . '"><i class="glyphicon glyphicon-link"></i></a> <span title="' . $messageDateUtc->format('Y-m-d H:i:s') . ' UTC">' . $messageDateLocal->format('Y-m-d H:i:s') . '</span></small>' .
			'</td>' .
			'<td>' . $messageText . '</td>' .
			'</tr>';
		}

		echo '</tbody></table>';

		// Free up resources
		$result->free();
	}

	$connection->next_result();

	// Print pagination
	if ($result = $connection->store_result()) {
		if ($result->num_rows) {
			parse_str(substr(strstr($_SERVER['REQUEST_URI'], '?'), 1), $queryData);

			echo '</div><div class="row"><div class="col-md-12"><div class="text-center"><ul class="pagination">';

			if ($pageNumberMinus1 != 0) {
				$queryData['page'] = 1;
				echo '<li><a href="?' . htmlspecialchars(http_build_query($queryData)) . '">&laquo;</a></li>';
				$queryData['page'] = $pageNumberMinus1;
				$pageUrlPrev = '?' . htmlspecialchars(http_build_query($queryData));
				echo '<li><a href="' . $pageUrlPrev . '">&lt;</a></li>';
			} else {
				echo '<li class="disabled"><a>&laquo;</a></li>' .
				'<li class="disabled"><a>&lt;</a></li>';
			}

			if ($row = $result->fetch_row()) {
				$rowCount = $row[0];
				$pageCount = ceil($rowCount / $paramPageMessageCount);

				for ($i = max($paramPageNumber - 4, 1); $i <= min(max($paramPageNumber + 4, 9), $pageCount); $i++) {
					if ($i != $paramPageNumber) {
						$queryData['page'] = $i;
						echo '<li><a href="?' . htmlspecialchars(http_build_query($queryData)) . '">' . $i . '</a></li>';
					} else {
						echo '<li class="active"><a>' . $i . '</a></li>';
					}
				}
			}

			if ($paramPageNumber < $pageCount) {
				$queryData['page'] = $paramPageNumber + 1;
				$pageUrlNext = '?' . http_build_query($queryData);
				echo '<li><a href="' . $pageUrlNext . '">&gt;</a></li>';
				$queryData['page'] = $pageCount;
				echo '<li><a href="?' . htmlspecialchars(http_build_query($queryData)) . '">&raquo;</a></li>';
			} else {
				echo '<li class="disabled"><a>&gt;</a></li>' .
				'<li class="disabled"><a>&raquo;</a></li>';
			}

			echo '</ul></div></div>';

			if (isset($pageUrlPrev)) {
				echo '<link rel="prev" property="prev" href="' . $pageUrlPrev . '"/>';
			}
			if (isset($pageUrlNext)) {
				echo '<link rel="next" property="next" href="' . $pageUrlNext . '"/>' .
				'<link rel="prerender" property="prerender" href="' . $pageUrlNext . '"/>';
			}

			// Free up resources
			$result->free();
		}
	}

	// Load the message highlighter if necessary
	if ($loadJsHighlightMessage) {
		echo '<script type="text/javascript" src="js/highlightMessage.js"></script>';
	}

	// Enable live mode if there are no query parameters given
	if ($loadJsLive) {
		echo '<script type="text/javascript" src="js/autobahn.js"></script>' .
		'<script type="text/javascript" src="js/linkify-string.js"></script>' .
		'<script type="text/javascript" src="js/live.js"></script>';
	}
?>
