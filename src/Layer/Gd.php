<?php
namespace Eyf\Oignon\Image;

use Eyf\Oignon\Layer;
use Eyf\Oignon\GD\Resource;

class Gd extends Layer {

    protected $resource;

    protected $imageType;

    protected $alphaBlending;
    protected $saveAlpha;
    protected $antiAlias;
    protected $backgroundColor;
    protected $transparentColor;


    public function setFilename($filename)
    {
        parent::setFilename($filename);

        list($w, $h, $type) = getimagesize($filename);
        
        $this->width = $w;
        $this->height = $h;
        $this->imageType = $type;
    }

    public function saveAsGif($filename = null)
    {
        $this->getGD()->toGif($filename);
    }

    public function saveAsJpeg($filename = null, $quality = 80)
    {
        $this->getGD()->toJpeg($filename, $quality);
    }

    public function saveAsPng($filename = null)
    {
        $this->getGD()->toPng($filename);
    }

    public function paste(Layer $topaste, $x = 0, $y = 0, $opacity = 100)
    {
        $ok = true;

        if ($opacity < 100) {

            if (!$topaste->isJpeg())
                $this->getGD()->mergeAlpha($topaste->getGD(), $opacity, $x, $y);
            else
                $this->getGD()->merge($topaste->getGD(), $opacity, $x, $y);
        }
        else
            $this->getGD()->paste($topaste->getGD(), $x, $y);
    }

    protected function _doResize($w, $h)
    {
        $this->getGD()->resize($w, $h);
    }

    protected function _doCrop($w, $h, $x = 0, $y = 0)
    {
        $this->getGD()->crop($w, $h, $x, $y);
    }

    public function isGif()
    {
        return isset($this->filename) && $this->imageType === IMAGETYPE_GIF;
    }

    public function isJpeg()
    {
        return isset($this->filename) && $this->imageType === IMAGETYPE_JPEG;
    }

    public function isPng()
    {
        return isset($this->filename) && $this->imageType === IMAGETYPE_PNG;
    }

    public function duplicate()
    {
        list($w, $h) = $this->getSize();
        $resource = $this->getGD()->duplicate();

        $copy = new static($w, $h);
        $copy->setGD($resource);

        return $copy;
    }

    public function reset($w, $h)
    {
        $this->resource = new Resource($w, $h);
    }

    public function getResource()
    {
        return $this->getGD()->get();
    }

    protected function getGD()
    {
        if (!isset($this->resource))
            $this->resource = $this->createGD();

        return $this->resource;
    }

    protected function createGD()
    {
        if (isset($this->filename))
            $resource = new Resource($this->filename, $this->imageType);
        else
            $resource = new Resource($this->getWidth(), $this->getHeight());

        // $transparent = imagecolorallocatealpha($resource, 255, 255, 255, 127);
        // imagefill($resource, 0, 0, $transparent);
        // imagecolortransparent($resource, $transparent);

        return $resource;
    }

    protected function setGD(Resource $resource)
    {
        if (isset($this->resource))
            $this->resource->destroy();

        $this->resource = $resource;
    }

    public function __destruct()
    {
        $this->getGD()->destroy();
    }

    public function destroy()
    {
        $this->__destruct();
    }
}