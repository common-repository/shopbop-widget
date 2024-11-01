<?php
namespace Shopbop\RenderContext;

class PanelItem
{
    public $noFollow;
    public $url;
    public $imageUrl;
    public $anchorUrl;
    public $anchorText;
    public $brandName;

    public function itemUrl()
    {
        return (empty($this->anchorUrl)) ? $this->url : $this->anchorUrl;
    }
}