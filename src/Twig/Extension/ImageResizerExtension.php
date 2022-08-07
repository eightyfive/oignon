<?php
namespace Eyf\Oignon\Twig\Extension;

use Eyf\Oignon\Service\ImageResizerService;

/**
 * @author Benoit Sagols <benoit.sagols@gmail.com>
 */
class ImageResizerExtension extends \Twig_Extension
{
    protected $resizer;

    public function __construct(ImageResizerService $resizer)
    {
        $this->resizer = $resizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('picture_source', array($this, 'renameSrc'))
        );
    }

    public function renameSrc($webSrc, $screen, $format = null)
    {
        return $this->resizer->renameImageSrc($webSrc, $screen, $format);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'eyf_picture_source';
    }
}
