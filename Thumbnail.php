<?php

namespace Suxesiv;

use Imagick;
use InvalidArgumentException;

/**
 * Class Thumbnail
 * @package Suxesiv
 */
class Thumbnail
{
    const MODE_ADAPTIVE = 'adaptive';
    const MODE_FILLED = 'filled';
    const MODE_BESTFIT = 'bestfit';

    /**
     * @var string
     */
    private $cacheWebFolder;

    /**
     * @var string
     */
    private $cacheAbsoluteFolder;

    /**
     * @var string
     */
    private $src;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $mode;

    /**
     * Thumbnail constructor.
     * @param string $webRoot
     * @param string $cacheFolder
     */
    public function __construct($webRoot, $cacheFolder = '/_thumbnails_')
    {
        $this->cacheWebFolder = $cacheFolder;
        $this->cacheAbsoluteFolder = $webRoot . $this->cacheWebFolder;
        $this->mode = self::MODE_ADAPTIVE;
    }

    /**
     * @param string $src
     * @return $this
     */
    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        if (!in_array($mode, [self::MODE_BESTFIT, self::MODE_FILLED, self::MODE_ADAPTIVE], true)) {
            throw new InvalidArgumentException('Invalid mode provided.');
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function create()
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

    /**
     *
     * @throws \InvalidArgumentException
     */
    private function checkParams()
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

    /**
     * @return string
     */
    private function getCacheName()
    {
        return sha1(sha1($this->src) . $this->width . $this->height . $this->mode) . '.' . $this->getFileExtension($this->src);
    }

    /**
     * @return string
     */
    private function createThumbnail()
    {
        $handle = fopen($this->src, 'rb');
        $imagick = new Imagick();
        $imagick->readImageFile($handle);

        switch ($this->mode) {
            case self::MODE_ADAPTIVE:
                $imagick->cropThumbnailImage($this->width, $this->height);
                break;
            case self::MODE_FILLED:
                $imagick->thumbnailImage($this->width, $this->height, true, true);
                break;
            case self::MODE_BESTFIT:
                $imagick->thumbnailImage($this->width, $this->height, true);
                break;
        }

        return $imagick->getImageBlob();
    }

    /**
     * @param string $path
     * @param mixed|string $content
     */
    private function saveFile($path, $content)
    {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        file_put_contents($path, $content);
    }

    /**
     * @param string $absolutePath
     * @return mixed
     */
    private function getFileExtension($absolutePath)
    {
        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);

        // if url, prevent query string being appended to extension
        preg_match('/^(\.?[a-z0-9_-]+).*$/i', $extension, $matches);

        if (!empty($matches) && isset($matches[1])) {
            $extension = $matches[1];
        }

        return $extension;
    }
}
