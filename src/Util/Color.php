<?php

namespace Eyf\Oignon\Util;

class Color
{
    const TRANSPARENT = 0x7fffffff;
    
    const ALIAS_TRANSPARENT = 'transparent';

    protected $color;

    public function __construct($color) {

        if (is_string($color) && $color === self::ALIAS_TRANSPARENT)
            $color = self::TRANSPARENT;

        $this->color = $color;
    }

    public function getRgba()
    {
        $color = $this->color;

        $b = ($this->color)&0xff;
        $color >>= 8;
        $g = ($color)&0xff;
        $color >>= 8;
        $r = ($color)&0xff;
        $color >>= 8;
        $a = ($color)&0xff;

        return array($r, $g, $b, $a);
    }

    public function isTransparent()
    {
        return $this->color === self::TRANSPARENT;
    }
}