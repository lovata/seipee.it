<?php

namespace Renatio\SeoManager\Classes;

class Robots
{
    protected $path = 'robots.txt';

    public function get()
    {
        if (file_exists($this->path)) {
            return file_get_contents($this->path);
        }

        return $this->setDefaultContent();
    }

    public function set($content)
    {
        file_put_contents($this->path, $content);
    }

    protected function setDefaultContent()
    {
        $content = "User-agent: *\r\nAllow: /";

        file_put_contents($this->path, $content);

        return $content;
    }
}
