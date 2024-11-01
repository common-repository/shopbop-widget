<?php
namespace Shopbop\RenderContext;

class Pane
{
    private $_postId = 0;
    private $_activePane;
    private $_viewText;

    public $name;
    public $active;
    public $title;
    public $items = array();
    public $number;

    public function __construct(array $params)
    {
        $this->_postId = $params['postID'];
        $this->_viewText = $params['viewText'];
    
        $this->name  = $params['name'];
        $this->title = $params['title'];
        $this->add($params['items']);
    }

    public function add(array $items)
    {
        foreach ($items as $item) {
            $this->items[] = new PaneItem(
                $this->_postId, 
                $item,
                $this->_viewText
            );
        }
    }
}