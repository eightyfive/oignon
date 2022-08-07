<?php
namespace Eyf\Oignon\Util;

class FileFormat {

    const JPG = 'jpg';
    const JPEG = 'jpeg';
    const GIF = 'gif';
    const PNG = 'png';

    protected $format;

    public function __construct($format)
    {

        if ($this->isFilename($format))
            $format = pathinfo($format, PATHINFO_EXTENSION);

        $format = strtolower($format);

        switch ($format)
        {
            case self::JPG:
            case self::JPEG:
                $format = self::JPEG;
                break;

            case self::GIF:
            case self::PNG:
                break;

            default:
                throw new \InvalidArgumentException('Unknown format: '. $format);
        }

        $this->format = $format;
    }

    public function getContentType()
    {
        return 'image/'.$this->format;
    }

    public function toGD()
    {
        if ($this->format === self::JPEG)
            return IMAGETYPE_JPEG;

        if ($this->format === self::GIF)
            return IMAGETYPE_GIF;

        if ($this->format === self::PNG)
            return IMAGETYPE_PNG;
    }

    public function isGif()
    {
        return $this->format === self::GIF;
    }

    public function isJpeg()
    {
        return $this->format === self::JPEG;
    }

    public function isPng()
    {
        return $this->format === self::PNG;
    }

    public function toString()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return $this->format;
    }

    public static function isFilename($filename)
    {
        $filename = explode('.', $filename);

        return count($filename) > 1;
    }
}