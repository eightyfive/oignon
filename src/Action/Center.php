<?php
namespace Eyf\Oignon\Action;

use Eyf\Oignon\Action;
use Eyf\Oignon\Layer;


class Center extends Action
{
    protected $offsetX;
    protected $offsetY;

    public function __construct($offsetX = 0, $offsetY = 0)
    {
        $this->offsetX = $offsetX;
        $this->offsetY = $offsetY;
    }

    public function perform(Layer $image)
    {
        list($oW, $oH) = $image->getOignon()->getSize();
        list($w, $h) = $image->getSize();

        $x = $oW/2 - $w/2 + $this->offsetX;
        $y = $oH/2 - $h/2 + $this->offsetY;

        $image->setPosition($x, $y);
    }
}