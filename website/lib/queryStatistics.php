<?php
	require 'config.php';
	require 'mysql.php';

	// Connect to the DB
	$connection = get_mysqli();

	// Query all statistics
	$result = $connection->multi_query(
		'SELECT T1.senderName,T1.senderReputation FROM trollboxData AS T1, (SELECT MAX(messageNumber) AS messageNumberMax FROM trollboxData WHERE senderName!="marking@poloniex" GROUP BY senderName) AS T2 WHERE T1.messageNumber=T2.messageNumberMax ORDER BY T1.senderReputation DESC LIMIT 20;'
	);
	if(!$result) {
		return;
	}

	// Store top reputation users
	$output = '<h3>Top 20 users with the most reputation</h3>';
	if ($result = $connection->store_result()) {
		$specialUsers = decode_json('/data/specialUsers.txt');

		$output .= '<div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th class="text-right">Reputation</th><th>Username</th></tr></thead><tbody>';

		while ($row = $result->fetch_row()) {
			$userName = htmlspecialchars($row[0]);
			$userReputation = $row[1];

			switch ($specialUsers[$userName]) {
				case 1:
					$userBadgeCode = ' <span title="Moderator" class="badge alert-info">M</span>';
					break;
				case 2:
					$userBadgeCode = ' <span title="Engineer" class="badge alert-warning">E</span>';
					break;
				case 3:
					$userBadgeCode = ' <span title="Administrator" class="badge alert-danger">A</span>';
					break;
				default:
					$userBadgeCode = '';
			}

			$output .= '<tr>' .
			'<td class="text-right"><span title="Reputation" class="badge">' . $userReputation . '</span></td>' .
			'<td><a href="?username=' . $userName . '">' . $userName . '</a>' . $userBadgeCode . '</td>' .
			'</tr>';
		}

		// Free up resources
		$result->free();

		$output .= '</tbody></table></div>';

		// Write the output to a file
		file_put_contents('/data/statistics.html', $output);
	}
?>
