<?php

namespace Citfact\SiteCore\Tools\Exception;

use Bitrix\Main\Diag\FileExceptionHandlerLog;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Mail\Mail;

class BaseExceptionHandlerLog extends FileExceptionHandlerLog
{
    /**
     * @var string
     */
    protected $email;

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        parent::initialize($options);

        if (!empty($options['email'])) {
            $this->email = $options['email'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($exception, $logType)
    {
        parent::write($exception, $logType);

        if (!empty($this->email)) {
            $exceptionLog = ExceptionHandlerFormatter::format($exception);
            $exceptionLog = date("Y-m-d H:i:s") . " - Host: " . $_SERVER["HTTP_HOST"] . " - " . static::logTypeToString($logType) . " - " . $exceptionLog . "\n";
            $exceptionLog .= "Request uri: " . $_SERVER['REQUEST_URI'] . "\n";

            global $USER;
            if ($USER){
                $exceptionLog .= "User ID: " . $USER->GetID() . "\n";
            }

            $mailParams = array(
                'TO' => $this->email,
                'CHARSET' => 'utf8',
                'CONTENT_TYPE' => 'text/plain',
                'SUBJECT' => sprintf('HandlerLog: Host %s', $_SERVER["HTTP_HOST"]),
                'BODY' => $exceptionLog,
                'HEADER' => array(),
            );

            Mail::send($mailParams);
        }
    }
}
