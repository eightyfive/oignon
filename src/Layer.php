<?php
namespace Eyf\Oignon;

use Eyf\Oignon\Action;

use Eyf\Oignon\Action\Resize as ResizeAction;
use Eyf\Oignon\Action\ResizeFit as ResizeFitAction;
use Eyf\Oignon\Action\ResizeCrop as ResizeCropAction;

use Eyf\Oignon\Action\Crop as CropAction;
use Eyf\Oignon\Action\CropCenter as CropCenterAction;

use Eyf\Oignon\Action\Center as CenterAction;

abstract class Layer
{
    protected $oignon;

    protected $actions = array();

    protected $width;
    protected $height;
    protected $x;
    protected $y;

    protected $filename;
    protected $opacity = 100;

    public function __construct($width, $height = 0, $x = 0, $y = 0)
    {
        if (is_string($width))
            $this->setFilename($width);
        else {
            $this->width = $width;
            $this->height = $height;
        }

        $this->x = $x;
        $this->y = $y;
    }

    abstract protected function _doResize($w, $h);
    abstract protected function _doCrop($w, $h, $x = 0, $y = 0);

    abstract public function paste(Layer $image, $x = 0, $y = 0);
    abstract public function saveAsGif($filename = null);
    abstract public function saveAsJpeg($filename = null, $quality = 80);
    abstract public function saveAsPng($filename = null);
    abstract public function duplicate();
    abstract public function getResource();
    abstract public function reset($width, $height);


    public function fit($crop = false)
    {
        list($w, $h) = $this->oignon->getSize();

        if ($crop)
            $this->addAction(new ResizeCropAction($w, $h));
        else
            $this->addAction(new ResizeFitAction($w, $h));
    }

    public function doResize($w, $h)
    {
        $this->_doResize($w, $h);
        $this->width = $w;
        $this->height = $h;
    }

    public function doCrop($w, $h, $x = 0, $y = 0)
    {
        $this->_doCrop($w, $h, $x, $y);
        $this->width = $w;
        $this->height = $h;
    }

    public function resize($w, $h, $keepRatio = false)
    {
        $this->addAction(new ResizeAction($w, $h, $keepRatio));
    }

    public function crop($w, $h, $x = 0, $y = 0)
    {
        $this->addAction(new CropAction($w, $h, $x, $y));
    }

    public function cropCenter($w, $h)
    {
        $this->addAction(new CropCenterAction($w, $h));
    }

    public function resizeWidth($w, $keepRatio = true)
    {
        $this->resize($w, null, $keepRatio);
    }

    public function resizeHeight($h, $keepRatio = true)
    {
        $this->resize(null, $h, $keepRatio);
    }

    public function resizeFit($w, $h)
    {
        $this->addAction(new ResizeFitAction($w, $h));
    }

    public function resizeCrop($w, $h, $x = 0, $y = 0)
    {
        $this->addAction(new ResizeCropAction($w, $h, $x, $y));
    }

    public function addAction(Action $action)
    {
        array_push($this->actions, $action);
    }

    public function flush()
    {
        while(count($this->actions) > 0) {
            $action = array_shift($this->actions);
            $action->perform($this);
        }
    }

    public function setOignon(Oignon $oignon)
    {
        $this->oignon = $oignon;
    }

    public function getOignon()
    {
        return $this->oignon;
    }

    public function merge(Layer $layer)
    {
        list($x, $y) = $layer->getPosition();
        $this->paste($layer, $x, $y, $layer->getOpacity());
    }

    public function center($offsetX = 0, $offsetY = 0)
    {
        $this->addAction(new CenterAction($offsetX, $offsetY));
    }

    public function getOpacity()
    {
        return $this->opacity;
    }

    public function setOpacity($opacity)
    {
        $this->opacity = $opacity;
    }

    public function setFilename($filename)
    {
        if (!file_exists($filename))
            throw new \RuntimeException('File `'.$filename.'` doesn\'t exist');

        $this->filename = $filename;
    }

    public function getSize()
    {
        return array($this->width, $this->height);
    }

    public function setSize($w, $h)
    {
        return array($w, $h);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getRatio()
    {
        return $this->width / $this->height;
    }

    public function isPortrait()
    {
        return $this->width < $this->height;
    }

    public function isLandscape()
    {
        return $this->width > $this->height;
    }

    public function isSquare()
    {
        return $this->width === $this->height;
    }

    public function getPosition()
    {
        return array($this->x, $this->y);
    }

    public function setPosition($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}