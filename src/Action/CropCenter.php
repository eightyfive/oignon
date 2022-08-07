<?php
namespace Oignon\Action;

use Oignon\Layer;


class CropCenter extends Crop
{
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function perform(Layer $image)
    {
        list($w, $h) = $image->getSize();

        $this->x = ($w - $this->width) / 2;
        $this->y = ($h - $this->height) / 2;

        parent::perform($image);
    }
}