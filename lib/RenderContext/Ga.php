<?php
namespace Shopbop\RenderContext;

class Ga
{
    public $status;
    public $url;

    public function __construct(array $params)
    {
        $this->status = $params['status'];
        $this->url    = $params['url'];
    }
}