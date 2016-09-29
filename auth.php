<?php
	include './additional files/ChromePhp.php';
	include './config/config.php';

	session_start();

	if (LOGGING){
		ChromePhp::log('[INFO] Session token: ' . $_SESSION["token"]);
	}

	if(isset($_SESSION["token"])){
		header('Location: index.php');
	}
?>

<html lang="ru">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="css/login.css" type="text/css">
	</head>
	<body>
		<div class="login-page">
			<div class="form">
				<div id="info"></div>
		    	<form class="register-form">
		      		<input id="registration-login" type="login" placeholder="Логин"/>
		      		<input id="registration-password" type="password" placeholder="Пароль"/>
		      		<input id="registration-email" type="email" placeholder="Email"/>
		      		<button id="register" type="button"> Зарегистророваться </button>
		      		<p class="message"> Уже зарегистрированы? <a href="#">Войти</a></p>
		    	</form>
		    	<form class="login-form">
		      		<input id="login-login" name="username" type="text" placeholder="Логин"/>
		      		<input id="login-password" name="password" type="password" placeholder="Пароль"/>
		      		<button id="login" type="button"> Войти </button>
		      		<p class="message"> Еще не зарегистрированы? <a href="#"> Создать аккаунт </a></p>
		    	</form>
		  	</div>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
      	<script type="text/javascript" src="js/login.js" ></script>
	</body>
</html>