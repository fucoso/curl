<?php

namespace Fucoso\Curl\Actions;

use Exception;
use Fucoso\Curl\Curl;
use Fucoso\Curl\Exceptions\FileSizeException;

class CurlActionDownload extends CurlAction
{

    /**
     * Downloads the given url to the given destination. Tries to resume download if file already exists.
     *
     * @param string $destination
     * @param string $url
     * @param boolean $overWrite
     * @return boolean|null
     */
    public function download($destination, &$url, $overWrite = false)
    {
        $urlInfo = $this->infoFromURL($url);

        if ($urlInfo->isSuccessful()) {

            $fileSize = null;
            if (file_exists($destination)) {
                $fileSize = $this->_getLocalFileSize($destination);
                if (!$overWrite && $fileSize >= $urlInfo->info->CONTENT_LENGTH_DOWNLOAD) {
                    return true;
                }
            }

            $curl = new Curl($url);
            $curl->options->SSL_VERIFYPEER = false;
            $curl->options->FOLLOWLOCATION = true;
            $curl->options->RETURNTRANSFER = false;
            $curl->options->CONNECTTIMEOUT = $this->connectTimeout;
            $curl->options->TIMEOUT = $this->curlTimeout;

            if (!$overWrite && file_exists($destination) && $urlInfo->headers->acceptRanges == 'bytes') {
                $curl->options->FILE = fopen($destination, 'a');
                $curl->options->RESUME_FROM = $fileSize;
                //Console::write("Resuming From {$fileSize} Bytes");
            } else {
                $curl->options->FILE = fopen($destination, 'w');
            }

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
            $curl->close();

            if ($curl->isSuccessful()) {
                $newFileSize = $this->_getLocalFileSize($destination);
                if ($urlInfo->info->CONTENT_LENGTH_DOWNLOAD == $newFileSize) {
                    return true;
                } else {
                    throw new FileSizeException("File Downloaded, Failed to verify file size.");
                }
            } else {
                if ($curl->isForbidden()) { //Forbidden
                    throw new Exception("Forbidden Access to the URL.");
                } else {
                    throw new Exception("General Failure to access the URL.");
                }
            }
        } else {
            if ($urlInfo->isForbidden()) { //Forbidden
                throw new Exception("Forbidden Access to the URL.");
            } else {
                throw new Exception("General Failure to access the URL.");
            }
        }

        return false;
    }

}
