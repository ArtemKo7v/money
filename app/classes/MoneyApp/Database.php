<?php

namespace MoneyApp;

use AmiLabs\DevKit\Application;
use MoneyApp\Locales;

class Database {

    protected $aConfig = array();
    protected $oDrv;
    protected $aPeriod = array();

    public function __construct(){
        $this->aConfig = Application::getInstance()
            ->getConfig()
            ->get(
                'Database',
                array(
                    'host'   => '127.0.0.1',
                    'user'   => 'root',
                    'pass'   => '',
                    'dbname' => 'money'
                )
            );
        $this->oDrv = new \mysqli($this->aConfig['host'], $this->aConfig['user'], $this->aConfig['pass'], $this->aConfig['dbname']);
    }

    public function getCategories($userId, $isExpense = NULL){
        $userId = (int)$userId;
        $aResult = array();
        $sql = "SELECT id, name, is_expense FROM categories WHERE (user_id = 0 OR user_id = {$userId})";
        if(!is_null($isExpense)){
            $isExpense = !!$isExpense;
            $sql = $sql . " AND is_expense=" . ($isExpense ? 1 : 0);
        }
        $sql .= " ORDER BY is_expense, name";
        $query = $this->oDrv->query($sql);
        foreach($query as $row){
            $aResult[] = array('id' => $row['id'], 'name' => $row['name'], 'expense' => $row['is_expense']);
        }
        return $aResult;
    }

    public function addTransaction($userId, $aTx){
        $userId = (int)$userId;

        // @todo: validation
        $sql = "INSERT INTO transactions SET user_id={$userId}, tx_date='{$aTx['date']}', category_id={$aTx['category']}, value='{$aTx['value']}', is_planned='{$aTx['planned']}', created_at=NOW()";
        if(!empty($aTx['title'])){
            $sql .= ", title='{$aTx['title']}'";
        }
        if(!empty($aTx['details'])){
            $sql .= ", details='{$aTx['details']}'";
        }
        $this->oDrv->query($sql);
    }

    public function checkPeriod($userId, $period = FALSE){
        $userId = (int)$userId;
        if(!$period){
            $now = time();
            $month = date('m', $now);
            $year = (int)date('Y', $now);
            $currentPeriod = $year . '-' . $month;
        }else{
            $currentPeriod = $period;
        }
        $balance = 0;
        $sql = "SELECT period, value FROM periods WHERE user_id={$userId} AND period = '{$currentPeriod}' LIMIT 1";
        $result = $this->oDrv->query($sql);
        if(!$result->num_rows){
            // Create new period
            $balance = $this->getPeriodOpenValue($userId, $currentPeriod);
            $sql = "INSERT INTO periods SET period='{$currentPeriod}', value='{$balance}', user_id={$userId}";
            $this->oDrv->query($sql);
            $this->aPeriod = array('period' => $currentPeriod, 'value' => $balance);
        }
    }

    public function getPeriodCloseValue($userId, $period = FALSE){
        $userId = (int)$userId;
        $aMonth = $this->getMonth($userId, $period);
        $balance = 0;
        foreach($aMonth['dates'] as $date => $aDate){
            $balance = $aDate['data-balance'];
        }
        return $balance;
    }

    public function getPeriodOpenValue($userId, $period = FALSE){
        $userId = (int)$userId;
        if(!$period){
            $now = time();
            $month = date('m', $now);
            $year = (int)date('Y', $now);
            $currentPeriod = $year . '-' . $month;
        }else{
            $currentPeriod = $period;
        }
        $balance = 0;
        $sql = "SELECT period, value FROM periods WHERE user_id={$userId} AND period <= '" . $currentPeriod . "' ORDER BY period DESC LIMIT 1";
        $result = $this->oDrv->query($sql);
        if($result->num_rows){
            $row = $result->fetch_assoc();
            if($currentPeriod == $row['period']){
                $balance = $row['value'];
            }else{
                $balance = $this->getPeriodCloseValue($userId, $row['period']);
            }
        }
        return $balance;
    }

    public function getMonth($userId, $period = FALSE){
        $userId = (int)$userId;

        $aResult = array(
            'dates' => array(),
            'maxlen' => 0
        );

        // 1. Get full month dates list
        $time = 0;

//        $period = '2017-03';

        if($period){
            $pair = explode('-', $period);
            if(count($pair) == 2){
                $month = $pair[1];
                $year = $pair[0];
                $time = mktime(0,0,0,(int)$month - 1, 1, (int)$year);
            }
        }
        if(!$time){
            $time = time();
            $month = date('m', $time);
            $year = (int)date('Y', $time);
        }

        $lastDay = (int)date('t', $time); // Last day of the month
        $sqlPeriod = $year . '-' . $month;

        $startBalance = ($sqlPeriod == $this->aPeriod['period']) ? $this->aPeriod['value'] : $this->getPeriodOpenValue($userId, $sqlPeriod);
        $months = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
        $aResult['monthNumber'] = (int)$month;
        $aResult['month'] = Locales::getInstance('ru')->get('common.months.' . $months[(int)$month - 1]);
        $aResult['year'] = $year;
        $maxLen = array('plan' => 1, 'data' => 1);
        for($i=1; $i <= $lastDay; $i++){
            $day = ($i < 10) ? '0' . $i : $i;
            // @todo: date format
            $date = $i;
            $sqlDate = $year . '-' . $month . '-' .$day;
            $ts = mktime(0, 0, 0, $month, $i, $year);
            $dow = date('w', $ts);
            $days = array('Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб');

            $aResult['dates'][$sqlDate] = array(
                'date'          => $date,
                'dayOfWeek'     => date('D', $ts),
                'dayName'       => $days[$dow],
                'plan'          => array(),
                'plan-change'   => 0,
                'plan-balance'  => 0,
                'data'          => array(),
                'data-change'   => 0,
                'data-balance'  => 0
            );
        }

        $actual = $this->_getMonthRecords($userId, $sqlPeriod . '-01');
        foreach($actual as $row){
            $date = $row['tx_date'];
            $cat = $row['category_name'];
            $part = !!(int)$row['is_planned'] ? 'plan' : 'data';
            if(!isset($aResult['dates'][$date][$part][$cat])){
                $aResult['dates'][$date][$part][$cat] = array(
                    'items' => array(),
                    'total' => 0
                );
            }
            if(is_null($row['title'])){
                $row['title'] = $cat;
            }
            $aResult['dates'][$date][$part][$cat]['items'][] = array('title' => $row['title'], 'value' => $row['value']);
            // $aResult['dates'][$date][$part][$cat]['itemsJSON'] = json_encode($aResult['dates'][$date][$part][$cat]['items']);
            $aResult['dates'][$date][$part][$cat]['total'] += $row['value'];
            $aResult['dates'][$row['tx_date']][$part . '-change'] += $row['value'];
            $len = count($aResult['dates'][$row['tx_date']][$part]);
            if($len > $maxLen[$part]){
                $maxLen[$part] = $len;
            }
        }
        foreach(array('plan', 'data') as $part){
            $balance = $startBalance;
            foreach($aResult['dates'] as $date => $aDateResult){
                if($aDateResult[$part . '-change']){
                    $balance += $aDateResult[$part . '-change'];
                }
                $aResult['dates'][$date][$part . '-balance'] = $balance;
            }
            $aResult[$part . '-maxlen'] = $maxLen[$part];
        }
        foreach($aResult['dates'] as $date => $aData){
            $aResult['dates'][$date]['JSON'] = json_encode($aData, JSON_UNESCAPED_UNICODE);
        }
        return $aResult;
    }

    public function getUser($userId){
        $sql = "SELECT login, currency_id FROM `users` WHERE id=" . (int)$userId;
        $result = $this->oDrv->query($sql);
        return ($result && $result->num_rows) ? $result->fetch_assoc() : FALSE;
    }

    public function getUserId($username){
        $sql = "SELECT id FROM `users` WHERE login='" . $this->oDrv->escape_string($username) . "' LIMIT 1";
        $result = $this->oDrv->query($sql);
        $result = ($result && $result->num_rows) ? $result->fetch_row() : FALSE;
        return $result ? $result[0] : $result;
    }

    public function userExists($username){
        $sql = "SELECT 1 FROM `users` WHERE login='" . $this->oDrv->escape_string($username) . "'";
        $result = $this->oDrv->query($sql);
        return ($result && $result->num_rows) ? TRUE : FALSE;
    }

    public function checkUserLP($username, $password){
        $sql = "SELECT id FROM `users` WHERE login='" . $this->oDrv->escape_string($username) . "' AND password='" . md5($password) . "'";
        $result = $this->oDrv->query($sql);
        return ($result && $result->num_rows) ? TRUE : FALSE;
    }

    public function createUser($username, $password){
        if(!$this->userExists($username)){
            $sql = "INSERT INTO `users` SET login='" . $this->oDrv->escape_string($username) . "', password='" . md5($password) . "'";
            $this->oDrv->query($sql);
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @param type $period
     * @return type
     */
    protected function _getMonthRecords($userId, $period = FALSE){
        $userId = (int)$userId;
        $period = $period ? "\"" . $period . "\"" : "NOW()";
        $sql = "SELECT"
                . " t.*, c.name as category_name"
                . " FROM transactions t"
                . " LEFT JOIN categories c"
                . " ON t.category_id = c.id"
                . " WHERE t.user_id={$userId}"
                . " AND YEAR(tx_date) = YEAR({$period})"
                . " AND MONTH(tx_date) = MONTH({$period})"
                . " ORDER BY tx_date";
        return $this->oDrv->query($sql);
    }
}

