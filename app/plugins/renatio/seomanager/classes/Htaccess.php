<?php

namespace Renatio\SeoManager\Classes;

class Htaccess
{
    protected $path = '.htaccess';

    public function get()
    {
        if (file_exists($this->path)) {
            return file_get_contents($this->path);
        }

        return '';
    }

    public function set($content)
    {
        file_put_contents($this->path, $content);
    }
}
