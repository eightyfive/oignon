<?php
namespace Oignon\Action;

use Oignon\Action;
use Oignon\Layer;


class Resize extends Action
{
    protected $width;
    protected $height;
    protected $keepRatio;

    public function __construct($width, $height, $keepRatio)
    {
        $this->width = $width;
        $this->height = $height;
        $this->keepRatio = $keepRatio;
    }

    public function perform(Layer $image)
    {
        if (!$this->width) {
            $h = $this->height;
            $w = $this->getWidth($image);
        } else if (!$this->height) {
            $w = $this->width;
            $h = $this->getHeight($image);
        } else {
            list($w, $h) = $this->getSize($image);
        }

        // Original width & height
        list($oW, $oH) = $image->getSize();
        
        if ($oW !== $w && $oH !== $h)
            $image->doResize($w, $h);
    }

    protected function getWidth($image)
    {
        if ($this->keepRatio)
            $w = round($this->height * $image->getRatio());
        else
            $w = $image->getWidth();

        return $w;
    }

    protected function getHeight($image)
    {
        if ($this->keepRatio)
            $h = round($this->width * 1/$image->getRatio());
        else
            $h = $image->getHeight();

        return $h;
    }

    protected function getSize($image)
    {
        if ($this->keepRatio) {

            // Priority to width
            $w = $this->width;
            $h = ($w * 1/$image->getRatio());
        } else {
            $w = $this->width;
            $h = $this->height;
        }

        return array($w, $h);
    }
}