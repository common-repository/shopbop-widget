<?php
namespace Shopbop;

use Shopbop\RenderContext\RenderContext;
use Mustache_Engine;

/**
 * Renders the Widget
 *
 * @package Corewidget
 *
 * @author widget <widget@stickyeyes.com>
 */
class Renderer
{
    /**
     * Renders the Widget
     *
     * @return void
     */
    public function render($template, RenderContext $ctx)
    {
        $m = new Mustache_Engine();
        return $m->render($template, $ctx);
    }
}