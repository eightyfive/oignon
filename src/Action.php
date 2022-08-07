<?php
namespace Oignon;

abstract class Action
{
    abstract public function perform(Layer $image);
}