<?php

namespace Fucoso\Curl;

use Closure;
use Exception;

/**
 * Contains the options that can be set for Curl Request.
 *
 * Instead of requiring a setopt() function and the CURLOPT_*
 * constants, which are cumbersome and ugly at best, this object
 * implements curl_setopt() through overloaded getter and setter
 * methods.
 *
 * For example, if you wanted to include the headers in the output,
 * the old way would be
 *
 * <code>
 *      curl_setopt($ch, CURLOPT_HEADER, true);
 * </code>
 *
 * But with this object, it's simply
 *
 * <code>
 *      $ch->options->HEADER = true;
 * </code>
 *
 * <b>NB:</b> Since, in my experience, the vast majority
 * of cURL scripts set CURLOPT_RETURNTRANSFER to true, the {@link Curl}
 * class sets it by default. If you do not want CURLOPT_RETURNTRANSFER,
 * you'll need to do this:
 *
 * <code>
 *      $c = new Curl;
 *      $c->options->RETURNTRANSFER = false;
 * </code>
 *
 * @property boolean $AUTOREFERER TRUE to automatically set the Referer: field in requests where it follows a Location:redirect.
 * @property boolean $BINARYTRANSFER TRUE to return the raw output when $this->RETURNTRANSFER is used.  ( From PHP 5.1.3, this option has no effect: the raw output will always be returned when  $this->RETURNTRANSFER is used. )
 * @property boolean $COOKIESESSION TRUE to mark this as a new cookie "session". It will force libcurl to ignore all cookies it is about to load that are "session cookies" from the previous session. By default, libcurl always stores and loads all cookies, independent if they are session cookies or not. Session cookies are cookies without expiry date and they are meant to be alive and existing for this "session" only.
 * @property boolean $CERTINFO TRUE to output SSL certification information to STDERR on secure transfers.  ( Added in cURL 7.19.1. Available since PHP 5.3.2. Requires  $this->VERBOSE to be on to have an effect. )
 * @property boolean $CONNECT_ONLY TRUE tells the library to perform all the required proxy authentication and connection setup, but no data transfer. This option is implemented for HTTP, SMTP and POP3.  ( Added in 7.15.2. Available since PHP 5.5.0. )
 * @property boolean $CRLF TRUE to convert Unix newlines to CRLF newlines on transfers.
 * @property boolean $DNS_USE_GLOBAL_CACHE TRUE to use a global DNS cache. This option is not thread-safe and is enabled by default.
 * @property boolean $FAILONERROR TRUE to fail verbosely if the HTTP code returned is greater than or equal to 400. The default behavior is to return the page normally, ignoring the code.
 * @property boolean $FILETIME TRUE to attempt to retrieve the modification date of the remote document. This value can be retrieved using the CURLINFO_FILETIMEoption with curl_getinfo().
 * @property boolean $FOLLOWLOCATION TRUE to follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive, PHP will follow as many "Location: " headers that it is sent, unless  $this->MAXREDIRS is set).
 * @property boolean $FORBID_REUSE TRUE to force the connection to explicitly close when it has finished processing, and not be pooled for reuse.
 * @property boolean $FRESH_CONNECT TRUE to force the use of a new connection instead of a cached one.
 * @property boolean $FTP_USE_EPRT TRUE to use EPRT (and LPRT) when doing active FTP downloads. Use FALSE to disable EPRT and LPRT and use PORT only.
 * @property boolean $FTP_USE_EPSV TRUE to first try an EPSV command for FTP transfers before reverting back to PASV. Set to FALSE to disable EPSV.
 * @property boolean $FTP_CREATE_MISSING_DIRS TRUE to create missing directories when an FTP operation encounters a path that currently doesn't exist.
 * @property boolean $FTPAPPEND TRUE to append to the remote file instead of overwriting it.
 * @property boolean $TCP_NODELAY Pass a long specifying whether the TCP_NODELAY option is to be set or cleared (1 = set, 0 = clear). The option is cleared by default.  ( Available since PHP 5.2.1 for versions compiled with libcurl 7.11.2 or greater. )
 * @property boolean $FTPASCII An alias of  $this->TRANSFERTEXT. Use that instead.
 * @property boolean $FTPLISTONLY TRUE to only list the names of an FTP directory.
 * @property boolean $HEADER TRUE to include the header in the output.
 * @property boolean $CURLINFO_HEADER_OUT TRUE to track the handle's request string.  ( Available since PHP 5.1.3. TheCURLINFO_ prefix is intentional. )
 * @property boolean $HTTPGET TRUE to reset the HTTP request method to GET. Since GET is the default, this is only necessary if the request method has been changed.
 * @property boolean $HTTPPROXYTUNNEL TRUE to tunnel through a given HTTP proxy.
 * @property boolean $MUTE TRUE to be completely silent with regards to the cURL functions.  ( Removed in cURL 7.15.5 (You can use  $this->RETURNTRANSFER instead) )
 * @property boolean $NETRC TRUE to scan the ~/.netrc file to find a username and password for the remote site that a connection is being established with.
 * @property boolean $NOBODY TRUE to exclude the body from the output. Request method is then set to HEAD. Changing this to FALSE does not change it to GET.
 * @property boolean $NOPROGRESS TRUE to disable the progress meter for cURL transfers.  ( PHP automatically sets this option toTRUE, this should only be changed for debugging purposes. )
 * @property boolean $NOSIGNAL TRUE to ignore any cURL function that causes a signal to be sent to the PHP process. This is turned on by default in multi-threaded SAPIs so timeout options can still be used.  ( Added in cURL 7.10. )
 * @property boolean $POST TRUE to do a regular HTTP POST. This POST is the normal application/x-www-form-urlencoded kind, most commonly used by HTML forms.
 * @property boolean $PUT TRUE to HTTP PUT a file. The file to PUT must be set with  $this->INFILE and $this->INFILESIZE.
 * @property boolean $RETURNTRANSFER TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
 * @property boolean $SSL_VERIFYPEER FALSE to stop cURL from verifying the peer's certificate. Alternate certificates to verify against can be specified with the $this->CAINFO option or a certificate directory can be specified with the $this->CAPATH option.  ( TRUE by default as of cURL 7.10. Default bundle installed as of cURL 7.10. )
 * @property boolean $TRANSFERTEXT TRUE to use ASCII mode for FTP transfers. For LDAP, it retrieves data in plain text instead of HTML. On Windows systems, it will not set STDOUT to binary mode.
 * @property boolean $UNRESTRICTED_AUTH TRUE to keep sending the username and password when following locations (using $this->FOLLOWLOCATION), even when the hostname has changed.
 * @property boolean $UPLOAD TRUE to prepare for an upload.
 * @property boolean $VERBOSE TRUE to output verbose information. Writes output to STDERR, or the file specified using  $this->STDERR.
 * @property int $BUFFERSIZE The size of the buffer to use for each read. There is no guarantee this request will be fulfilled, however.  ( Added in cURL 7.10. )
 * @property int $CLOSEPOLICY One of the CURLCLOSEPOLICY_* values.  ( Removed in PHP 5.6.0.  This option is deprecated, as it was never implemented in cURL and never had any effect. )
 * @property int $CONNECTTIMEOUT The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
 * @property int $CONNECTTIMEOUT_MS The number of milliseconds to wait while trying to connect. Use 0 to wait indefinitely. If libcurl is built to use the standard system name resolver, that portion of the connect will still use full-second resolution for timeouts with a minimum timeout allowed of one second.  ( Added in cURL 7.16.2. Available since PHP 5.2.3. )
 * @property int $DNS_CACHE_TIMEOUT The number of seconds to keep DNS entries in memory. This option is set to 120 (2 minutes) by default.
 * @property int $FTPSSLAUTH The FTP authentication method (when is activated):CURLFTPAUTH_SSL (try SSL first), CURLFTPAUTH_TLS (try TLS first), or CURLFTPAUTH_DEFAULT (let cURL decide).  ( Added in cURL 7.12.2. )
 * @property int $HTTP_VERSION CURL_HTTP_VERSION_NONE (default, lets CURL decide which version to use), CURL_HTTP_VERSION_1_0 (forces HTTP/1.0), or CURL_HTTP_VERSION_1_1 (forces HTTP/1.1).
 * @property int $HTTPAUTH The HTTP authentication method(s) to use. The options are:CURLAUTH_BASIC, CURLAUTH_DIGEST,CURLAUTH_GSSNEGOTIATE, CURLAUTH_NTLM,CURLAUTH_ANY, and CURLAUTH_ANYSAFE.  ( The bitwise | (or) operator can be used to combine more than one method. If this is done, cURL will poll the server to see what methods it supports and pick the best one. CURLAUTH_ANY is an alias for CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM. CURLAUTH_ANYSAFE is an alias for CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM. )
 * @property int $INFILESIZE The expected size, in bytes, of the file when uploading a file to a remote site. Note that using this option will not stop libcurl from sending more data, as exactly what is sent depends on $this->READFUNCTION.
 * @property int $LOW_SPEED_LIMIT The transfer speed, in bytes per second, that the transfer should be below during the count of  $this->LOW_SPEED_TIME seconds before PHP considers the transfer too slow and aborts.
 * @property int $LOW_SPEED_TIME The number of seconds the transfer speed should be below $this->LOW_SPEED_LIMIT before PHP considers the transfer too slow and aborts.
 * @property int $MAXCONNECTS The maximum amount of persistent connections that are allowed. When the limit is reached,  $this->CLOSEPOLICY is used to determine which connection to close.
 * @property int $MAXREDIRS The maximum amount of HTTP redirections to follow. Use this option alongside  $this->FOLLOWLOCATION.
 * @property int $PORT An alternative port number to connect to.
 * @property int $PROTOCOLS Bitmask of CURLPROTO_* values. If used, this bitmask limits what protocols libcurl may use in the transfer. This allows you to have a libcurl built to support a wide range of protocols but still limit specific transfers to only be allowed to use a subset of them. By default libcurl will accept all protocols it supports. See also $this->REDIR_PROTOCOLS.  ( Added in cURL 7.19.4. Valid protocol options are: CURLPROTO_HTTP,CURLPROTO_HTTPS, CURLPROTO_FTP,CURLPROTO_FTPS, CURLPROTO_SCP,CURLPROTO_SFTP, CURLPROTO_TELNET,CURLPROTO_LDAP, CURLPROTO_LDAPS,CURLPROTO_DICT, CURLPROTO_FILE,CURLPROTO_TFTP, CURLPROTO_ALL )
 * @property int $PROXYAUTH The HTTP authentication method(s) to use for the proxy connection. Use the same bitmasks as described in $this->HTTPAUTH. For proxy authentication, onlyCURLAUTH_BASIC and CURLAUTH_NTLM are currently supported.  ( Added in cURL 7.10.7. )
 * @property int $PROXYPORT The port number of the proxy to connect to. This port number can also be set in  $this->PROXY.
 * @property int $PROXYTYPE Either CURLPROXY_HTTP (default) orCURLPROXY_SOCKS5.  ( Added in cURL 7.10. )
 * @property int $REDIR_PROTOCOLS Bitmask of CURLPROTO_* values. If used, this bitmask limits what protocols libcurl may use in a transfer that it follows to in a redirect when  $this->FOLLOWLOCATION is enabled. This allows you to limit specific transfers to only be allowed to use a subset of protocols in redirections. By default libcurl will allow all protocols except for FILE and SCP. This is a difference compared to pre-7.19.4 versions which unconditionally would follow to all protocols supported. See also  $this->PROTOCOLS for protocol constant values.  ( Added in cURL 7.19.4. )
 * @property int $RESUME_FROM The offset, in bytes, to resume a transfer from.
 * @property int $SSL_VERIFYHOST 1 to check the existence of a common name in the SSL peer certificate. 2 to check the existence of a common name and also verify that it matches the hostname provided. In production environments the value of this option should be kept at 2 (default value).  ( Support for value 1 removed in cURL 7.28.1 )
 * @property int $SSLVERSION The SSL version (2 or 3) to use. By default PHP will try to determine this itself, although in some cases this must be set manually.
 * @property int $TIMECONDITION How  $this->TIMEVALUE is treated. UseCURL_TIMECOND_IFMODSINCE to return the page only if it has been modified since the time specified in  $this->TIMEVALUE. If it hasn't been modified, a "304 Not Modified" header will be returned assuming  $this->HEADER is TRUE. UseCURL_TIMECOND_IFUNMODSINCE for the reverse effect.CURL_TIMECOND_IFMODSINCE is the default.
 * @property int $TIMEOUT The maximum number of seconds to allow cURL functions to execute.
 * @property int $TIMEOUT_MS The maximum number of milliseconds to allow cURL functions to execute. If libcurl is built to use the standard system name resolver, that portion of the connect will still use full-second resolution for timeouts with a minimum timeout allowed of one second.  ( Added in cURL 7.16.2. Available since PHP 5.2.3. )
 * @property int $TIMEVALUE The time in seconds since January 1st, 1970. The time will be used by  $this->TIMECONDITION. By default,CURL_TIMECOND_IFMODSINCE is used.
 * @property int $MAX_RECV_SPEED_LARGE If a download exceeds this speed (counted in bytes per second) on cumulative average during the transfer, the transfer will pause to keep the average rate less than or equal to the parameter value. Defaults to unlimited speed.  ( Added in cURL 7.15.5. Available since PHP 5.4.0. )
 * @property int $MAX_SEND_SPEED_LARGE If an upload exceeds this speed (counted in bytes per second) on cumulative average during the transfer, the transfer will pause to keep the average rate less than or equal to the parameter value. Defaults to unlimited speed.  ( Added in cURL 7.15.5. Available since PHP 5.4.0. )
 * @property int $SSH_AUTH_TYPES A bitmask consisting of one or more of CURLSSH_AUTH_PUBLICKEY,CURLSSH_AUTH_PASSWORD, CURLSSH_AUTH_HOST,CURLSSH_AUTH_KEYBOARD. Set to CURLSSH_AUTH_ANY to let libcurl pick one.  ( Added in cURL 7.16.1. )
 * @property int $IPRESOLVE Allows an application to select what kind of IP addresses to use when resolving host names. This is only interesting when using host names that resolve addresses using more than one version of IP, possible values are CURL_IPRESOLVE_WHATEVER,CURL_IPRESOLVE_V4, CURL_IPRESOLVE_V6, by defaultCURL_IPRESOLVE_WHATEVER.  ( Added in cURL 7.10.8. )
 * @property string $CAINFO The name of a file holding one or more certificates to verify the peer with. This only makes sense when used in combination with  $this->SSL_VERIFYPEER.  ( Requires absolute path. )
 * @property string $CAPATH A directory that holds multiple CA certificates. Use this option alongside  $this->SSL_VERIFYPEER.
 * @property string $COOKIE The contents of the "Cookie: " header to be used in the HTTP request. Note that multiple cookies are separated with a semicolon followed by a space (e.g., "fruit=apple; colour=red")
 * @property string $COOKIEFILE The name of the file containing the cookie data. The cookie file can be in Netscape format, or just plain HTTP-style headers dumped into a file. If the name is an empty string, no cookies are loaded, but cookie handling is still enabled.
 * @property string $COOKIEJAR The name of a file to save all internal cookies to when the handle is closed, e.g. after a call to curl_close.
 * @property string $CUSTOMREQUEST A custom request method to use instead of "GET" or "HEAD"when doing a HTTP request. This is useful for doing "DELETE"or other, more obscure HTTP requests. Valid values are things like "GET", "POST", "CONNECT" and so on; i.e. Do not enter a whole HTTP request line here. For instance, entering "GET /index.html HTTP/1.0\r\n\r\n" would be incorrect.  ( Don't do this without making sure the server supports the custom request method first. )
 * @property string $EGDSOCKET Like  $this->RANDOM_FILE, except a filename to an Entropy Gathering Daemon socket.
 * @property string $ENCODING The contents of the "Accept-Encoding: " header. This enables decoding of the response. Supported encodings are "identity","deflate", and "gzip". If an empty string, "", is set, a header containing all supported encoding types is sent.  ( Added in cURL 7.10. )
 * @property string $FTPPORT The value which will be used to get the IP address to use for the FTP "PORT" instruction. The "PORT" instruction tells the remote server to connect to our specified IP address. The string may be a plain IP address, a hostname, a network interface name (under Unix), or just a plain '-' to use the systems default IP address.
 * @property string $INTERFACE The name of the outgoing network interface to use. This can be an interface name, an IP address or a host name.
 * @property string $KEYPASSWD The password required to use the  $this->SSLKEY or $this->SSH_PRIVATE_KEYFILE private key.  ( Added in cURL 7.16.1. )
 * @property string $KRB4LEVEL The KRB4 (Kerberos 4) security level. Any of the following values (in order from least to most powerful) are valid: "clear","safe", "confidential", "private".. If the string does not match one of these, "private" is used. Setting this option to NULL will disable KRB4 security. Currently KRB4 security only works with FTP transactions.
 * @property string $POSTFIELDS The full data to post in a HTTP "POST" operation. To post a file, prepend a filename with @ and use the full path. The filetype can be explicitly specified by following the filename with the type in the format ';type=mimetype'. This parameter can either be passed as a urlencoded string like 'para1=val1&para2=val2&...' or as an array with the field name as key and field data as value. If value is an array, theContent-Type header will be set to multipart/form-data. As of PHP 5.2.0, value must be an array if files are passed to this option with the @ prefix. As of PHP 5.5.0, the @ prefix is deprecated and files can be sent using CURLFile.
 * @property string $PROXY The HTTP proxy to tunnel requests through.
 * @property string $PROXYUSERPWD A username and password formatted as "[username]:[password]" to use for the connection to the proxy.
 * @property string $RANDOM_FILE A filename to be used to seed the random number generator for SSL.
 * @property string $RANGE Range(s) of data to retrieve in the format "X-Y" where X or Y are optional. HTTP transfers also support several intervals, separated with commas in the format "X-Y,N-M".
 * @property string $REFERER The contents of the "Referer: " header to be used in a HTTP request.
 * @property string $SSH_HOST_PUBLIC_KEY_MD5 A string containing 32 hexadecimal digits. The string should be the MD5 checksum of the remote host's public key, and libcurl will reject the connection to the host unless the md5sums match. This option is only for SCP and SFTP transfers.  ( Added in cURL 7.17.1. )
 * @property string $SSH_PUBLIC_KEYFILE The file name for your public key. If not used, libcurl defaults to $HOME/.ssh/id_dsa.pub if the HOME environment variable is set, and just "id_dsa.pub" in the current directory if HOME is not set.  ( Added in cURL 7.16.1. )
 * @property string $SSH_PRIVATE_KEYFILE The file name for your private key. If not used, libcurl defaults to $HOME/.ssh/id_dsa if the HOME environment variable is set, and just "id_dsa" in the current directory if HOME is not set. If the file is password-protected, set the password with $this->KEYPASSWD.  ( Added in cURL 7.16.1. )
 * @property string $SSL_CIPHER_LIST A list of ciphers to use for SSL. For example, RC4-SHA and TLSv1are valid cipher lists.
 * @property string $SSLCERT The name of a file containing a PEM formatted certificate.
 * @property string $SSLCERTPASSWD The password required to use the  $this->SSLCERT certificate.
 * @property string $SSLCERTTYPE The format of the certificate. Supported formats are "PEM"(default), "DER", and "ENG".  ( Added in cURL 7.9.3. )
 * @property string $SSLENGINE The identifier for the crypto engine of the private SSL key specified in  $this->SSLKEY.
 * @property string $SSLENGINE_DEFAULT The identifier for the crypto engine used for asymmetric crypto operations.
 * @property string $SSLKEY The name of a file containing a private SSL key.
 * @property string $SSLKEYPASSWD The secret password needed to use the private SSL key specified in  $this->SSLKEY.  ( Since this option contains a sensitive password, remember to keep the PHP script it is contained within safe. )
 * @property string $SSLKEYTYPE The key type of the private SSL key specified in $this->SSLKEY. Supported key types are "PEM" (default),"DER", and "ENG".
 * @property string $URL The URL to fetch. This can also be set when initializing a session with curl_init().
 * @property string $USERAGENT The contents of the "User-Agent: " header to be used in a HTTP request.
 * @property string $USERPWD A username and password formatted as "[username]:[password]" to use for the connection.
 * @property array $HTTP200ALIASES An array of HTTP 200 responses that will be treated as valid responses and not as errors.  ( Added in cURL 7.10.3. )
 * @property array $HTTPHEADER An array of HTTP header fields to set, in the format array('Content-type: text/plain', 'Content-length: 100')
 * @property array $POSTQUOTE An array of FTP commands to execute on the server after the FTP request has been performed.
 * @property array $QUOTE An array of FTP commands to execute on the server prior to the FTP request.
 * @property stream $FILE The file that the transfer should be written to. The default is STDOUT (the browser window).
 * @property stream $INFILE The file that the transfer should be read from when uploading.
 * @property stream $STDERR An alternative location to output errors to instead of STDERR.
 * @property stream $WRITEHEADER The file that the header part of the transfer is written to.
 * @property Closure $HEADERFUNCTION A callback accepting two parameters. The first is the cURL resource, the second is a string with the header data to be written. The header data must be written when by this callback. Return the number of bytes written.
 * @property Closure $PASSWDFUNCTION A callback accepting three parameters. The first is the cURL resource, the second is a string containing a password prompt, and the third is the maximum password length. Return the string containing the password.
 * @property Closure $PROGRESSFUNCTION A callback accepting five parameters. The first is the cURL resource, the second is the total number of bytes expected to be downloaded in this transfer, the third is the number of bytes downloaded so far, the fourth is the total number of bytes expected to be uploaded in this transfer, and the fifth is the number of bytes uploaded so far.  ( The callback is only called when the  $this->NOPROGRESS option is set to FALSE. Return a non-zero value to abort the transfer. In which case, the transfer will set aCURLE_ABORTED_BY_CALLBACK error. )
 * @property Closure $READFUNCTION A callback accepting three parameters. The first is the cURL resource, the second is a stream resource provided to cURL through the option  $this->INFILE, and the third is the maximum amount of data to be read. The callback must return a string with a length equal or smaller than the amount of data requested, typically by reading it from the passed stream resource. It should return an empty string to signal EOF.
 * @property Closure $WRITEFUNCTION A callback accepting two parameters. The first is the cURL resource, and the second is a string with the data to be written. The data must be saved by this callback. It must return the exact number of bytes written or the transfer will be aborted with an error.
 *
 */
class CurlOptions
{

    /**
     * Store the curl_init() resource passed by containing Curl object.
     *
     * @var resource
     */
    private $_curlHandle = null;

    /**
     * Store the CURLOPT_* values.
     *
     * Do not access directly. Access is through {@link __get()}
     * and {@link __set()} magic methods.
     *
     * @var array
     */
    private $_curlOptions = array();

    /**
     * List of available options to make sure an invalid option is not set by mistake.
     *
     * @var array
     */
    private $_availableOptions = array(
        'AUTOREFERER', 'BINARYTRANSFER', 'COOKIESESSION', 'CERTINFO', 'CONNECT_ONLY',
        'CRLF', 'DNS_USE_GLOBAL_CACHE', 'FAILONERROR', 'FILETIME', 'FOLLOWLOCATION',
        'FORBID_REUSE', 'FRESH_CONNECT', 'FTP_USE_EPRT', 'FTP_USE_EPSV', 'FTP_CREATE_MISSING_DIRS',
        'FTPAPPEND', 'TCP_NODELAY', 'FTPASCII', 'FTPLISTONLY', 'HEADER', 'CURLINFO_HEADER_OUT',
        'HTTPGET', 'HTTPPROXYTUNNEL', 'MUTE', 'NETRC', 'NOBODY', 'NOPROGRESS', 'NOSIGNAL', 'POST',
        'PUT', 'RETURNTRANSFER', 'SSL_VERIFYPEER', 'TRANSFERTEXT', 'UNRESTRICTED_AUTH', 'UPLOAD',
        'VERBOSE', 'BUFFERSIZE', 'CLOSEPOLICY', 'CONNECTTIMEOUT', 'CONNECTTIMEOUT_MS',
        'DNS_CACHE_TIMEOUT', 'FTPSSLAUTH', 'HTTP_VERSION', 'HTTPAUTH', 'INFILESIZE',
        'LOW_SPEED_LIMIT', 'LOW_SPEED_TIME', 'MAXCONNECTS', 'MAXREDIRS', 'PORT', 'PROTOCOLS',
        'PROXYAUTH', 'PROXYPORT', 'PROXYTYPE', 'REDIR_PROTOCOLS', 'RESUME_FROM', 'SSL_VERIFYHOST',
        'SSLVERSION', 'TIMECONDITION', 'TIMEOUT', 'TIMEOUT_MS', 'TIMEVALUE', 'MAX_RECV_SPEED_LARGE',
        'MAX_SEND_SPEED_LARGE', 'SSH_AUTH_TYPES', 'IPRESOLVE', 'CAINFO', 'CAPATH', 'COOKIE', 'COOKIEFILE',
        'COOKIEJAR', 'CUSTOMREQUEST', 'EGDSOCKET', 'ENCODING', 'FTPPORT', 'INTERFACE', 'KEYPASSWD',
        'KRB4LEVEL', 'POSTFIELDS', 'PROXY', 'PROXYUSERPWD', 'RANDOM_FILE', 'RANGE', 'REFERER',
        'SSH_HOST_PUBLIC_KEY_MD5', 'SSH_PUBLIC_KEYFILE', 'SSH_PRIVATE_KEYFILE', 'SSL_CIPHER_LIST',
        'SSLCERT', 'SSLCERTPASSWD', 'SSLCERTTYPE', 'SSLENGINE', 'SSLENGINE_DEFAULT', 'SSLKEY',
        'SSLKEYPASSWD', 'SSLKEYTYPE', 'URL', 'USERAGENT', 'USERPWD', 'HTTP200ALIASES', 'HTTPHEADER',
        'POSTQUOTE', 'QUOTE', 'FILE', 'INFILE', 'STDERR', 'WRITEHEADER', 'HEADERFUNCTION',
        'PASSWDFUNCTION', 'PROGRESSFUNCTION', 'READFUNCTION', 'WRITEFUNCTION',
    );

    /**
     * Create the new {@link CurlOptions} object, with the
     * container $curlHandle.
     *
     * @param resource $curlHandle The curl handle of the object which would create this object.
     * @throws Exception
     */
    public function __construct($curlHandle)
    {
        if (!$curlHandle) {
            throw new Exception("Curl Options: Invalid Curl Hanlde Provided.");
        }

        $this->_curlHandle = $curlHandle;

        //By default lets consider we would have a response.
        $this->RETURNTRANSFER = true;

        // Applications can override this User Agent value
        $this->USERAGENT = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';

        //Set Default Timeout for Curl
        $this->CONNECTTIMEOUT = 15;
        $this->TIMEOUT = 30;
    }

    /**
     * If the session was closed with {@link Curl::close()} & reopened by
     * {@link Curl::initiate()}, this function would be called to update
     * the options in the new curl object.
     *
     * @param string $curlHandle The new curl handle of the object which would create this object.
     * @return bool|Curl
     */
    public function initiate($curlHandle)
    {
        $this->_curlHandle = $curlHandle;
        foreach ($this->_curlOptions as $const => $value) {
            curl_setopt($this->_curlHandle, constant($const), $value);
        }
    }

    /**
     * Magic property setter.
     *
     * A sneaky way to access curl_setopt(). If the
     * constant CURLOPT_$opt exists, then we try to set
     * the option using curl_setopt() and return its
     * success. If it doesn't exist, just return false.
     *
     * Also stores the variable in {@link $curlopt} so
     * its value can be retrieved with {@link __get()}.
     *
     * @param string $opt The second half of the CURLOPT_* constant, not case sensitive
     * @param mixed $value
     * @return void
     */
    public function __set($opt, $value)
    {
        if (in_array($opt, $this->_availableOptions)) {
            $const = 'CURLOPT_' . strtoupper($opt);
            if (defined($const)) {
                if (curl_setopt($this->_curlHandle, constant($const), $value)) {
                    $this->_curlOptions[$const] = $value;
                }
            }
        } else {
            throw new Exception("Curl Options: Invalid Option Name Provided.");
        }
    }

    /**
     * Magic property getter.
     *
     * When options are set with {@link __set()}, they
     * are also stored in {@link $_curlopt} so that we
     * can always find out what the options are now.
     *
     * The default cURL functions lack this ability.
     *
     * @param string $option The second half of the CURLOPT_* constant, not case sensitive
     * @return mixed The set value of CURLOPT_<var>$opt</var>, or NULL if it hasn't been set (ie: is still default).
     */
    public function __get($option)
    {
        if (in_array($option, $this->_availableOptions)) {
            if (array_key_exists('CURLOPT_' . strtoupper($option), $this->_curlOptions)) {
                return $this->_curlOptions['CURLOPT_' . strtoupper($option)];
            } else {
                return null;
            }
        } else {
            throw new Exception("Curl Options: Invalid Option Name Requested.");
        }
    }

    /**
     * Magic property isset()
     *
     * Can tell if a CURLOPT_* value was set by using
     * <code>
     *      isset($curl->options->*)
     * </code>
     *
     * The default cURL functions lack this ability.
     *
     * @param string $opt The second half of the CURLOPT_* constant, not case sensitive
     * @return bool
     */
    public function __isset($opt)
    {
        return isset($this->_curlOptions['CURLOPT_' . strtoupper($opt)]);
    }

    /**
     * Magic property unset()
     *
     * Unfortunately, there is no way, short of writing an
     * extremely long, but mostly NULL-filled array, to
     * implement a decent version of
     * <code>
     *      unset($curl->options->*);
     * </code>
     *
     * @todo Consider implementing an array of all the CURLOPT_*
     *       constants and their default values.
     * @param string $opt The second half of the CURLOPT_* constant, not case sensitive
     * @return void
     */
    public function __unset($opt)
    {
        // Since we really can't reset a CURLOPT_* to its
        // default value without knowing the default value,
        // just do nothing.
    }

}
