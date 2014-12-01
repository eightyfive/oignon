<?php
namespace Eyf\Oignon\Service;

use Symfony\Component\HttpFoundation\Response;

use Eyf\Oignon\Oignon;

class ImageResizerService
{
    protected $oignon;
    protected $webDir;
    protected $cacheDirName;
    protected $sizes = array();

    public function __construct(Oignon $oignon, $absWebDir, $sizes, $cacheDirName = 'c')
    {
        $this->oignon       = $oignon;
        $this->webDir       = $absWebDir;
        $this->sizes        = $sizes;
        $this->cacheDirName = $cacheDirName;
    }

    public function resize($webSrc, $screen, $size, Response $response = null)
    {
        if (!isset($this->sizes[$screen])) {
            throw new \RuntimeException('Unknown screen size');
        }

        if (!isset($this->sizes[$screen][$size])) {
            throw new \RuntimeException('Unknown image size');
        }

        $webDest = $this->renameImageSrc($webSrc, $screen, $size);

        $src  = $this->webDir.$webSrc;
        $dest = $this->webDir.$webDest;

        $layer = $this->oignon->openFile($src);

        // Resize
        $size = $this->sizes[$screen][$size];
        if (count($size) === 4) {

            list($w, $h, $x, $y) = $size;
            $layer->resizeCrop($w, $h, $x, $y);
        } else {
            
            list($w, $h) = $size;
            $layer->resizeFit($w, $h);
        }
        // $layer->crop($w, $h);

        // Add Watermark
        // if ($size !== 'cover' && $size !== 'small') {

        //     $water = $oignon->importFile($this->getAppRootDir().'/app/Resources/images/watermark.png');
        //     $water->resizeWidth($w * 60/100);
        //     $water->center();
        //     $water->setOpacity(20);
        // }

        // Cache the result
        $this->oignon->saveAs($dest);

        if ($response) {
            $response->headers->set('Content-Type', $this->oignon->getContentType());
            $response->setContent($this->oignon->raw());

            return $response;
        }
    }

    public function renameImageSrc($webSrc, $screen, $size)
    {
        $info = parse_url($webSrc);

        if ($info === false) {
            return $webSrc;
        }

        $crumbs    = explode('/', ltrim($info['path'], '/'));
        $filename  = array_pop($crumbs);
        $webSrcDir = implode('/', $crumbs);

        $path = array($this->cacheDirName, $webSrcDir, $screen, $size, $filename);

        if (isset($info['scheme'])) {
            array_unshift($path, implode('://', array($info['scheme'], $info['host'])));

            $path = implode('/', $path);
        } else {

            // Relative paths needs the leading `/` back.
            $path = '/'.implode('/', $path);
        }

        return $path;
    }
}