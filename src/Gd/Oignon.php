<?php
namespace Eyf\Oignon\Gd;

use Eyf\Oignon\Oignon as Sprout;
use Eyf\Oignon\Layer\Gd as GdLayer;

class Oignon extends Sprout
{
    protected function newLayer($width, $height = 0, $x = 0, $y = 0)
    {
        return new GdLayer($width, $height, $x, $y);
    }
}