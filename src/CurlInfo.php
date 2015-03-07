<?php

namespace Fucoso\Curl;

use Exception;

/**
 * Contains the infrmation about the curl request after execution.
 *
 * In case you need any information about the curl request,
 * all that information after performing curl execution would
 * be stored in this class & can be accessed by {@link Curl::info} property.
 *
 * e.g.
 * <code>
 *      $curl = new Curl();
 *      .
 *      .
 *      .
 *      $curl->execute();
 *      if($curl->info->HTTP_CODE == '200')...
 * </code>
 *
 * The upper case of the properties is to keep it consistent with actual curl library constant names.
 */
class CurlInfo
{

    /**
     * Store the curl_init() resource passed by containing Curl object.
     *
     * @var resource
     */
    private $_curlHandle = null;

    /**
     * @var int $CONNECT_TIME Time in seconds it took to establish the connection
     */
    public $CONNECT_TIME = null;

    /**
     * content-length of download, read from Content-Length: field
     *
     * @var int $CONTENT_LENGTH_DOWNLOAD
     */
    public $CONTENT_LENGTH_DOWNLOAD = null;

    /**
     * Specified size of upload
     *
     * @var int $CONTENT_LENGTH_UPLOAD
     */
    public $CONTENT_LENGTH_UPLOAD = null;

    /**
     * Content-Type: of the requested document, NULL indicates server did not send valid Content-Type: header
     *
     * @var int $CONTENT_TYPE
     */
    public $CONTENT_TYPE = null;

    /**
     * Last effective URL
     *
     * @var int $EFFECTIVE_URL
     */
    public $EFFECTIVE_URL = null;

    /**
     * Remote time of the retrieved document, if -1 is returned the time of the document is unknown
     *
     * @var int $FILETIME
     */
    public $FILETIME = null;

    /**
     * The request string sent. For this to work, add the HEADER_OUT option to the handle by calling curl_setopt()
     *
     * @var int $HEADER_OUT
     */
    public $HEADER_OUT = null;

    /**
     * Total size of all headers received
     *
     * @var int $HEADER_SIZE
     */
    public $HEADER_SIZE = null;

    /**
     * Last received HTTP code
     *
     * @var int $HTTP_CODE
     */
    public $HTTP_CODE = null;

    /**
     * Time in seconds until name resolving was complete
     *
     * @var int $NAMELOOKUP_TIME
     */
    public $NAMELOOKUP_TIME = null;

    /**
     * Time in seconds from start until just before file transfer begins
     *
     * @var int $PRETRANSFER_TIME
     */
    public $PRETRANSFER_TIME = null;

    /**
     * Number of redirects
     *
     * @var int $REDIRECT_COUNT
     */
    public $REDIRECT_COUNT = null;

    /**
     * Time in seconds of all redirection steps before final transaction was started
     *
     * @var int $REDIRECT_TIME
     */
    public $REDIRECT_TIME = null;

    /**
     * Total size of issued requests, currently only for HTTP requests
     *
     * @var int $REQUEST_SIZE
     */
    public $REQUEST_SIZE = null;

    /**
     * Total number of bytes downloaded
     *
     * @var int $SIZE_DOWNLOAD
     */
    public $SIZE_DOWNLOAD = null;

    /**
     * Total number of bytes uploaded
     *
     * @var int $SIZE_UPLOAD
     */
    public $SIZE_UPLOAD = null;

    /**
     * Average download speed
     *
     * @var int $SPEED_DOWNLOAD
     */
    public $SPEED_DOWNLOAD = null;

    /**
     * Average upload speed
     *
     * @var int $SPEED_UPLOAD
     */
    public $SPEED_UPLOAD = null;

    /**
     * Result of SSL certification verification requested by setting CURLOPT_SSL_VERIFYPEER
     *
     * @var int $SSL_VERIFYRESULT
     */
    public $SSL_VERIFYRESULT = null;

    /**
     * Time in seconds until the first byte is about to be transferred
     *
     * @var int $STARTTRANSFER_TIME
     */
    public $STARTTRANSFER_TIME = null;

    /**
     * Total transaction time in seconds for last transfer
     *
     * @var int $TOTAL_TIME
     */
    public $TOTAL_TIME = null;

    /**
     * Create CurlInfo Object for a {@link Curl} object.
     * @param resource $curlHandle
     * @throws Exception
     */
    public function __construct($curlHandle)
    {
        if (!$curlHandle) {
            throw new Exception("Curl Options: Invalid Curl Hanlde Provided.");
        }

        $this->_curlHandle = $curlHandle;
    }

    /**
     * Process the CURL request after execution to extraction information.
     */
    public function status()
    {
        $list = array(
            'EFFECTIVE_URL', 'HTTP_CODE', 'FILETIME', 'TOTAL_TIME', 'NAMELOOKUP_TIME',
            'CONNECT_TIME', 'PRETRANSFER_TIME', 'STARTTRANSFER_TIME', 'REDIRECT_COUNT',
            'REDIRECT_TIME', 'SIZE_UPLOAD', 'SIZE_DOWNLOAD', 'SPEED_DOWNLOAD', 'SPEED_UPLOAD',
            'HEADER_SIZE', 'HEADER_OUT', 'REQUEST_SIZE', 'SSL_VERIFYRESULT', 'CONTENT_LENGTH_DOWNLOAD',
            'CONTENT_LENGTH_UPLOAD', 'CONTENT_TYPE',
        );

        foreach ($list as $option) {
            if (property_exists($this, $option)) {
                $this->$option = curl_getinfo($this->_curlHandle, constant('CURLINFO_' . $option));
            }
        }
    }

}
