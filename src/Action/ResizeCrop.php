<?php
namespace Oignon\Action;

use Oignon\Layer;

class ResizeCrop extends Resize
{
    protected $x;
    protected $y;

    public function __construct($width, $height, $x = 0, $y = 0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->keepRatio = true; // important: Used in parent @see perform()

        $this->x = $x;
        $this->y = $y;

        if (!$width || !$height)
            throw new \InvalidArgumentException('$width AND $height must be specified and not empty');
    }

    public function perform(Layer $image)
    {
        parent::perform($image);
        
        $image->doCrop($this->width, $this->height, $this->x, $this->y);
    }

}