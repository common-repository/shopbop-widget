<?php
namespace Shopbop\RenderContext;

class PaneItem
{
    const WIDGET_ANCHOR_TEXT_MAX_LENGTH = 50;

    public function __construct($postID, array $item, $viewText)
    {
        $this->noFollow     = ($item['hasNoFollow'] == 1 || $postID < 0  && is_front_page() == false);
        $this->url          = trim(htmlspecialchars($item['url']));
        $this->imageUrl     = trim($item['image']);
        $this->anchorText   = trim($item['anchorText']);
        $this->brandName    = trim($item['brand']['name']);
        $this->viewText     = $viewText;

        if (!empty($item['anchorUrl'])) {
            $this->anchorUrl = htmlspecialchars($item['anchorUrl']);
        }
    }

    public $viewText;
    
    public $noFollow;
    public $url;
    public $imageUrl;
    public $anchorText;
    public $anchorUrl;
    public $brandName;

    public function itemUrl()
    {
        return (empty($this->anchorUrl)) ? $this->url : $this->anchorUrl;
    }

    public function ellidedAnchorText()
    {
        return $this->_ellidedText($this->anchorText, self::WIDGET_ANCHOR_TEXT_MAX_LENGTH);
    }

    public function ellidedBrandName()
    {
        return $this->_ellidedText($this->brandName, self::WIDGET_ANCHOR_TEXT_MAX_LENGTH);
    }

    private function _ellidedText($text, $max)
    {
        return trim((strlen($text) > $max-5) ? substr($text, 0, $max-5) . '...' : $text);
    }
}