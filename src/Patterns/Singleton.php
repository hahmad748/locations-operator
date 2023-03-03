<?php

namespace Devsfort\Location\Patterns;


abstract class Singleton
{
    protected static $instance = null;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null == static::$instance) {
            static::$instance = static::buildInstance();
        }

        return static::$instance;
    }

    public static function buildInstance()
    {
        return new static();
    }
}
