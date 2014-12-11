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

    protected function findSize($webSrc, $screen, $format = null)
    {
        $sizeSets = $this->findSets($webSrc);

        if (!count($sizeSets)) {
            throw new \RuntimeException('No sizes found for image: '.$webSrc);
        }

        if ($format) {
            $sizeSets = $this->findFormatSets($format, $sizeSets);

            if (!count($sizeSets)) {
                throw new \RuntimeException('No sizes found for image identifier `'.$format.'`: '.$webSrc);
            }
        }

        $size = $this->findScreenSize($screen, $sizeSets);

        if (!$size) {
            throw new \RuntimeException('No size found for screen `'.$screen.'` and image: '.$webSrc);
        }

        return $size;
    }

    protected function findSets($webSrc)
    {
        $sizeSets = array();
        $crumbs = explode('/', ltrim($webSrc, '/'));

        while (count($crumbs)) {

            $set = $this->findSet($crumbs);

            if ($set) {
                array_push($sizeSets, $set);
            }

            array_pop($crumbs);
        }


        return $sizeSets;
    }

    protected function findSet(array $crumbs)
    {
        $sizeSet = null;

        while (count($crumbs) && $sizeSet == null) {

            $path = implode('/', $crumbs);
            $sizeSet = isset($this->sizes[$path]) ? $this->sizes[$path] : null;
            array_shift($crumbs);
        }

        return $sizeSet ? $sizeSet : null;
    }

    protected function findFormatSets($format, array $sizeSets)
    {
        $formatSizeSets = array();

        foreach ($sizeSets as $sizeSet) {

            if (isset($sizeSet[$format])) {
                array_push($formatSizeSets, $sizeSet[$format]);
            }
        }

        return $formatSizeSets;
    }

    protected function findScreenSize($screen, array $sizeSets)
    {
        foreach ($sizeSets as $sizeSet) {

            if (isset($sizeSet[$screen])) {

                return $sizeSet[$screen];
            }
        }

        return null;
    }

    public function resize($webSrc, $screen, $format = null, Response $response = null)
    {
        $webDest = $this->renameImageSrc($webSrc, $screen, $format);

        $src  = $this->webDir.$webSrc;
        $dest = $this->webDir.$webDest;

        $layer = $this->oignon->openFile($src);

        // Resize
        $size = $this->findSize($webSrc, $screen, $format);

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

    public function renameImageSrc($webSrc, $screen, $format = null)
    {
        $info = parse_url($webSrc);

        if ($info === false) {
            return $webSrc;
        }

        $crumbs    = explode('/', ltrim($info['path'], '/'));
        $filename  = array_pop($crumbs);
        $webSrcDir = implode('/', $crumbs);

        $path = array($this->cacheDirName, $webSrcDir);
        
        if ($format)
            array_push($path, $format);

        array_push($path, $screen);
        array_push($path, $filename);

        if (isset($info['scheme'])) {
            array_unshift($path, implode('://', array($info['scheme'], $info['host'])));

            $path = implode('/', $path);
        } else {

            // Non-domain paths needs the absolute leading `/` back.
            $path = '/'.implode('/', $path);
        }

        return $path;
    }
}