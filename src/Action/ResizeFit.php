<?php
namespace Eyf\Oignon\Action;

use Eyf\Oignon\Layer;

class ResizeFit extends Resize
{
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->keepRatio = true; // don't need this here (always keep ratio)
    }

    public function perform(Layer $image)
    {
        $w = $this->width;
        $h = $this->height;

        // Wanted ratio
        $ratio = $w / $h;

        // Original width, height & ratio
        $oW = $image->getWidth();
        $oH = $image->getHeight();
        $oRatio = $oW / $oH;

        // Calculate best width & height
        if ($oW <= $w && $oH <= $h) {
            $bW = $oW;
            $bH = $oH;
        } else if ($ratio > $oRatio) {
            $bW = round($h * $oRatio);
            $bH = $h;
        } else {
            $bW = $w;
            $bH = round($w / $oRatio);
        }

        $copy = $image->duplicate();

        // Resize at best width & height
        $copy->doResize($bW, $bH);

        // Paste best resized at.. (center)
        $x = ($w - $bW) /2;
        $y = ($h - $bH) /2;

        // Create a new resource at the wanted width & height
        $image->reset($w, $h);
        $image->paste($copy, $x, $y);

        $copy->destroy();
    }

}