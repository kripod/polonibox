<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<meta name="description" content="Easily check the legitimacy of Poloniex trollbox users and prove the existence of conversations, avoiding scammers to gain reputation.">
		<meta name="keywords" content="PoloniBox, Poloniex, Trollbox, Chat, Log, Exchange, Cryptocurrency">
		<meta name="author" content="Jojatekok">

		<title>PoloniBox - Insights to the logs of Poloniex Exchange's chat</title>

		<link rel="stylesheet" href="css/bootstrap.css">
		<link rel="stylesheet" href="css/bootstrap-theme.css">

		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/linkify.js"></script>
		<script type="text/javascript" src="js/linkify-jquery.js"></script>
	</head>

	<body>
		<div class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<a class="navbar-brand" href="/">PoloniBox</a>
				</div>

				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a href="#messages" data-toggle="tab"><span class="small glyphicon glyphicon-list"></span> Messages</a></li>
						<li><a href="#search" data-toggle="tab"><span class="small glyphicon glyphicon-search"></span> Search</a></li>
						<li><a href="#stats" data-toggle="tab"><span class="small glyphicon glyphicon-stats"></span> Statistics</a></li>
						<li><a href="#about" data-toggle="tab"><span class="small glyphicon glyphicon-info-sign"></span> About</a></li>
					</ul>

					<ul class="nav navbar-nav navbar-right">
						<li><a href="//github.com/Jojatekok" target="_blank">&copy; Jojatekok</a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="tab-content">
				<div class="tab-pane fade in active" id="messages">
					<div class="notification alert alert-info text-center" id="notification-container">
						<span id="notification-container-message-count">0</span>
						new messages - Click to view them!
					</div>

					<div class="row" data-linkify="this">
						<?php require 'lib/printChatLog.php'; ?>
					</div>
				</div>

				<div class="tab-pane fade" id="search">
					<div class="row">
						<div class="col-md-12">
							<form class="form-horizontal" action="lib/search.php" method="post">
								<div class="form-group">
									<label for="searchCondition" class="col-lg-2 control-label">Search conditions</label>
									<div class="col-lg-10">
										<select class="form-control" name="searchCondition" id="searchCondition">
											<option value="and">[AND] Apply every condition at once</option>
											<option value="or">[OR] Apply conditions separately</option>
										</select>
									</div>
								</div>

								<div class="form-group">
									<label for="messageText" class="col-lg-2 control-label">Message</label>
									<div class="col-lg-10">
										<input type="text" class="form-control" name="messageText" id="messageText">
									</div>
								</div>

								<div class="form-group">
									<label for="username" class="col-lg-2 control-label">Sender name</label>
									<div class="col-lg-10">
										<input type="text" class="form-control" name="username" id="username">
									</div>
								</div>

								<div class="form-group">
									<div class="col-lg-10 col-lg-offset-2">
										<button class="btn btn-primary" type="submit">Search</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="stats">
					<div class="row">
						<div class="col-md-12">
							<?php require 'data/statistics.html'; ?>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="about">
					<div class="row">
						<div class="col-md-12">
							<h2>Disclaimer</h2>

							<p>PoloniBox is a place where you can find a lot of useful information about the legitimacy of <a href="//poloniex.com" target="_blank">Poloniex</a> trollbox users. The service was originally made with a purpose of avoiding scammers to gain reputation, and prove the existence of conversations.</p>
							<p>I am not responsible for any kind of inappropriate content which may appear in the chat log. There is no censorship here, every message was written as-is by the members of Poloniex.</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="js/bootstrap.js"></script>
	</body>
</html>
