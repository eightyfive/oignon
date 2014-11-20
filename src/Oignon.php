<?php

namespace Eyf\Oignon;

use Eyf\Oignon\Util\FileFormat;

use Eyf\Oignon\Action;
use Eyf\Oignon\Action\Resize as ResizeAction;
use Eyf\Oignon\Action\ResizeFit as ResizeFitAction;
use Eyf\Oignon\Action\ResizeCrop as ResizeCropAction;
use Eyf\Oignon\Action\Crop as CropAction;

use Eyf\Oignon\Layer\Gd as ImageLayer;

class Oignon
{
    protected $width;
    protected $height;

    protected $filename;
    protected $fileFormat;

    protected $layers = array();

    public function __construct($width = 0, $height = 0)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getSize()
    {
        return array($this->getWidth(), $this->getHeight());
    }

    public function getWidth()
    {
        if (!empty($this->width))
            return $this->width;

        return $this->layers[0]->getWidth();
    }

    public function getHeight()
    {
        if (!empty($this->height))
            return $this->height;
        
        return $this->layers[0]->getHeight();
    }

    public function addLayer(Layer $layer = null, $atIndex = null)
    {
        if ($layer === null)
            $layer = $this->newLayer($this->width, $this->height);

        if ($atIndex === null)
            $atIndex = count($this->layers);

        $layer->setOignon($this);

        array_splice($this->layers, $atIndex, 0, array($layer));

        return $layer;
    }

    public function openFile($filename)
    {
        $this->layers = array();
        $this->setFilename($filename);

        return $this->importFile($filename);
    }

    public function importFile($filename)
    {
        return $this->addLayer($this->newLayer($filename));
    }

    public function mergeLayers()
    {
        $merged = $this->getMergedLayers();

        $this->layers = array();
        $this->addLayer($merged);

        return $merged;
    }

    public function getMergedLayers()
    {
        $this->flush();

        $merged = null;

        if (!empty($this->width) && !empty($this->height)) {
            $merged = $this->newLayer($w, $w);
            $merged->setOignon($this);
        }

        foreach ($this->layers as $layer) {

            if ($merged === null)
                $merged = $layer;
            else
                $merged->merge($layer);
        }

        return $merged;
    }

    public function save($quality = 80)
    {
        $this->saveAs($this->getFilename(), $this->getFormat(), $quality);
    }

    public function saveAs($filename, $format = null, $quality = 80)
    {   
        // Create directory(ies)
        $directory = dirname($filename);

        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        if (!$format) {
            $fileFormat = new FileFormat($filename);
            $format = $fileFormat->toString();
        }

        $this->saveOrRaw($filename, $format, $quality);
    }

    public function raw($quality = 80)
    {
        return $this->rawAs($this->getFormat(), $quality);
    }

    public function rawAs($format, $quality = 80)
    {
        ob_start();
        $this->saveOrRaw(null, $format, $quality);

        return ob_get_clean();
    }

    public function web($quality = 80)
    {
        $this->webAs($this->getFormat(), $this->getContentType(), $quality);
    }

    public function webAs($format, $contentType = null, $quality = 80)
    {
        if (!$contentType) {
            $fileFormat = new FileFormat($format);
            $contentType = $fileFormat->getContentType();
        }

        header('Content-type: '.$contentType);
        echo $this->rawAs($format, $quality);
    }

    public function flush()
    {
        foreach ($this->layers as $layer) {
            $layer->flush();
        }
    }

    protected function saveOrRaw($filename, $format, $quality)
    {
        $format = new FileFormat($format);

        $layer = $this->getMergedLayers();

        if ($format->isGif())
            $layer->saveAsGif($filename);

        else if ($format->isJpeg())
            $layer->saveAsJpeg($filename);

        else if ($format->isPng())
            $layer->saveAsPng($filename);
    }

    public function setFilename($filename)
    {
        $this->fileFormat = new FileFormat($filename);
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getFormat()
    {
        return isset($this->fileFormat) ? $this->fileFormat->toString() : null;
    }

    public function getContentType()
    {
        return isset($this->fileFormat) ? $this->fileFormat->getContentType() : null;
    }

    protected function newLayer($width, $height = 0, $x = 0, $y = 0)
    {
        return new ImageLayer($width, $height, $x, $y);
    }
}