<?php
namespace Shopbop\RenderContext;

class Promotion
{
    public $active = false;
    public $title;
    public $content;

    public function __construct(array $params)
    {
        $this->title  = $params['title'];
        $this->content = $params['content'];
    }
}