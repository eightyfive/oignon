<?php
namespace Eyf\Oignon;

abstract class Action
{
    abstract public function perform(Layer $image);
}