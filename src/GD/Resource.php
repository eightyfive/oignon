<?php
namespace Eyf\Oignon\GD;

class Resource
{
    private $resource;
    private $filename;
    private $imageType;

    public function __construct($w = 0, $h = 0)
    {
        if (!empty($w)) {

            if (is_resource($w))
                $this->createFromResource($w);
            else if (is_string($w))
                $this->createFromFile($w, $h);
            else
                $this->createFromSize($w, $h);
        }
    }

    public function createFromSize($w, $h)
    {
        $this->createFromResource(imagecreatetruecolor($w, $h));
    }

    public function createFromJpeg($filename)
    {
        $this->createFromResource(imagecreatefromjpeg($filename));
    }

    public function createFromGif($filename)
    {
        $this->createFromResource(imagecreatefromgif($filename));
    }

    public function createFromPng($filename)
    {
        $this->createFromResource(imagecreatefrompng($filename));
    }

    protected function createFromResource($resource)
    {
        if (false === $resource)
            throw new \RuntimeException('Resource creation failed');

        if (!is_resource($resource))
            throw new \RuntimeException('Resource is not a valid resource');

        if ('gd' !== get_resource_type($resource))
            throw new \RuntimeException('Resource is not a valid GD resource');

        $this->set($resource);
    }

    public function createFromFile($filename, $imageType = null)
    {
        if (!$imageType)
            $imageType = $this->filenameToImageType($filename);

        if ($imageType === IMAGETYPE_JPEG)
            $this->createFromJpeg($filename);

        else if ($imageType === IMAGETYPE_GIF)
            $this->createFromGif($filename);

        else if ($imageType === IMAGETYPE_PNG)
            $this->createFromPng($filename);

        else
            throw new \RuntimeException('Unknown GD image type');

        $this->filename = $filename;
        $this->imageType = $imageType;
    }

    public function get()
    {
        return $this->resource;
    }

    public function set($resource)
    {
        $this->resource = $resource;
    }

    public function getImageType()
    {
        if (!isset($this->imageType) && isset($this->filename))
            $this->imageType = $this->filenameToImageType($this->filename);

        return $this->imageType;
    }

    public function isJpeg()
    {
        return $this->imageType === IMAGETYPE_JPEG;
    }

    public function isGif()
    {
        return $this->imageType === IMAGETYPE_GIF;
    }

    public function isPng()
    {
        return $this->imageType === IMAGETYPE_PNG;
    }

    public function duplicate()
    {
        // /!\ $copy is NOT a `clone`
        // If you delete $this->resource,
        // Then $copy->resource will not be a valid GD resource anymore (type 'Unknown')

        $w = $this->getWidth();
        $h = $this->getHeight();

        $copy = new self($w, $h);
        $copy->saveAlphaChannel(true);
        $copy->paste($this);

        return $copy;
    }

    public function destroy()
    {
        $this->__destruct();
    }

    public function __destruct()
    {
        if (is_resource($this->resource) && 'gd' === get_resource_type($this->resource)) {
            imagedestroy($this->resource);
        }
    }

    public function setAlphaBlending($bool)
    {
        if (false === imagealphablending($this->resource, $bool))
            throw new \RuntimeException('Set alpha blending failed');
    }

    public function setSaveAlpha($bool)
    {
        if (false === imagesavealpha($this->resource, $bool))
            throw new \RuntimeException('Set save alpha failed');
    }

    // http://www.php.net/manual/en/function.imagesavealpha.php
    // "You have to unset alphablending (imagealphablending($im, false)), to use it."
    public function saveAlphaChannel($bool)
    {
        $this->setSaveAlpha($bool);
        $this->setAlphaBlending(!$bool);
    }

    public function setAntiAlias($bool)
    {
        if (function_exists('imageantialias')) {
            imageantialias($this->resource, $bool);
        }
    }

    public function fill($r, $g, $b, $a)
    {
        if (false === imagefill($this->resource, 0, 0, $this->getColor($r, $g, $b, $a)))
            throw new \RuntimeException('Fill operation failed ('.$r.','.$g.','.$b.','.$a.')');
    }

    public function setTransparentColor($r, $g, $b, $a)
    {
        if (false === imagecolortransparent($this->resource, $this->getColor($r, $g, $b, $a)))
            throw new \RuntimeException('Set color as transparent operation failed ('.$r.','.$g.','.$b.','.$a.')');
    }

    public function toGif($filename = null)
    {
        imagegif($this->resource, $filename);
    }

    public function toJpeg($filename = null, $quality = 80)
    {
        imagejpeg($this->resource, $filename, $quality);
    }

    public function toPng($filename = null)
    {
        imagepng($this->resource, $filename);
    }

    public function mergeAlpha(Resource $resource, $opacity, $atX = 0, $atY = 0)
    {
        $this->copymergeAlpha($this->resource, $resource->get(), $atX, $atY, 0, 0, $resource->getWidth(), $resource->getHeight(), $opacity);
    }

    public function merge(Resource $resource, $opacity, $atX = 0, $atY = 0)
    {
        $this->copymerge($this->resource, $resource->get(), $atX, $atY, 0, 0, $resource->getWidth(), $resource->getHeight(), $opacity);
    }

    public function paste(Resource $topaste, $atX = 0, $atY = 0, $fromX = 0, $fromY = 0)
    {
        $this->copy($this->resource, $topaste->get(), $atX, $atY, $fromX, $fromY, $topaste->getWidth(), $topaste->getHeight());
    }

    public function copyTo(Resource $to, $atX = 0, $atY = 0, $fromX = 0, $fromY = 0)
    {
        $this->copy($to->get(), $this->resource, $atX, $atY, $fromX, $fromY, $this->getWidth(), $this->getHeight());
    }

    public function crop($w, $h, $fromX = 0, $fromY = 0)
    {
        $copy = $this->duplicate();

        $this->reset($w, $h);
        $this->paste($copy, 0, 0, $fromX, $fromY);

        $copy->destroy();
    }

    public function resize($w, $h)
    {
        $copy = $this->duplicate();

        $this->reset($w, $h);

        $this->saveAlphaChannel(true);

        if (false === imagecopyresampled($this->resource, $copy->get(), 0, 0, 0, 0, $w, $h, $copy->getWidth(), $copy->getHeight()))
            throw new \RuntimeException('Resize operation failed');

        $copy->destroy();
    }

    public function getWidth()
    {
        return imagesx($this->resource);
    }

    public function getHeight()
    {
        return imagesy($this->resource);
    }

    public function reset($w, $h)
    {
        $this->destroy();
        $this->createFromSize($w, $h);
    }

    private function copy($dest, $src, $destX, $destY, $srcX, $srcY, $srcW, $srcH)
    {
        if (false === imagecopy($dest, $src, $destX, $destY, $srcX, $srcY, $srcW, $srcH))
            throw new \RuntimeException('Copy operation failed');
    }

    private function copymerge($dest, $src, $destX, $destY, $srcX, $srcY, $srcW, $srcH, $opacity)
    {
        if (false === imagecopymerge($dest, $src, $destX, $destY, $srcX, $srcY, $srcW, $srcH, $opacity))
            throw new \RuntimeException('Copy merge operation failed');
    }

    // Hack in order to paste (merge width opacity) transparent images with aplpha channel (png or gif)
    // http://www.redmonkey.org/php-bug-23815
    // http://stackoverflow.com/questions/9514832/adding-some-opacity-on-an-image-with-imagecopymerge-in-php
    private function copymergeAlpha($dest, $src, $destX, $destY, $srcX, $srcY, $srcW, $srcH, $opacity)
    {
        // creating a cut resource
        $cut = imagecreatetruecolor($srcW, $srcH);

        // copying relevant section from background to the cut resource
        imagecopy($cut, $dest, 0, 0, $destX, $destY, $srcW, $srcH);

        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src, 0, 0, $srcX, $srcY, $srcW, $srcH);

        // insert cut resource to destination image
        imagecopymerge($dest, $cut, $destX, $destY, 0, 0, $srcW, $srcH, $opacity);
    }

    private function getColor($r, $g, $b, $a)
    {
        $color = imagecolorallocatealpha($this->resource, $r, $g, $b, $a);

        if (false === $color)
            throw new \RuntimeException('Get color operation failed ('.$r.','.$g.','.$b.','.$a.')');

        return $color;
    }


    public static function fromSize($w, $h)
    {
        $instance = new self();
        $instance->createFromSize($w, $h);

        return $intance;
    }

    public static function fromJpeg($filename)
    {
        $instance = new self();
        $instance->createFromJpeg($filename);

        return $intance;
    }

    public static function fromGif($filename)
    {
        $instance = new self();
        $instance->createFromGif($filename);

        return $intance;
    }

    public static function fromPng($filename)
    {
        $instance = new self();
        $instance->createFromPng($filename);

        return $intance;
    }

    public static function from($w, $h = null)
    {
        if (is_string($w))
            return self::fromFile($w, $h);

        else
            return self::fromSize($w, $h);
    }

    public static function fromFile($filename, $imageType = null)
    {
        $instance = new self();
        $instance->createFromFile($filename, $imageType);

        return $instance;
    }

    public static function filenameToImageType($filename)
    {
        if (function_exists('exif_imagetype'))
            $imageType = exif_imagetype($filename);
        else
            list($w, $h, $imageType) = getimagesize($filename);

        return $imageType;
    }
}