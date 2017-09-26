<?php

namespace Suxesiv;

use Imagick;
use InvalidArgumentException;

class Thumbnail
{
    const MODE_ADAPTIVE = 'adaptive';
    const MODE_FILLED = 'filled';
    const MODE_BESTFIT = 'bestfit';

    private $cacheWebFolder;
    private $cacheAbsoluteFolder;

    private $src;
    private $width;
    private $height;
    private $mode;

    public function __construct($webRoot, $cacheFolder = '/_thumbnails_')
    {
        $this->cacheWebFolder = $cacheFolder;
        $this->cacheAbsoluteFolder = $webRoot . $this->cacheWebFolder;
        $this->mode = self::MODE_ADAPTIVE;
    }

    public function setSrc(string $src)
    {
        $this->src = $src;
        return $this;
    }

    public function setWidth(int $width)
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
        return $this;
    }

    public function setMode(string $mode)
    {
        if (!in_array($mode, [self::MODE_BESTFIT, self::MODE_FILLED, self::MODE_ADAPTIVE], true)) {
            throw new InvalidArgumentException('Invalid mode provided.');
        }
        $this->mode = $mode;
        return $this;
    }

    public function create() : string
    {
        $this->checkParams();

        $cacheFilename = $this->getCacheName();
        $cacheAbsolutePath = $this->cacheAbsoluteFolder . '/' . $cacheFilename;
        $cacheWebPath = $this->cacheWebFolder . '/' . $cacheFilename;

        if (!is_file($cacheAbsolutePath)) {
            $thumbnail = $this->createThumbnail();
            $this->saveFile($cacheAbsolutePath, $thumbnail);
        }

        return $cacheWebPath;
    }

    private function checkParams() : void
    {
        $requiredProperties = [
            'src',
            'width',
            'height',
            'mode'
        ];

        foreach ($requiredProperties as $property) {
            if (empty($this->{$property})) {
                throw new InvalidArgumentException('Please set a ' . $property);
            }
        }
    }

    private function getCacheName() : string
    {
        return sha1(sha1($this->src) . $this->width . $this->height . $this->mode) . '.' . $this->getFileExtension($this->src);
    }

    private function createThumbnail() : string
    {
        $handle = fopen($this->src, 'rb');
        $imagick = new Imagick();
        $imagick->readImageFile($handle);

        switch ($this->mode) {
            case Thumbnail::MODE_ADAPTIVE:
                $imagick->cropThumbnailImage($this->width, $this->height);
                break;
            case Thumbnail::MODE_FILLED:
                $imagick->thumbnailImage($this->width, $this->height, true, true);
                break;
            case Thumbnail::MODE_BESTFIT:
                $imagick->thumbnailImage($this->width, $this->height, true);
                break;
        }

        return $imagick->getImageBlob();
    }

    private function saveFile($path, $content) : void
    {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        file_put_contents($path, $content);
    }

    private function getFileExtension($absolutePath)
    {
        return pathinfo($absolutePath, PATHINFO_EXTENSION);
    }
}
