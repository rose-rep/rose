<?php

namespace Citfact\SiteCore\Tools\WebService;

use Citfact\SiteCore\Core;
use const FILE_APPEND;
use const LOCK_EX;
use const SOAP_SINGLE_ELEMENT_ARRAYS;

abstract class SoapClientService
{
    protected $client;
    protected $wsdl = '';
    protected $options = [];
    protected $lastError;
    protected $errors = [];

    public function __construct()
    {
        $this->errors = [];
    }

    public function init()
    {
        if (!$this->wsdl) {
            $this->lastError = 'Empty wsdl param.';
            return;
        }
        try {
            $options = array_merge($this->options,
                [
                    'trace' => true,
                    'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                ]
            );
            $this->client = new \SoapClient(
                $this->wsdl,
                $options
            );

        } catch (\SoapFault $e) {
            $this->catchException($e);
        }
    }

    protected function sendRequest($method, $params = null)
    {
        if (!$this->client) {
            $this->init();
        }
        $this->lastError = null;
        try {
            if (empty($this->client)) {
                throw new \Exception('Ошибка клиента: ' . $this->lastError);
            }
            $response = $this->client->__soapCall(
                $method,
                [$params]
            );
            $response = $this->parseResponse($response);
            return $response;
        } catch (\SoapFault $e) {
            $this->catchException($e);
        } catch (\Exception $e) {
            $this->catchException($e);
        }

        return '';
    }

    protected function parseResponse($response)
    {
        return $response;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    protected function getAllErrors()
    {
        return $this->errors;
    }

    protected function dump($data)
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/local/var/logs/';
        file_put_contents($dir . 'soap' . date('Y-m-d') . '.log', print_r("\n" . date('H:i:s d.m.Y '), true), FILE_APPEND | LOCK_EX);
        file_put_contents($dir . 'soap' . date('Y-m-d') . '.log', print_r($data, true), FILE_APPEND | LOCK_EX);
    }

    /**
     * @param \Exception $e
     */
    protected function catchException($e)
    {
        $this->lastError = $e->getMessage();
        $this->errors[] = $this->lastError;
        $this->dump(' $this->lastError ');
        $this->dump($this->lastError);
    }
}
