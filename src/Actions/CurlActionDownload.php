<?php

namespace Fucoso\Curl\Actions;

use Exception;
use Fucoso\Curl\Actions\Input\File;
use Fucoso\Curl\Curl;
use Fucoso\Curl\Exceptions\FileSizeException;

class CurlActionDownload extends CurlAction
{

    /**
     * Downloads the given url to the given destination. Tries to resume download if file already exists.
     *
     * @param File $downloadFile
     * @return boolean|null
     */
    public function download(File $downloadFile)
    {

        $urlInfo = $this->infoFromURL($downloadFile->getUrl());

        if ($urlInfo->isSuccessful()) {

            $fileSize = null;
            if (file_exists($downloadFile->getDestination())) {
                $fileSize = $this->_getLocalFileSize($downloadFile->getDestination());
                if (!$downloadFile->getOverwrite() && $fileSize >= $urlInfo->info->CONTENT_LENGTH_DOWNLOAD) {
                    return true;
                }
            }

            $curl = new Curl($downloadFile->getUrl());
            $curl->options->SSL_VERIFYPEER = false;
            $curl->options->FOLLOWLOCATION = true;
            $curl->options->RETURNTRANSFER = false;
            $curl->options->CONNECTTIMEOUT = $this->connectTimeout;
            $curl->options->TIMEOUT = $this->curlTimeout;

            if (!$downloadFile->getOverwrite() && file_exists($downloadFile->getDestination()) && $urlInfo->headers->acceptRanges == 'bytes') {
                $curl->options->FILE = fopen($downloadFile->getDestination(), 'a');
                $curl->options->RESUME_FROM = $fileSize;
                $this->output("Resuming From {$fileSize} Bytes");
            } else {
                $curl->options->FILE = fopen($downloadFile->getDestination(), 'w');
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
            $downloadFile->getUrl() = $curl->effectiveURL();
            $curl->close();

            if ($curl->isSuccessful()) {
                $newFileSize = $this->_getLocalFileSize($downloadFile->getDestination());
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
