<?php

namespace Fucoso\Curl\Actions\Input;

use Fucoso\Curl\Curl;

class File
{

    /**
     *
     * @var string
     */
    private $url;

    /**
     *
     * @var string
     */
    private $destination;

    /**
     *
     * @var boolean
     */
    private $overwrite = false;

    /**
     *
     * @var Curl
     */
    private $curl;

    /**
     *
     * @var Curl
     */
    private $info;

    public function __construct($url, $destination)
    {
        $this->setUrl($url);
        $this->setDestination($destination);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function getOverwrite()
    {
        return $this->overwrite;
    }

    public function getCurl()
    {
        return $this->curl;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    public function setCurl(Curl $curl)
    {
        $this->curl = $curl;
        return $this;
    }

    public function setInfo(Curl $info)
    {
        $this->info = $info;
        return $this;
    }

}
