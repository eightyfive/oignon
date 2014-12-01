<?php
namespace Eyf\Oignon\Layer;

use Eyf\Oignon\Layer;
use Eyf\Oignon\Util\GdResource;

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
        $this->getGdResource()->toGif($filename);
    }

    public function saveAsJpeg($filename = null, $quality = 80, $progressive = true)
    {
        $this->getGdResource()->toJpeg($filename, $quality, $progressive);
    }

    public function saveAsPng($filename = null)
    {
        $this->getGdResource()->toPng($filename);
    }

    public function paste(Layer $topaste, $x = 0, $y = 0, $opacity = 100)
    {
        $ok = true;

        if ($opacity < 100) {

            if (!$topaste->isJpeg())
                $this->getGdResource()->mergeAlpha($topaste->getGdResource(), $opacity, $x, $y);
            else
                $this->getGdResource()->merge($topaste->getGdResource(), $opacity, $x, $y);
        }
        else
            $this->getGdResource()->paste($topaste->getGdResource(), $x, $y);
    }

    protected function _doResize($w, $h)
    {
        $this->getGdResource()->resize($w, $h);
    }

    protected function _doCrop($w, $h, $x = 0, $y = 0)
    {
        $this->getGdResource()->crop($w, $h, $x, $y);
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
        $resource = $this->getGdResource()->duplicate();

        $copy = new static($w, $h);
        $copy->setGdResource($resource);

        return $copy;
    }

    public function reset($w, $h)
    {
        $this->resource = new GdResource($w, $h);
    }

    protected function getGdResource()
    {
        if (!isset($this->resource))
            $this->resource = $this->createGdResource();

        return $this->resource;
    }

    protected function createGdResource()
    {
        if (isset($this->filename))
            $resource = new GdResource($this->filename, $this->imageType);
        else
            $resource = new GdResource($this->getWidth(), $this->getHeight());

        // $transparent = imagecolorallocatealpha($resource, 255, 255, 255, 127);
        // imagefill($resource, 0, 0, $transparent);
        // imagecolortransparent($resource, $transparent);

        return $resource;
    }

    protected function setGdResource(GdResource $resource)
    {
        if (isset($this->resource))
            $this->resource->destroy();

        $this->resource = $resource;
    }

    public function __destruct()
    {
        $this->getGdResource()->destroy();
    }

    public function destroy()
    {
        $this->__destruct();
    }
}