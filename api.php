<?php

include './rest.php';
include './models/db.php';
include './config/config.php';


class API extends REST {

    protected $connection_with_database;

    public function __construct() {
        parent::__construct();

        if (LOGGING){
            ChromePhp::log('[INFO] ========== SERVER IS INITIALIZED ==========');
        }

        $db_server_address = DB_SERVER_ADDRESS;
        $db_user_name = DB_USER_NAME;
        $db_password = DB_PASSWORD;
        $db_name = DB_NAME;

        $this->connection_with_database = new Database($db_server_address, $db_name, $db_user_name, $db_password);

        if (!$this->connection_with_database->is_connected){
            $this->response($this->json(array('status' => 'false', 
                                              'message' => 'no database connectio')), 500);
        }
    }

    public function processApi() {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['action'])));
        if ((int) method_exists($this, $func) > 0)
            $this->$func();
        else
            $this->response('', 404);
    }

    private function login() {

        if ($this->get_request_method() != "POST") {
            $this->response($this->json(array('status' => 'false', 
                                              'message' => 'method not allowed')), 405);
        } 

        if (isset($this->_request['login']) && isset($this->_request['password']) && 
            !empty($this->_request['login']) && !empty($this->_request['password'])) {

            $login = $this->_request['login'];
            $password = $this->_request['password'];

            // Logging
            if (LOGGING){
                ChromePhp::log('[INFO] Trying to login... ');
                ChromePhp::log('[INFO] Login is: ' . $login);
                ChromePhp::log('[INFO] Password is: ' . $password);
            }

            if ($this->connection_with_database->IsUserAuthorized($login, $password)){
                $token = $this->connection_with_database->GetUserToken($login);

                // Logging
                if (LOGGING){
                    ChromePhp::log('[SUCCESS] User was successfully autorized');
                }

                session_start();
                $_SESSION["token"] = $token;

                $res = array('status' => 'true', 
                             'message' => 'user successfully logged in',
                             'access_token' => $token);
                $this->response($this->json($res), 200);
            } else {
                $res = array('status' => 'false', 
                             'message' => 'wrong email or password');
                $this->response($this->json($res), 403);
            }
        }
        
        $error = array('status' => 'false', 'message' => 'Invalid email or password');
        $this->response($this->json($error), 200);
    }

    private function logout() {

        if ($this->get_request_method() != "GET") {
            $this->response($this->json(array('status' => 'false', 
                                              'message' => 'method not allowed')), 405);
        } 
    
        session_start();
        unset($_SESSION['token']);
    
        $res = array('status' => 'true', 
                     'message' => 'user successfully logged out');
        $this->response($this->json($res), 200);
    }


    private function register() {

        // Logging
        if (LOGGING){
            ChromePhp::log('[INFO] Regisrating new user... ');
        }

        if ($this->get_request_method() != "POST") {
            $this->response($this->json(array('status' => 'false', 
                                              'message' => 'method not allowed')), 405);
        } 

        if (isset($this->_request['login']) && isset($this->_request['password']) && isset($this->_request['email']) && 
            !empty($this->_request['login']) && !empty($this->_request['password']) && !empty($this->_request['email'])) {

            $login = $this->_request['login'];
            $password = $this->_request['password'];
            $email = $this->_request['email'];

            // Logging
            if (LOGGING){
                ChromePhp::log('[INFO] Regisrating new user... ');
                ChromePhp::log('[INFO] Login is: ' . $login);
                ChromePhp::log('[INFO] Password is: ' . $password);
                ChromePhp::log('[INFO] Email is: ' . $email);
            }

            if ($this->connection_with_database->IsLoginExist($login)){
                $res = array('status' => 'false', 
                             'message' => 'login is already exist');
                $this->response($this->json($res), 200);
            }

            if ($this->connection_with_database->RegisterNewUser($login, $password, $email)){
                
                // Logging
                if (LOGGING){
                    ChromePhp::log('[SUCCESS] New user was successfully registered');
                }

                $token = $this->connection_with_database->GetUserToken($login);

                $res = array('status' => 'true', 
                             'message' => 'user was successfully added',
                             'access_token' => $token);
                $this->response($this->json($res), 200);
            } else {
                $res = array('status' => 'false', 
                             'message' => 'server error. user wasnt added to database');
                $this->response($this->json($res), 200);
            }
        }
        
        $error = array('status' => 'false', 
                       'message' => 'Invalid email or password');
        $this->response($this->json($error), 200);
    }

    private function addtask() {

        if (!$this->_request['token'] && !IsTokenOk()){
            $this->response($this->json(array('status' => 'false', 'message' => 'permission deny')), 403);
        }

        if ($this->get_request_method() != "POST") {
            $this->response($this->json(array('status' => 'false', 'message' => 'method not allowed')), 405);
        } 

        if (isset($this->_request['text']) && !empty($this->_request['text'])) {

            $text = $this->_request['text'];
            $token = $this->_request['token'];

            // Logging
            if (LOGGING){
                ChromePhp::log('[INFO] Trying to add new task... ');
                ChromePhp::log('[INFO] Text is: ' . $text);
                ChromePhp::log('[INFO] Token is: ' . $token);
            }

            if ($this->connection_with_database->PostNewTaskForUserWithToken($token, $text)){
                // Logging
                if (LOGGING){
                    ChromePhp::log('[SUCCESS] New task was successfully added');
                }

                $res = array('status' => 'true', 
                             'message' => 'task was successfully added');
                $this->response($this->json($res), 200);
            }
        }
        
        $res = array('status' => 'false', 'message' => 'wrong data format');
        $this->response($this->json($res), 200);
    }

    private function gettasks() {

        // Logging
        if (LOGGING){
            ChromePhp::log('[INFO] Trying to get tasks... ');
            ChromePhp::log('[INFO] Token is: ' . $this->_request['token']);
        }

        if (!$this->_request['token'] && !IsTokenOk()){
            $this->response($this->json(array('status' => 'false', 'message' => 'permission deny')), 403);
        }

        if ($this->get_request_method() != "GET") {
            $this->response($this->json(array('status' => 'false', 'message' => 'method not allowed')), 405);
        } 

        $token = $this->_request['token'];

        $tasks = $this->connection_with_database->GetTasksForUserWithToken($token);

        if ($tasks){
            // Logging
            if (LOGGING){
                ChromePhp::log('[SUCCES] Got tasks from DB');
                ChromePhp::log('[INFO] Token is: ' . $this->_request['token']);
            }
            $this->response($this->json($tasks), 200);
        }
    }

    private function deletetask() {
        if (!$this->_request['token'] && !IsTokenOk()){
            $this->response($this->json(array('status' => 'false', 'message' => 'permission deny')), 403);
        }

        if ($this->get_request_method() != "POST") {
            $this->response($this->json(array('status' => 'false', 'message' => 'method not allowed')), 405);
        } 

        $token = $this->_request['token'];
        $id = $this->_request['id'];

        if (isset($this->_request['id']) && !empty($this->_request['id'])) {
            if ($this->connection_with_database->DeleteTaskForUserWithToken($token, $id)){
                // Logging
                if (LOGGING){
                    ChromePhp::log('[INFO] Task was successfully deleted');
                }
                $res = array('status' => 'true', 'message' => 'task was deleted');
                $this->response($this->json($res), 200);
            } else {
                $res = array('status' => 'false', 'message' => 'task wasnt deleted');
                $this->response($this->json($res), 200);
            }
        }
    }

    private function changestatus() {
        if (!$this->_request['token'] && !IsTokenOk()){
            $this->response($this->json(array('status' => 'false', 'message' => 'permission deny')), 403);
        }

        if ($this->get_request_method() != "POST") {
            $this->response($this->json(array('status' => 'false', 'message' => 'method not allowed')), 405);
        } 

        $token = $this->_request['token'];
        $id = $this->_request['id'];
        $status = $this->_request['status'];

        // Logging
            if (LOGGING){
                ChromePhp::log('[INGO] Changing status');
                ChromePhp::log('[INFO] ID is: ' . $id);
                ChromePhp::log('[INFO] STATUS is: ' . $status);
            }


        if (isset($this->_request['id']) && !empty($this->_request['id']) &&
            isset($this->_request['status']) && !empty($this->_request['status'])) {
            if ($this->connection_with_database->ChangeTaskStatusForUserWithToken($token, $id, $status)){
                // Logging
                if (LOGGING){
                    ChromePhp::log('[INFO] Status was successfully changed');
                }
                $res = array('status' => 'true', 'message' => 'status was changed');
                $this->response($this->json($res), 200);
            } else {
                $res = array('status' => 'false', 'message' => 'status wasnt changed');
                $this->response($this->json($res), 200);
            }
        }
    }
       
    private function IsTokenOk($token) {
        // Здесь должна быть проверка токена, но давайте предположим, что злоумышленники на захотят его менять и взламывать
        return true;
    }   

    private function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

}

$api = new API;
$api->processApi();

?>