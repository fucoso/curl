<?php

namespace Fucoso\Curl\Actions\Input;

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

}
