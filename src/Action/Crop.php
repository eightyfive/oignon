<?php
namespace Eyf\Oignon\Action;

use Eyf\Oignon\Action;
use Eyf\Oignon\Layer;


class Crop extends Action
{
    protected $width;
    protected $height;
    protected $x;
    protected $y;

    public function __construct($width, $height, $x = 0, $y = 0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->x = $x;
        $this->y = $y;
    }

    public function perform(Layer $image)
    {
        $image->doCrop($this->width, $this->height, $this->x, $this->y);
    }
}