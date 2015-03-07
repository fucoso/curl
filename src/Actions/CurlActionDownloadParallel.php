<?php

namespace Fucoso\Curl\Actions;

use Exception;
use Fucoso\Curl\Actions\Input\File;
use Fucoso\Curl\Curl;
use Fucoso\Curl\CurlParallel;
use Fucoso\Curl\Exceptions\FileSizeException;

class CurlActionDownloadParallel extends CurlAction
{

    /**
     * Downloads the given url to the given destination. Tries to resume download if file already exists.
     *
     * @param File destination
     * @param float $chunkSizeMegaBytes
     * @return boolean|null
     */
    public function download(File $downloadFile, $chunkSizeMegaBytes = 500)
    {
        $oldURl = $downloadFile->getUrl();
        $urlInfo = $this->infoFromURL($downloadFile->getUrl());

        if ($oldURl != $urlInfo->effectiveURL()) {
            $downloadFile->getUrl() = $urlInfo->effectiveURL();
            $urlInfo = $this->infoFromURL($downloadFile->getUrl());
        }

        if ($urlInfo->isSuccessful()) {
            $this->output("File Size: {$urlInfo->info->CONTENT_LENGTH_DOWNLOAD} Bytes");
            $fileSize = null;
            if (file_exists($downloadFile->getDestination())) {
                $fileSize = $this->_getLocalFileSize($downloadFile->getDestination());
                if (!$downloadFile->getOverwrite() && $fileSize >= $urlInfo->info->CONTENT_LENGTH_DOWNLOAD) {

                    $possibleChunks = (int) ($urlInfo->info->CONTENT_LENGTH_DOWNLOAD / ($chunkSizeMegaBytes * 1024 * 1024));
                    //Console::increaseIndent();
                    for ($i = 1; $i <= $possibleChunks + 10; $i++) {
                        $chunkFile = $downloadFile->getDestination() . ".{$i}.xpart";
                        if (file_exists($chunkFile)) {
                            $this->output("Deleting {$chunkFile} - Download was verified");
                            unlink($chunkFile);
                        } else {
                            break;
                        }
                    }
                    //Console::decreaseIndent();

                    return true;
                }
            }

            $chunkSize = $chunkSizeMegaBytes * 1024 * 1024;
            //$chunkSize = (int) ($urlInfo->info->CONTENT_LENGTH_DOWNLOAD / $chunks);

            $parallel = new CurlParallel();

            $parallelCurls = [];

            $chunkStart = 0;
            $chunkEnd = -1;
            $i = 0;
            //Console::increaseIndent();
            //for ($i = 0; $i < $chunks; $i ++) {
            while ($chunkStart < $urlInfo->info->CONTENT_LENGTH_DOWNLOAD) {
                $i++;

                $chunkStart = $chunkEnd + 1;

                if ($chunkStart >= $urlInfo->info->CONTENT_LENGTH_DOWNLOAD) {
                    break;
                }

                $chunkEnd = $chunkStart + $chunkSize;
                if ($chunkEnd >= $urlInfo->info->CONTENT_LENGTH_DOWNLOAD) {
                    $chunkEnd = $urlInfo->info->CONTENT_LENGTH_DOWNLOAD;
                }

                $file = $downloadFile->getDestination() . ".{$i}.xpart";


                $curl = new Curl($downloadFile->getUrl());
                $curl->options->SSL_VERIFYPEER = false;
                $curl->options->FOLLOWLOCATION = true;
                $curl->options->RETURNTRANSFER = false;
                $curl->options->CONNECTTIMEOUT = $this->connectTimeout;
                $curl->options->TIMEOUT = $this->curlTimeout;

                $curl->setDestinationFile($file);

                if (!$downloadFile->getOverwrite() && file_exists($file) && $urlInfo->headers->acceptRanges == 'bytes') {
                    $resumeChunkSize = $this->_getLocalFileSize($file);
                    if ($chunkStart + $resumeChunkSize >= $chunkEnd) {
                        //$this->output("Skipping {$file} - Already Downloaded");
                        $parallelCurls[] = $file;
                        continue;
                    }
                    $chunkStart = $chunkStart + $resumeChunkSize;
                    $curl->options->FILE = fopen($file, 'a');
                    $curl->options->RANGE = "{$chunkStart}-{$chunkEnd}";
                    $this->output("Resuming {$file} From {$chunkStart} To {$chunkEnd} Bytes");
                } else {
                    $curl->options->FILE = fopen($file, 'a');
                    $curl->options->RANGE = "{$chunkStart}-{$chunkEnd}";
                    $this->output("Downloading {$file} From {$chunkStart} To {$chunkEnd} Bytes");
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
                $parallelCurls[] = $curl;
                $parallel->add($curl);
            }
            //Console::decreaseIndent();

            if (count($parallelCurls) > 0) {
                $parallel->execute();

                foreach ($parallelCurls as $curl) {
                    if ($curl instanceof Curl) {
                        $curl->close();
                        if (is_resource($curl->options->FILE)) {
                            fclose($curl->options->FILE);
                        }
                        if ($curl->isSuccessful()) {
                            throw new Exception("One or more chunks failed to download.");
                        }
                    }
                }

                //$parallel->close();
            }

            if (file_exists($downloadFile->getDestination())) {
                unlink($downloadFile->getDestination());
            }

            foreach ($parallelCurls as $curl) {
                $readFile = $curl;
                if ($curl instanceof Curl) {
                    $readFile = $curl->getDestinationFile();
                }
                file_put_contents($downloadFile->getDestination(), file_get_contents($readFile), FILE_APPEND);
            }

            $newFileSize = $this->_getLocalFileSize($downloadFile->getDestination());
            $this->output("Downloaded File Size: {$newFileSize} Bytes");
            if ($urlInfo->info->CONTENT_LENGTH_DOWNLOAD == $newFileSize) {
                foreach ($parallelCurls as $curl) {
                    if ($curl instanceof Curl) {
                        unlink($curl->getDestinationFile());
                    } else {
                        unlink($curl);
                    }
                }
                return true;
            } else {
                throw new FileSizeException("File Downloaded, Failed to verify file size.");
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
