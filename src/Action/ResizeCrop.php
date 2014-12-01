<?php
namespace Eyf\Oignon\Action;

use Eyf\Oignon\Layer;

class ResizeCrop extends Resize
{
    protected $cropWidth;
    protected $cropHeight;

    public function __construct($width, $height, $x = null, $y = null)
    {
        $this->width      = $width;
        $this->height     = $height;
        $this->cropWidth  = $width;
        $this->cropHeight = $height;
        $this->x = $x;
        $this->y = $y;

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

        // Resize
        parent::perform($image);

        // Determine x & y coordinates
        if ($this->x === null && $this->y === null)
        {
            $x = ($image->getWidth() - $this->cropWidth) / 2;
            $y = ($image->getHeight() - $this->cropHeight) / 2;
        }
        else {

            if ($this->x === null)
            {
                $x = 0;
                $y = $this->y;
            }
            else if ($this->y === null)
            {
                $x = $this->x;
                $y = 0;
            }
            else {
                $x = $this->x;
                $y = $this->y;
            }
        }
        
        // Crop
        $image->doCrop($this->cropWidth, $this->cropHeight, $x, $y);
    }

}