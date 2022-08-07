<?php
namespace Eyf\Oignon\Action;

use Eyf\Oignon\Layer;

class ResizeCrop extends Resize
{
    protected $cropWidth;
    protected $cropHeight;

    public function __construct($width, $height)
    {
        $this->width      = $width;
        $this->height     = $height;
        $this->cropWidth  = $width;
        $this->cropHeight = $height;

        $this->keepRatio = true; // important: Used in parent @see perform()
    }

    public function perform(Layer $image)
    {
        list($w, $h) = $this->getSize($image);
        
        if ($w < $this->width) {
            $this->height = null;
        }
        if ($h < $this->height) {
            $this->width = null;
        }

        parent::perform($image);

        $x = ($image->getWidth() - $this->cropWidth) / 2;
        $y = ($image->getHeight() - $this->cropHeight) / 2;
        
        $image->doCrop($this->cropWidth, $this->cropHeight, $x, $y);
    }

}