<?php 
	include './additional files/ChromePhp.php';
	include './config/config.php';

	session_start();

	if (LOGGING){
		ChromePhp::log('[INFO] Session token: ' . $_SESSION["token"]);
	}

	if(!isset($_SESSION["token"])){
		header('Location: auth.php');
	}
?>

<html lang="ru">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="css/index.css" type="text/css">
		<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
	</head>
	<body>
		<div id="tasker" class="tasker">
			<div id="error" class="error">Please enter a task</div>
			<div id="tasker-header" class="tasker-header">
				<input id="input-task" type="text" placeholder="Введите задачу">
				<button id="add-task-btn">Добавить</button>
			</div>
			<div class="tasker-body">
				<ul id="tasks"></ul>
			</div>
			<div class="tasker-logout">
				<button id="logout">Выйти из системы</button>
			</div>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
      	<script type="text/javascript" src="js/index.js" ></script>

	</body>
</html>