<?php
namespace Eyf\Oignon\Picturefill;

use Eyf\Picturefill\Resizer\ResizerAdapterInterface;

use Eyf\Oignon\Oignon;

class OignonAdapter implements ResizerAdapterInterface
{
    protected $oignon;

    public function __construct(Oignon $oignon)
    {
        $this->oignon = $oignon;
    }

    public function resize($src, $dest, $width, $height = null, $x = null, $y = null)
    {
        $layer = $this->oignon->openFile($src);

        if (!$height)
            $layer->resize($width, null, true);
        else if (!$x)
            $layer->resizeFit($width, $height);
        else
            $layer->resizeCrop($width, $height, $x, $y);

        // Save result on disk
        $this->oignon->saveAs($dest);
    }
}