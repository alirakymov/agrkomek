<?php

declare(strict_types=1);

namespace Qore\UploadManager;

class UploadedFile
{
    /**
     * um
     *
     * @var mixed
     */
    private $um = null;

    /**
     * uniqid
     *
     * @var mixed
     */
    private $uniqid = null;

    /**
     * __construct
     *
     * @param UploadManager $_um
     * @param string $_uniqid
     */
    public function __construct(UploadManager $_um, string $_uniqid)
    {
        $this->um = $_um;
        $this->uniqid = $_uniqid;
    }

    /**
     * getPath
     *
     */
    public function getPath()
    {
        return $this->um->getFilePath($this->uniqid);
    }

    /**
     * getUriPath
     *
     */
    public function getUri()
    {
        return $this->um->getFileUri($this->uniqid);
    }

    /**
     * remove
     *
     */
    public function remove()
    {
        return $this->um->unlinkFile($this->uniqid);
    }

}
