<?php

namespace Citfact\SiteCore\Tools\WebService;

use Bitrix\ImConnector\Library;
use mysql_xdevapi\Exception;
use const CURLAUTH_BASIC;
use const CURLOPT_HTTPAUTH;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_PROXY;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_URL;
use const CURLOPT_USERAGENT;
use const CURLOPT_USERPWD;
use const FILE_APPEND;
use const LOCK_EX;

class CurlWebService
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const AUTO_EXTENSION = 'AUTO_EXTENSION';

    /**
     * @var array Дополнительные опции Curl.
     */
    protected $options = [];
    protected $headers = [];

    /**
     * @var resource Экземпляр класса Curl.
     */
    protected $chRes;
    protected $statusCode;
    protected $downloadFilePath;
    protected $curlError = null;

    /**
     * Api constructor.
     */
    public function __construct()
    {
        if (function_exists('curl_init')) {
            $this->chRes = curl_init();

            $this->options[CURLOPT_SSL_VERIFYPEER] = false;
            $this->options[CURLOPT_RETURNTRANSFER] = true;
        } else {
            $this->debug('CURL IS NOT DEFINED');
        }
    }

    /**
     * @param $data
     */
    protected function debug($data)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/var/logs/' . 'webservice' . date('Y-m-d') . '.log', print_r("\n" . date('H:i:s '), true), FILE_APPEND | LOCK_EX);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/var/logs/' . 'webservice' . date('Y-m-d') . '.log', print_r($data, true), FILE_APPEND | LOCK_EX);
    }

    /**
     * Метод, устанавливающий агент пользователя.
     *
     * @param string $userAgent Название клиентского приложения.
     */
    public function setUserAgent(string $userAgent)
    {
        $this->options[CURLOPT_USERAGENT] = $userAgent;
    }

    /**
     * Метод, устанавливающий прокси-сервер.
     *
     * @param string $proxy Прокси-сервер.
     */
    public function setProxy(?string $proxy)
    {
        if (!$proxy) {
            return;
        }
        $this->options[CURLOPT_PROXY] = $proxy;
    }

    /**
     * Установить заголовок
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Очистить заголовки
     */
    public function clearHeaders()
    {
        $this->headers = [];
    }

    /**
     * Метод, устанавливающий данные авторизации.
     *
     * @param string $login Логин для аутентификации..
     * @param string $password Пароль для аутентификации..
     */
    public function auth(string $login, string $password)
    {
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $this->options[CURLOPT_USERPWD] = "$login:$password";
    }

    /**
     * Метод для выполнения запроса по ссылке.
     *
     * @param string $url Удалённый ресурс.
     * @param array|string $params Тело запроса.
     *
     * @param string $method Название метода (GET, POST, PUT).
     * @return mixed
     */
    protected function request(string $url, $params = [], string $method = self::METHOD_POST, $type = null)
    {
        if (!function_exists('curl_setopt')) {
            exit();
        }

        curl_reset($this->chRes);

        if ($method === self::METHOD_POST) {
            curl_setopt($this->chRes, CURLOPT_POST, true);
            curl_setopt($this->chRes, CURLOPT_POSTFIELDS, $params);
        } elseif ($method === self::METHOD_PUT) {
            curl_setopt($this->chRes, CURLOPT_PUT, true);
            curl_setopt($this->chRes, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif ($params) {
            curl_setopt($this->chRes, CURLOPT_HTTPGET, true);
            $url .= '?' . http_build_query($params);
        }
        curl_setopt($this->chRes, CURLOPT_URL, $url);

        curl_setopt_array($this->chRes, $this->options);
        curl_setopt($this->chRes, CURLOPT_HTTPHEADER, $this->getHeadersArray());


        $this->debug('$url');
        $this->debug($url);
        $this->debug('$this->options');
        $this->debug($this->options);
        $this->debug('$this->headers');
        $this->debug($this->headers);
        $this->debug('$params');
        $this->debug($params);
        $response = curl_exec($this->chRes);
        $this->debug('$response');
        $this->debug($response);

        if (strpos($response, '500 Internal Server Error') !== false) {
            $this->debug(date('d.m.Y H:i:s ') . "500 Internal Server Error \n");
        }
        $this->curlError = curl_error($this->chRes);
        if ($this->curlError) {
            $this->debug('$error');
            $this->debug($this->curlError);
        }

        $this->statusCode = curl_getinfo($this->chRes, CURLINFO_HTTP_CODE);

        return $response;
    }

    public function download(string $url, $filePath = false, $fileExtension = null)
    {
        if (!function_exists('curl_setopt')) {
            exit();
        }
        curl_setopt($this->chRes, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->chRes, CURLOPT_URL, $url);
        curl_setopt_array($this->chRes, $this->options);
        curl_setopt($this->chRes, CURLOPT_HTTPHEADER, $this->getHeadersArray());

        $this->debug('download $url');
        $this->debug($url);
        $this->debug('$this->options');
        $this->debug($this->options);
        $this->debug('$this->headers');
        $this->debug($this->headers);
        $response = curl_exec($this->chRes);
        $mimeType = curl_getinfo($this->chRes, CURLINFO_CONTENT_TYPE);
        $this->debug('strlen($response)');
        $this->debug(strlen($response));

        if ($fileExtension === self::AUTO_EXTENSION) {
            $fileExtension = $this->getExtensionByMime($mimeType);
        }
        $fileExtension = $this->prepareExtension($fileExtension);

        if ($filePath) {
            $this->downloadFilePath = $filePath . $fileExtension;
            file_put_contents($this->downloadFilePath, $response);
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=download' . time() . $fileExtension);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . strlen($response));
            ob_clean();
            flush();
            echo $response;
            flush();
        }
        if (strpos($response, '500 Internal Server Error') !== false) {
            $this->debug(date('d.m.Y H:i:s ') . "500 Internal Server Error \n");
        }
        $error = curl_error($this->chRes);
        if ($error) {
            $this->debug('$error');
            $this->debug($error);
        }
        $this->statusCode = curl_getinfo($this->chRes, CURLINFO_HTTP_CODE);
        $this->debug('$this->statusCode');
        $this->debug($this->statusCode);
        if ($this->statusCode == 200) {
            return ['success' => true];
        } else {
            return ['error' => "Status Code: $this->statusCode"];
        }
    }

    public function getStatusCode()
    {
        return intval($this->statusCode);
    }

    public function getDownloadFilePath()
    {
        return $this->downloadFilePath;
    }

    /**
     * Api destructor.
     */
    public function __destruct()
    {
        if (function_exists('curl_close')) {
            curl_close($this->chRes);
        }
    }

    /**
     * Получить массив заголовков для использования в curl
     *
     * @return array
     */
    protected function getHeadersArray()
    {
        $headers = [];
        foreach ($this->headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }
        return $headers;
    }

    /**
     * @param string $mimeType
     * @return string
     */
    protected function getExtensionByMime(?string $mimeType): ?string
    {
        return Library::$mimeTypeAssociationExtension[$mimeType] ?: '';
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function prepareExtension(?string $extension): string
    {
        return $extension ? str_replace('..', '.', '.' . $extension) : '';
    }
}
