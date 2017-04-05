<?php
namespace MoneyApp;

use AmiLabs\DevKit\Application;

use MoneyApp\Database;

class Auth {

    protected $oDB;
    protected $userId = FALSE;
    protected $aUser = array();

    public function __construct(){
        $this->oDB = new Database();
        session_start();
        if(isset($_SESSION['userId'])){
            $this->userId = $_SESSION['userId'];
        }
        if($this->userId){
            $this->aUser = $this->oDB->getUser($this->userId);
        }

        // Register:
        // $this->oDB->createUser($email, $password);
    }

    /**
     * Is user logged in.
     *
     * @return bool
     */
    public function isLoggedIn(){
        return !!$this->getUserId();
    }

    public function getUser(){
        return $this->aUser;
    }

    public function getUserId(){
        return $this->userId;
    }

    public function login($username, $password){
        $result = FALSE;
        if($this->oDB->checkUserLP($username, $password)){
            $_SESSION['userId'] = $this->oDB->getUserId($username);
            session_commit();
            $result = TRUE;
        }
        return $result;
    }

    public function logout(){
        $_SESSION['userId'] = FALSE;
        session_commit();
    }
}