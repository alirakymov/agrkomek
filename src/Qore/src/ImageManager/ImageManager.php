<?php

namespace Qore\ImageManager;

use Qore\Qore;
use Qore\UploadManager;
use Imagine;

class ImageManager
{
    /**
     * file
     *
     * @var mixed
     */
    private $file = null;

    /**
     * width
     *
     * @var mixed
     */
    private $width = null;

    /**
     * height
     *
     * @var mixed
     */
    private $height = null;

    /**
     * @var int|null
     */
    private ?int $pointX = null;

    /**
     * @var int|null
     */
    private ?int $pointY = null;

    /**
     * @var Image
     */
    private $image = null;

    /**
     * init
     *
     * @param UploadManager\UploadedFile $_file
     */
    public function init(UploadManager\UploadedFile $_file)
    {
        $this->file = $_file;
        return $this;
    }

    /**
     * Return array with original sizes [$width, $height]
     *
     * @return array
     */
    public function getOriginalSize() : array
    {
        if (is_null($this->image)) {
            $this->loadImage();
        }

        $box = $this->image->getSize();
        return [$box->getWidth(), $box->getHeight()];
    }

    /**
     * setSize
     *
     * @param int $_width
     * @param int $_height
     *
     * @return ImageManager
     */
    public function setSize(int $_width = null, int $_height = null) : ImageManager
    {
        $this->width = $_width;
        $this->height = $_height;

        return $this;
    }

    /**
     * Set point coordinates for cropping box
     *
     * @param int $_x
     * @param int $_y
     *
     * @return ImageManager
     */
    public function setPoint(int $_x, int $_y) : ImageManager
    {
        $this->pointX = $_x;
        $this->pointY = $_y;

        return $this;
    }

    /**
     * thumbnail
     *
     * @param mixed $_width
     * @param mixed $_height
     */
    public function getUri()
    {
        if (! is_file($this->getImagePath())) {
            $this->resize();
        }

        return $this->getImageUri();
    }

    /**
     * remove
     *
     */
    public function remove()
    {
        $filePath = $this->file->getPath();
        array_map('unlink', glob(substr($filePath, 0, strrpos($filePath, '.')) . '*'));
    }

    /**
     * getImagePath
     *
     */
    protected function getImagePath()
    {
        $originalImagePath = $this->file->getPath();
        return substr($originalImagePath, 0, strrpos($originalImagePath, '.'))
            . $this->getImageSuffix()
            . substr($originalImagePath, strrpos($originalImagePath, '.'));

    }

    /**
     * getImagePath
     *
     */
    protected function getImageUri()
    {
        $originalImagePath = $this->file->getUri();
        return substr($originalImagePath, 0, strrpos($originalImagePath, '.'))
            . $this->getImageSuffix()
            . substr($originalImagePath, strrpos($originalImagePath, '.'));
    }

    /**
     * getImageSuffix
     *
     */
    protected function getImageSuffix() : string
    {
        $return = '';

        if (! is_null($this->width)) {
            $return .= $this->width;
        }

        if (! is_null($this->height)) {
            $return .= 'x' . $this->height;
        }

        return $return !== '' ? '.' . $return : '';
    }

    /**
     * Resize image with setted options
     *
     * @return ImageManager
     */
    public function resize(int $_width = null, int $_height = null) : ImageManager
    {
        if (is_null($this->image)) {
            $this->loadImage();
        }

        $width = $_width ?? $this->width;
        $height = $_height ?? $this->height;

        if (! is_null($width) && ! is_null($height)) {
            $box = new Imagine\Image\Box($width, $height);
        } elseif (! is_null($width)) {
            $box = $this->image->getSize();
            $box = $box->scale($width/$box->getWidth());
        } elseif (! is_null($height)) {
            $box = $this->image->getSize();
            $box = $box->scale($height/$box->getHeight());
        } else {
            return $this;
        }

        $point = ! is_null($this->pointX) && ! is_null($this->pointY)
            ? new Imagine\Image\Point($this->pointX, $this->pointY)
            : null;

        $image = clone $this->image;
        if (! is_null($point)) {
            $image->crop($point, $box)
                 ->save($this->getImagePath());
        } else {
            $image->thumbnail(
                $box,
                Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND | Imagine\Image\ImageInterface::THUMBNAIL_FLAG_UPSCALE
            )->save($this->getImagePath());
        }

        $this->flushOptions();
        return $this;
    }

    /**
     * flush options
     *
     * @return void
     */
    protected function flushOptions()
    {
        $this->width = null;
        $this->height = null;
        $this->pointX = null;
        $this->pointY = null;
    }

    /**
     * Load image resource
     *
     * @return void
     */
    protected function loadImage() : void
    {
        try {
            $imagine = new Imagine\Imagick\Imagine();
            $this->image = $imagine->open($this->file->getPath());
        } catch (\Exception $e) { // \Imagine\Exception\RuntimeException
            # - TODO:  delete this image for future
        }
    }

}
