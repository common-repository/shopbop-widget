<?php
namespace Shopbop\RenderContext;

class RenderContext
{
    public function __construct(array $params)
    {
        foreach ($params as $key => $val) {
            $this->$key = $val;
        }

        $this->initPanes();
    }
    
    protected function initPanes()
    {
        for ($n=0; $n<count($this->panes); $n++) {
            $this->panes[$n]->number = $n+1;

            if ($this->panes[$n]->name == $this->activePane) {
                $this->panes[$n]->active = true;
                continue;
            }
            $this->panes[$n]->active = false;
        }
        if ($this->activePane == 'featured' && $this->promotion instanceof Promotion) {
            $this->promotion->active = true;
        }
    }

    /**
     * Unique ID.
     *
     * @var string
     */
    public $id;

    /**
     * Widget width
     *
     * @var string
     */
    public $width;

    /**
     * Active Pane.
     *
     * @var string
     */
    public $activePane;

    /**
     * Marketing Message.
     *
     * @var string
     */
    public $marketingMessage;
    
    /**
     * Promotion.
     *
     * @var RenderContext/Promotion
     */
    public $promotion;
    
    /**
     * RenderContext::Pane
     *
     * @var array RenderContext::Pane
     */
    public $panes = array();

    /**
     * RenderContext::CategoryKeywordLinks
     *
     * @var array RenderContext::CategoryKeywordLinks
     */
    public $categoryKeywordLinks = array();

    /**
     * Google Analytics
     *
     * @var RenderContext::GA
     */
    public $ga;
    
    /**
     * Free Shipping Everywhere text.
     *
     * @var string
     */
    public $freeShippingEverywhereText;
    
    /**
      * Get this widget text.
      *
      * @var string
      */
    public $getThisWidgetText;

    /**
     * Blog domain.
     *
     * @var string
     */
    public $domain;

    /**
     * Translated category keyword links prefix.
     *
     * @var string
     */
    public $categoryKeywordLinksPrefix;

    /**
     * Translated category keyword links suffix.
     *
     * @var string
     */
    public $categoryKeywordLinksSuffix;
}
