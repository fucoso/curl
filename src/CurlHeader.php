<?php

namespace Fucoso\Curl;

/**
 * Contains the Response Headers if any were returned.
 *
 * Provides an interface to parse & access the response headers easily. If {@link Curl::options->HEADER }
 * was set to true in such case reponse will have header in it, {@link Curl} class will extract
 * the header automatically and will also adjust the response string to only contain the body.
 *
 * In case headers were parsed they can be accessed as:
 *
 * <code>
 *      $curl = new Curl($url);
 *      ...
 *      $contentType = $curl->headers->contentType;
 *      ...
 * </code>
 *
 * Notice above that <b>Content-Type</b> header was accessed as <b>contentType</b>, this shows that
 * how every header parsed will be converted to pascal casing and any non alpha numeric characters will
 * removed from the header name. So <b>Referer</b> would access as <b>referer</b> and <b>User-Agent</b> will be
 * accessed as <b>userAgent</b>.
 */
class CurlHeader
{

    /**
     * Storage for parsed headers.
     *
     * @var array
     */
    private $_headers = array();

    /**
     * HTTP verb string.
     *
     * @var string
     */
    public $verb = '';

    /**
     * Create object for CurlHeader
     */
    public function __construct()
    {

    }

    /**
     * Process the reponse headers and parse out the headers and verbs contained in it.
     * In case of a relocation (redirect), headers string can include all headers in redirects,
     * this function would process it so that only last request headers are represented. Any
     * earlier request headers will be discarded.
     *
     * @param string $responseHeaders
     */
    public function process($responseHeaders)
    {
        $this->_headers = array();
        $tokens = explode("\n", trim($responseHeaders));
        if (count($tokens) > 0) {
            $count = count($tokens);
            $index = 1;
            foreach ($tokens as $token) {

                // If its an empty token, it represents the new line in headers,
                // which would mean the parsed headers are from an old requests,
                // so lets clean and move to next set.F
                if (trim('' . $token) == '') {
                    if ($index < $count) {
                        $this->_headers = array();
                    }
                }

                // If token starts with HTTP, it means its verb line in header.
                if (substr($token, 0, 4) == 'HTTP') {
                    $this->verb = $token;
                    continue;
                }

                $token = trim($token);
                $headerParts = explode(':', $token);
                if (isset($headerParts[0])) {

                    // Clean the Header Key.
                    $key = trim($headerParts[0]);
                    $key = preg_replace('/[^a-zA-Z0-9]/', ' ', $key);
                    $key = trim($key);
                    $key = ucwords($key);
                    $key = str_replace(' ', '', $key);
                    $key = lcfirst($key);

                    $value = '';
                    if (isset($headerParts[1])) {
                        $value = trim($headerParts[1]);
                    }
                    if (trim($key) != '') {
                        $this->_headers[$key] = $value;
                    }
                }
                $index++;
            }
        }
    }

    /**
     * Magic property getter.
     *
     * If header was avilable and was parsed, in that case, header each
     * header property can be requested using this getter like CURL::headerInfo->header
     *
     * The default CURL functions lack this ability.
     *
     * @param string $header Header name which is to be accessed
     * @return mixed|null The value returned in the response for this curl request.
     */
    public function __get($header)
    {
        if (array_key_exists($header, $this->_headers)) {
            return $this->_headers[$header];
        } else {
            return null;
        }
    }

    /**
     * Check if there were any headers parsed successfuly or not.
     *
     * @return boolean
     */
    public function hasHeaders()
    {
        if (count($this->_headers) > 0) {
            return true;
        } else {
            return false;
        }
    }

}
