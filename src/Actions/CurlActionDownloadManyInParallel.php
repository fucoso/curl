<?php

namespace Fucoso\Curl\Actions;

use Exception;
use Fucoso\Curl\Actions\Input\File;
use Fucoso\Curl\Curl;
use Fucoso\Curl\CurlParallel;

class CurlActionDownloadParallel extends CurlAction
{

    /**
     * Get a curl instance for the file to initiate download, if url exists.
     *
     * @param File $downloadFile
     * @return Curl
     */
    public function setCurlForFile(File &$downloadFile)
    {
        $urlInfo = $this->infoFromURL($downloadFile->getUrl());

        if ($urlInfo->isSuccessful()) {

            $downloadFile->setInfo($urlInfo);

            $fileSize = null;
            if (file_exists($downloadFile->getDestination())) {
                $fileSize = $this->getLocalFileSize($downloadFile->getDestination());
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
                $this->logMessage("Resuming {$downloadFile->getUrl()} From {$fileSize} Bytes");
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

            $downloadFile->setCurl($curl);

            return true;
        } else {
            if ($urlInfo->isForbidden()) { //Forbidden
                throw new Exception("Forbidden Access to the URL.");
            } else {
                throw new Exception("General Failure to access the URL.");
            }
        }

        return false;
    }

    /**
     * Downloads the given url to the given destination. Tries to resume download if file already exists.
     *
     * @param array[File] destination
     * @param float $chunkSizeMegaBytes
     * @return boolean|null
     */
    public function download(array $downloadFiles)
    {
        $parallel = new CurlParallel();
        $parallelCurls = [];

        foreach ($downloadFiles as &$downloadFile) {
            /* @var $downloadFile File */
            try {
                $this->setCurlForFile($downloadFile);
                if ($downloadFile->getCurl()) {
                    $parallel->add($downloadFile->getCurl());
                    $parallelCurls[] = $downloadFile;
                }
            } catch (Exception $ex) {
                $this->logMessage($ex->getMessage());
            }
        }

        if (count($parallelCurls) > 0) {
            $parallel->execute();

            foreach ($parallelCurls as $downloadFile) {
                if ($downloadFile->getCurl() instanceof Curl) {
                    $downloadFile->getCurl()->close();
                    if (is_resource($downloadFile->getCurl()->options->FILE)) {
                        fclose($downloadFile->getCurl()->options->FILE);
                    }

                    if (!$downloadFile->getCurl()->isSuccessful()) {
                        $this->logMessage("{$downloadFile->getUrl()} Failed to download properly.");
                    }
                }
            }
        }

        $result = true;

        foreach ($parallelCurls as $downloadFile) {
            $newFileSize = $this->_getLocalFileSize($downloadFile->getDestination());
            $this->output("Downloaded File Size for {$downloadFile->getUrl()}: {$newFileSize} Bytes");
            if ($downloadFile->getInfo()->info->CONTENT_LENGTH_DOWNLOAD == $newFileSize) {

                $this->logMessage("File Downloaded.");
            } else {
                $this->logMessage("File Downloaded, Failed to verify file size.");
                $result = false;
            }
            $this->logMessage('');
        }

        return $result;
    }

}
