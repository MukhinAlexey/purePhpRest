<?php

include './additional files/ChromePhp.php';
include './config/config.php';

class Database{

	public $is_connected = false;
	protected $connection;


	public function __construct($server_name, $database_name, $login, $password){

		mysqli_report(MYSQLI_REPORT_STRICT);

		try {
     		$this->connection = mysqli_connect($server_name, $login, $password);
		} catch (Exception $e ) {
     		ChromePhp::log('[ERROR] Can not connect to MySQL');
			ChromePhp::log('[ERROR] Error number: ' . mysqli_connect_errno());
			return false;
		}

		mysqli_set_charset($this->connection , "utf8");
		
		if (LOGGING){
			ChromePhp::log('[INFO] Coneting to database with name: ' . $database_name);
		}

		mysqli_select_db($this->connection, $database_name);

		if (LOGGING){
			ChromePhp::log('[SUCCESS] Connected to MySQL' . PHP_EOL);
			ChromePhp::log('[INFO] Server address: ' . mysqli_get_host_info($this->connection) . PHP_EOL);
		}

		$this->is_connected = true;
		return true;
	}

	public function Disconnect(){
		// Close connection
		mysqli_close($this->connection);
		$this->is_connected = false;

		if (LOGGING){
			ChromePhp::log('[SUCCESS] Disconnectes from MySQL' . PHP_EOL);
		}
	}


	public function IsLoginExist($login){
		$login = mysqli_real_escape_string($this->connection, $login);

		$result = mysqli_query($this->connection, "SELECT * FROM users WHERE login='$login';");
		$returned_rows_num = mysqli_num_rows($result);

		if ($returned_rows_num > 0){
			return true;
		} 
		return false;
	}


	public function IsUserAuthorized($login, $password){
		$login = mysqli_real_escape_string($this->connection, $login);
		$password = mysqli_real_escape_string($this->connection, $password);

		$crypt_password = md5($password);
		$result = mysqli_query($this->connection, "SELECT * 
												   FROM users 
												   WHERE login='$login' AND password='$crypt_password';");
		$returned_rows_num = mysqli_num_rows($result);

		if ($returned_rows_num > 0){
			return true;
		} 
		return false;
	}


	public function GetUserToken($login){
		$login = mysqli_real_escape_string($this->connection, $login);


		$result = mysqli_query($this->connection, "SELECT id 
												   FROM users 
												   WHERE login='$login';");
		$row = mysqli_fetch_assoc($result);
		$id_user = $row['id'];

		$result = mysqli_query($this->connection, "SELECT token 
												   FROM sessions AS S 
												   LEFT JOIN users_to_sessions AS UTS 
												   ON S.id = UTS.id_session WHERE id_user='$id_user';");
		$row = mysqli_fetch_assoc($result);

		return $row['token'];
	
	}


	public function RegisterNewUser($login, $password, $email){
		$login = mysqli_real_escape_string($this->connection, $login);
		$password = mysqli_real_escape_string($this->connection, $password);
		$email = mysqli_real_escape_string($this->connection, $email);

		$crypt_password = md5($password);
		if (mysqli_query($this->connection, "INSERT INTO users (login, email, password) 
										     VALUES ('$login', '$email', '$crypt_password');")){

			$result = mysqli_query($this->connection, "SELECT LAST_INSERT_ID() AS id;");
			$row = mysqli_fetch_assoc($result);
			$id_user = $row['id'];

			// Здесь должен быть крутой и умный код генерации токена, который еще и со временем обновляется, но сдесь он глупый
			// И да, может получиться у двух пользователей одинаковый токен, но, наверное, вероятность не так велика в масштабе 10-20 пользователей
			// Я посторался передать лишь суть работы системы авторизации
			// P.S. Я понимаю, что она дырявая
			$token_to_insert = rand();

			$result = mysqli_query($this->connection, "INSERT INTO sessions (token) 
													   VALUES ('$token_to_insert');");

			$result = mysqli_query($this->connection, "SELECT LAST_INSERT_ID() AS id;");
			$row = mysqli_fetch_assoc($result);
			$id_session = $row['id'];

			mysqli_query($this->connection, "INSERT INTO users_to_sessions (id_user, id_session) 
											 VALUES ('$id_user','$id_session');");

			return true;
		} else {
			return false;
		}
	}

	public function PostNewTaskForUserWithToken($token, $text){
		$token = mysqli_real_escape_string($this->connection, $token);
		$text = mysqli_real_escape_string($this->connection, $text);

		$result = mysqli_query($this->connection, "SELECT id_user 
												   FROM sessions AS S 
												   LEFT JOIN users_to_sessions AS UTS 
												   ON S.id = UTS.id_session WHERE token='$token';");
		$row = mysqli_fetch_assoc($result);
		$id_user = $row['id_user'];

		$result = mysqli_query($this->connection, "INSERT INTO tasks (text, status) 
										 		   VALUES ('$text', 'false');");
		$result = mysqli_query($this->connection, "SELECT LAST_INSERT_ID() AS id;");
		$row = mysqli_fetch_assoc($result);
		$id_task = $row['id'];

		$result = mysqli_query($this->connection, "INSERT INTO users_to_tasks (id_user, id_task) 
										 		   VALUES ('$id_user', '$id_task');");

		return true;		
	}

	public function GetTasksForUserWithToken($token){
		$token = mysqli_real_escape_string($this->connection, $token);

		if (LOGGING){
			ChromePhp::log('[INFO] Session token: ' . $token);
		}

		$result = mysqli_query($this->connection, "SELECT id_task, text, status
												   FROM sessions AS S 
												   JOIN users_to_sessions AS UTS 
												   ON S.id = UTS.id_session 
												   JOIN users_to_tasks AS UTT
												   ON UTT.id_user = UTS.id_user
												   JOIN tasks AS T
												   ON T.id = UTT.id_task
												   WHERE token='$token';");

		$tasks = array();

		if (mysqli_num_rows($result) > 0){
			while($row = mysqli_fetch_array($result)) {
      			array_push($tasks, $row);
   			}
   		}
   		
		return $tasks;		
	}

	public function DeleteTaskForUserWithToken($token, $id){
		if (LOGGING){
			ChromePhp::log('[INFO] ID of deleting task: ' . $id);
		}

		$token = mysqli_real_escape_string($this->connection, $token);
		$id = mysqli_real_escape_string($this->connection, $id);

		if ((mysqli_query($this->connection, "DELETE FROM tasks  
											  WHERE id = '$id';")) &&
			(mysqli_query($this->connection, "DELETE FROM users_to_tasks  
											  WHERE id_task = '$id';"))){
			return true;		
		} else {
			return false;
		}
	}

	public function ChangeTaskStatusForUserWithToken($token, $id, $status){
		if (LOGGING){
			ChromePhp::log('[INFO] ID of changing status task: ' . $id);
		}

		$token = mysqli_real_escape_string($this->connection, $token);
		$id = mysqli_real_escape_string($this->connection, $id);
		$status = mysqli_real_escape_string($this->connection, $status);

		if (mysqli_query($this->connection, "UPDATE tasks
											 SET status='$status'
											 WHERE id='$id'")){
			return true;		
		} else {
			return false;
		}
	}
}

?>