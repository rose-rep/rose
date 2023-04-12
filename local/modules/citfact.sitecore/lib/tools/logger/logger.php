<?php

namespace Citfact\Tools\Logger;

use Bitrix\Main\Diag\Debug;


class Logger
{
    protected $strStatus = '';
    protected $strLog = '';
    protected $logPath;
    protected $logName;


    public function __construct()
    {
        $this->logPath = '';
        $this->logName = "log_".date('Y-m-d_H-i-s').".log";
    }


    public function addToStatus($str)
    {
        $this->strStatus .= (string)$str . "\n";
    }


    public function showStatus()
    {
        echo $this->strStatus;
    }


    public function addToLog($str)
    {
        $this->strLog .= (string)$str . "\n";
        Debug::writeToFile((string)$str, "".date(' Y-m-d H:i:s'), $this->logPath . "/" . $this->logName);
    }


    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;
    }


    public function setLogName($logName)
    {
        $this->logName = $logName.".log";
    }


    public function getStatus()
    {
        return $this->strStatus;
    }

}