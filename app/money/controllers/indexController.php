<?php

use AmiLabs\DevKit\Controller;
use AmiLabs\DevKit\Registry;
use AmiLabs\DevKit\Cache;

use MoneyApp\Database;
use MoneyApp\Auth;
use MoneyApp\Locales;

/**
 *
 */
class indexController extends Controller {

    private $oDB;
    private $oAuth;

    public function __construct(){
        $this->oDB = new Database();
        $this->oAuth = new Auth();

        parent::__construct();

        if($this->oAuth->isLoggedIn()){
            $userId = $this->oAuth->getUserId();
            $this->oDB->checkPeriod($userId);
            $this->getView()->set('lastBalance', $this->oDB->getPeriodCloseValue($userId), TRUE);
        }
        $this->getView()->set('oLocales', Locales::getInstance('ru'), TRUE);
        $this->getView()->set('oAuth', $this->oAuth, TRUE);
    }

    public function actionIndex(){

    }

    public function actionMonth(){
        $this->getView()->set('aMonthData', $this->oDB->getMonth($this->oAuth->getUserId()));
    }

    public function actionAdd(){
        $userId = $this->oAuth->getUserId();
        $oRequest = $this->getRequest();
        if('POST' === $oRequest->getMethod()){
            $aTx = array(
                'date'      => $oRequest->get('date'),
                'category'  => $oRequest->get('category'),
                'value'     => $oRequest->get('amount'),
                'title'     => $oRequest->get('title'),
                'details'   => $oRequest->get('description'),
                'planned'   => $oRequest->get('to_plan', 0),
            );
            // @todo: check result
            $this->oDB->addTransaction($userId, $aTx);
            die('OK');
        }
        $this->getView()->set('date', $oRequest->get('date', FALSE));
        $this->getView()->set('aIncomeCategories', $this->oDB->getCategories($userId, FALSE));
        $this->getView()->set('aExpenseCategories', $this->oDB->getCategories($userId, TRUE));
    }

    public function actionLogin(){
        $result = '';
        $oRequest = $this->getRequest();
        if('POST' === $oRequest->getMethod()){
            $username = $oRequest->get('username');
            $password = $oRequest->get('password');
            if($this->oAuth->login($username, $password)){
                $result = 'OK';
            }
        }
        die($result);
    }

    public function actionLogout(){
        $this->oAuth->logout();
        die();
    }
}