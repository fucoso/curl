<?php

namespace Fucoso\Curl;

use Exception;

/**
 * Curl connection object
 *
 * Provides an Object-Oriented interface to the PHP cURL
 * functions and a clean way to replace curl_setopt().
 *
 *
 */
class Curl
{

    /**
     * Store the curl_init() resource.
     * @var resource
     */
    private $_curlHandle = null;

    /**
     * Contains the options that can be set for Curl Request.
     *
     * @var CurlOptions
     */
    public $options = null;

    /**
     * Contains the infrmation about the curl request after execution.
     *
     * @var CurlInfo
     */
    public $info = null;

    /**
     * Contains the headers list in the response if there were any returned.
     *
     * @var CurlHeader
     */
    public $headers = null;

    /**
     * Flag the Curl object as linked to a {@link CurlParallel}
     * object.
     *
     * @var bool
     */
    private $_isMulti = false;

    /**
     * Store the response. Used with {@link fetch()} and
     * {@link fetchJson()}.
     *
     * @var string
     */
    private $_response = null;

    /**
     * URL that was provided by the caller.
     *
     * @var string
     */
    private $_url = null;

    /**
     * URL after the call, in case any redirection or anything occurs that changes the url where the response came from.
     *
     * @var string
     */
    private $_effectiveUrl = null;

    /**
     * Status Code of the request.
     *
     * @var int
     */
    private $_statusCode = null;

    /**
     * Flag to check if the curl object has executed its request or not.
     *
     * @var boolean
     */
    private $_executed = false;

    /**
     *
     * @var string
     */
    private $_destinationFile = null;

    /**
     * Create the new {@link Curl} object, with the
     * URL parameter.
     *
     * @param string $url The URL to open
     * @return Curl A new Curl object.
     * @throws Exception
     */
    public function __construct($url)
    {
        // Make sure the cURL extension is loaded
        if (!extension_loaded('curl')) {
            throw new Exception("cURL library is not loaded. Please recompile PHP with the cURL library.");
        }

        // Create the cURL resource
        $this->_curlHandle = curl_init();


        if ($this->_curlHandle) {
            $this->options = new CurlOptions($this->_curlHandle);
            $this->info = new CurlInfo($this->_curlHandle);
            $this->headers = new CurlHeader();

            $this->_url = $url;
            $this->_effectiveUrl = $url;

            // Set some default options
            $this->options->URL = $this->_url;
        } else {
            throw new Exception("Curl: Failed to initialize Curl Request.");
        }

        // Return $this for chaining
        return $this;
    }

    /**
     * When destroying the object, be sure to free resources.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Execute the cURL transfer.
     *
     * @return mixed
     */
    public function execute()
    {
        if (!$this->_isMulti) {
            $this->_response = curl_exec($this->_curlHandle);
            $this->_processInformation();
            $this->_processHeaders();
            return $this->isSuccessful();
        } else {
            curl_exec($this->_curlHandle);
            $this->_processInformation();
            return $this->isSuccessful();
        }
    }

    /**
     * Process the information for the current request to fill {@link Curl::info} object
     * and also some other basic properties like {@link Curl::_effectiveUrl},
     * {@link Curl::_statusCode} and {@link Curl:: _executed}
     */
    private function _processInformation()
    {
        $this->info->status();
        $this->_effectiveUrl = $this->info->EFFECTIVE_URL;
        $this->_statusCode = $this->info->HTTP_CODE;
        $this->_executed = true;
    }

    /**
     * Process the headers in the response string if HEADER option is set to true & if
     * there are headers, clean them out of the response after processing.
     */
    private function _processHeaders()
    {
        if ($this->options->HEADER) {
            if ($this->_response) {
                if ($this->info->HEADER_SIZE > 0) {
                    $this->headers->process(substr($this->_response, 0, $this->info->HEADER_SIZE));

                    if (!$this->options->NOBODY) {
                        $this->_response = substr($this->_response, $this->info->HEADER_SIZE);
                    }
                }
            }
        }
    }

    /**
     * Use this function to get the
     * returned data (whatever that is). Otherwise it's similar
     * to {@link exec()} except the output is saved, instead of
     * running the request repeatedly.
     *
     * @see $multi
     * @return mixed
     */
    public function response()
    {
        if ($this->_executed) {
            if ($this->_isMulti && !$this->_response) {
                $this->_response = curl_multi_getcontent($this->_curlHandle);
                $this->_processHeaders();
            }
            return $this->_response;
        } else {
            return null;
        }
    }

    /**
     * Fetch a JSON encoded value and return a JSON
     * object. Requires the PHP JSON functions. Pass TRUE
     * to return an associative array instead of an object.
     *
     * @param bool array optional. Return an array instead of an object.
     * @return mixed an array or object (possibly null).
     */
    public function fetchJson($array = false)
    {
        if ($this->_executed) {
            return json_decode($this->response(), $array);
        } else {
            return null;
        }
    }

    /**
     * Close the cURL session and free the resource.
     */
    public function close()
    {
        if (!empty($this->_curlHandle) && is_resource($this->_curlHandle)) {
            curl_close($this->_curlHandle);
        }
    }

    /**
     * Return an error string from the last execute (if any).
     *
     * @return string
     */
    public function error()
    {
        if ($this->_executed) {
            return curl_error($this->_curlHandle);
        } else {
            return "Execution Pending.";
        }
    }

    /**
     * Return the error number from the last execute (if any).
     *
     * 1000 is a special case added so that pending execution could be associtated with a number.
     *
     * @return integer
     */
    public function errorNumber()
    {
        if ($this->_executed) {
            return curl_errno($this->_curlHandle);
        } else {
            return 1000;
        }
    }

    /**
     * Get cURL version information (and adds OOCurl version info)
     *
     * @return array
     */
    public function version()
    {
        $version = curl_version();
        return $version;
    }

    /**
     * Grants access to {@link Curl::$ch $ch} to a {@link CurlParallel} object.
     *
     * @param CurlParallel $multiHandle The CurlParallel object that needs {@link Curl::$ch $ch}.
     */
    public function grant(CurlParallel $multiHandle)
    {
        $multiHandle->accept($this->_curlHandle);
        $this->_isMulti = true;
    }

    /**
     * Removes access to {@link Curl::$ch $ch} from a {@link CurlParallel} object.
     *
     * @param CurlParallel $multiHandle The CurlParallel object that no longer needs {@link Curl::$ch $ch}.
     */
    public function revoke(CurlParallel $multiHandle)
    {
        $multiHandle->release($this->_curlHandle);
        $this->_isMulti = false;
    }

    /**
     * Return the status code from the response.
     *
     * @return int|null
     */
    public function statusCode()
    {
        if ($this->_executed) {
            return $this->_statusCode;
        } else {
            return null;
        }
    }

    /**
     * Returns the effective url after the execution. If execution did not occur the same url is retuned.
     *
     * @return string|null
     */
    public function effectiveURL()
    {
        return $this->_effectiveUrl;
    }

    /**
     * Returns TRUE if the request was successful.
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        if ($this->statusCode()) {
            if ((int) ($this->statusCode() / 100) == 2 || (int) ($this->statusCode() / 100) == 3) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns TRUE if the request failed.
     *
     * @return boolean
     */
    public function isFailed()
    {
        if (!$this->isSuccessful()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns TRUE if the request was forbidden by the remote server.
     *
     * @return boolean
     */
    public function isForbidden()
    {
        if ($this->isFailed()) {
            if ($this->statusCode()) {
                if ($this->statusCode() && $this->statusCode() == 403) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getDestinationFile()
    {
        return $this->_destinationFile;
    }

    public function setDestinationFile($destinationFile)
    {
        $this->_destinationFile = $destinationFile;
    }

}
