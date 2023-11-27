<?php

namespace Qore\UploadManager;

use Qore\Qore;
use Laminas\Diactoros\UploadedFile as DiactorUploadedFile;
use Ramsey\Uuid\Uuid;

class UploadManager
{
    /**
     * paths
     *
     * @var array
     */
    private $paths = null;

    /**
     * __construct
     *
     * @param array $_paths
     */
    public function __construct(array $_paths)
    {
        $this->paths = $_paths;
    }

    /**
     * saveFile
     *
     * @param UploadedFile $_file
     * @param bool $_toPublic
     * @param bool $_toGlobal
     *
     * @return string
     */
    public function saveFile(DiactorUploadedFile $_file, bool $_toPublic = true, bool $_toGlobal = false) : string
    {
        $isImage = false;
        if (is_file($fileUri = $_file->getStream()->getMetadata('uri'))) {
            $isImage = exif_imagetype($fileUri) !== false;
        }

        $uniqid = sha1(Uuid::uuid4()) . '.'
            . ($_toGlobal ? 'g' : 'l')
            . ($_toPublic ? 'p' : 'r')
            . ($isImage ? 'i' : 'f')
            . substr($_file->getClientFilename(), strrpos($_file->getClientFilename(), '.'));

        $path = $this->getDirectory($uniqid);

        ! is_dir($path) && mkdir($path, 0744, true);

        $_file->moveTo($path . DS . $uniqid);

        return $uniqid;
    }

    /**
     * getFile
     *
     * @param mixed $_uniqid
     */
    public function getFile($_uniqid)
    {
        return new UploadedFile($this, $_uniqid);
    }

    /**
     * getFilePath
     *
     * @param string $_uniqid
     */
    public function getFilePath(string $_uniqid)
    {
        return $this->getDirectory($_uniqid) . DS . $_uniqid;
    }

    /**
     * getFileUri
     *
     * @param string $_uniqid
     */
    public function getFileUri(string $_uniqid)
    {
        return str_replace('{uniqid}', $_uniqid, $this->getLocation($_uniqid));
    }

    /**
     * unlinkFile
     *
     */
    public function unlinkFile(string $_uniqid)
    {
        if (file_exists($filePath = $this->getFilePath($_uniqid))) {
            unlink($filePath);
        }
    }

    /**
     * getDirectory
     *
     * @param string $_uniqid
     */
    protected function getDirectory(string $_uniqid) : string
    {
        list($isGlobal, $isPublic, $isImage) = $this->getFileAttributes($_uniqid);

        $path = $this->paths
            [$isGlobal ? 'global' : 'local']
            [$isPublic ? 'public' : 'private']
            [$isImage ? 'images' : 'files']
            ['path'];

        return $path . DS . $this->splitedPath($_uniqid);
    }

    /**
     * getLocation
     *
     * @param string $_uniqid
     */
    protected function getLocation(string $_uniqid)
    {
        list($isGlobal, $isPublic, $isImage) = $this->getFileAttributes($_uniqid);

        $path = $this->paths
            [$isGlobal ? 'global' : 'local']
            [$isPublic ? 'public' : 'private']
            [$isImage ? 'images' : 'files']
            ['uri'];

        return str_replace('{location}', $this->splitedPath($_uniqid), $path);
    }

    /**
     * getFileAttributes
     *
     * @param string $_uniqid
     */
    protected function getFileAttributes(string $_uniqid) : array
    {
        return [
            substr($_uniqid, strpos($_uniqid, '.') + 1, 1) === 'g',
            substr($_uniqid, strpos($_uniqid, '.') + 2, 1) === 'p',
            substr($_uniqid, strpos($_uniqid, '.') + 3, 1) === 'i',
        ];
    }

    /**
     * splitedPath
     *
     * @param string $_uniqid
     */
    protected function splitedPath(string $_uniqid) : string
    {
        return substr($_uniqid, 0, 2)
            . DS . substr($_uniqid, 2, 2)
            . DS . substr($_uniqid, 4, 2)
            . DS . substr($_uniqid, 6, 2)
            . DS . substr($_uniqid, 8, 2)
            . DS . substr($_uniqid, 10, 2)
            . DS . substr($_uniqid, 12, 2);
    }

}
