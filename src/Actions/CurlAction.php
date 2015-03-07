<?php

namespace Fucoso\Curl\Actions;

use Closure;
use Fucoso\Curl\Curl;

class CurlAction
{

    /**
     * Accessible Cookie Path where cookies are meant to be stored.
     * Make sure that this path is readable & writeable.
     * If this value is not set by the calling application, CURL won't
     * use cookies.
     *
     * @var string
     */
    private $cookiePath = null;

    /**
     * Cookie file name in which cookie information would be stored.
     *
     * @var string
     */
    private $cookieFile = null;

    /**
     * Timeout for a failed connection.
     *
     * @var int
     */
    private $connectTimeout = 15;

    /**
     * Curl process timeout.
     *
     * @var int
     */
    private $curlTimeout = 30;

    /**
     *
     * @var Closure
     */
    private $logMessageCall;

    public function getCookiePath()
    {
        return $this->cookiePath;
    }

    public function getCookieFile()
    {
        return $this->cookieFile;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    public function getCurlTimeout()
    {
        return $this->curlTimeout;
    }

    public function getlogMessageCall()
    {
        return $this->logMessageCall;
    }

    public function setCookiePath($cookiePath)
    {
        $this->cookiePath = $cookiePath;
        return $this;
    }

    public function setCookieFile($cookieFile)
    {
        $this->cookieFile = $cookieFile;
        return $this;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function setCurlTimeout($curlTimeout)
    {
        $this->curlTimeout = $curlTimeout;
        return $this;
    }

    public function setLogMessageCall(Closure $outputCall)
    {
        $this->logMessageCall = $outputCall;
        return $this;
    }

    /**
     * Send a HEAD request to the given URL to get the information only for the url.
     *
     * @param string $url
     * @return boolean|null
     */
    public function infoFromURL(&$url)
    {

        $curl = new Curl($url);
        $curl->options->SSL_VERIFYPEER = false;
        $curl->options->FOLLOWLOCATION = true;
        $curl->options->NOBODY = true;
        $curl->options->HEADER = true;
        $curl->options->FILETIME = true;
        $curl->options->CONNECTTIMEOUT = $this->connectTimeout;
        $curl->options->TIMEOUT = $this->curlTimeout;

        if ($this->cookiePath && $this->cookieFile) {
            $this->cookieFile = $this->cookiePath . "{$this->cookieFile}";
            $curl->options->COOKIEFILE = $this->cookieFile;
            $curl->options->COOKIEJAR = $this->cookieFile;
        }

        $headers = array(
            'DNT: 1',
        );
        $curl->options->HTTPHEADER = $headers;

        $curl->execute();

        return $curl;
    }

    /**
     * Get a URL through curl and return a {@link Curl} object.
     *
     * @param string $url
     * @return self
     */
    public function getURL(&$url)
    {

        $curl = new Curl($url);
        $curl->options->SSL_VERIFYPEER = false;
        $curl->options->FOLLOWLOCATION = true;
        $curl->options->CONNECTTIMEOUT = $this->connectTimeout;
        $curl->options->TIMEOUT = $this->curlTimeout;

        if ($this->cookiePath && $this->cookieFile) {
            $this->cookieFile = $this->cookiePath . "{$this->cookieFile}";
            $curl->options->COOKIEFILE = $this->cookieFile;
            $curl->options->COOKIEJAR = $this->cookieFile;
        }

        $headers = array(
            'DNT: 1',
        );
        $curl->options->HTTPHEADER = $headers;

        $curl->execute();
        $url = $curl->effectiveURL();

        if ($curl->isSuccessful()) {
            return $curl;
        } else {
            if ($curl->isForbidden()) { //Forbidden
                return null; // null means a forbidden request. This is to help with decision making for forbidden responses.
            } else {
                return false; // false means all other failed requests.
            }
        }
    }

    /**
     * Post To a URL through curl and return a {@link Curl} object.
     *
     * @param string $url
     * @return self
     */
    public function postURL(&$url, $data = false)
    {

        $curl = new Curl($url);
        $curl->options->SSL_VERIFYPEER = false;
        $curl->options->CONNECTTIMEOUT = $this->connectTimeout;
        $curl->options->TIMEOUT = $this->curlTimeout;

        if ($this->cookiePath && $this->cookieFile) {
            $this->cookieFile = $this->cookiePath . "{$this->cookieFile}";
            $curl->options->COOKIEFILE = $this->cookieFile;
            $curl->options->COOKIEJAR = $this->cookieFile;
        }

        if ($data && ((is_array($data) && count($data) > 0) || (is_string($data) && trim($data) != ''))) {
            $curl->options->POST = true;
            $curl->options->POSTFIELDS = $data;
        }

        $curl->execute();

        if ($curl->isSuccessful()) {
            return $curl;
        } else {
            if ($curl->isForbidden()) { //Forbidden
                return null; // null means a forbidden request. This is to help with decision making for forbidden responses.
            } else {
                return false; // false means all other failed requests.
            }
        }
    }

    /**
     * Get a URL through curl and return the response as string.
     *
     * @param string $url
     * @return string|boolean|null
     */
    public function getURLString(&$url)
    {
        $curl = $this->getURL($url);
        if ($curl && $curl->isSuccessful()) {
            return $curl->response();
        } else {
            return $curl;
        }
    }

    /**
     * Calculate the file size of a local file, this function was written to avoid overhead in php filesize()
     * function.
     *
     * @param string $destination
     * @return boolean
     */
    protected function getLocalFileSize($destination)
    {
        $fileHandle = fopen($destination, 'r+');
        if ($fileHandle !== false) {
            fseek($fileHandle, 0, SEEK_END);
            $size = ftell($fileHandle);
            fclose($fileHandle);
            if ($size === false) {
                return false;
            } else {
                return $size;
            }
        } else {
            return false;
        }
    }

    protected function logMessage($message)
    {
        if (is_callable($this->getlogMessageCall())) {
            $outputCall = $this->getlogMessageCall();
            $outputCall($message);
        }
    }

}
