<?php
namespace Shopbop;

use Shopbop\RenderContext\RenderContext;
use Shopbop\RenderContext\Panels;
use Shopbop\RenderContext\PanelItem;
use Shopbop\RenderContext\Pane;
use Shopbop\RenderContext\PaneItem;
use Shopbop\RenderContext\Promotion;
use Shopbop\RenderContext\CategoryKeywordLinks;
use Shopbop\RenderContext\Ga;

/**
 * A class that is used to display wordpress front end widget on the side bar.
 *
 * @package Corewidget
 *
 * @author  widget <widget@stickyeyes.com>
 */
class CoreWidgetPublic extends \WP_Widget
{

	/**
	 * Widget prefix string fro sonstants.
	 *
	 * @var string
	 */
	public $widgetPrefix = 'SHOPBOP_';

	/**
	 * Consturctor to add the widget options.
     *
     * @return void
	 */
	public function __construct()
	{
		// widget actual processes
		$widgetOptions  = array(
						   "classname"   => __(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME')),
						   "description" => __(constant($this->widgetPrefix.'PUBLIC_WIDGET_DESCRIPTION')),
						  );
		$controlOptions = array(
						   "width"   => constant($this->widgetPrefix.'WIDGET_DEFAULT_WIDTH'),
						   "height"  => "350",
						   "id_base" => constant($this->widgetPrefix.'PUBLIC_WIDGET_ID_BASE'),
						  );

		parent::__construct(constant($this->widgetPrefix.'PUBLIC_WIDGET_ID_BASE'), __(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME')), $widgetOptions, $controlOptions);


        $core         = new CoreWidgetBase($this->widgetPrefix);
        $languageFile = dirname(__FILE__)  . '/../languages/corewidget-' . $core->getLanguage() . ".mo";

        if(file_exists($languageFile))
            load_textdomain(constant($this->widgetPrefix . 'WIDGET_TRANSLATION'), $languageFile );
	}

	/**
	 * Display the output of the widget.
	 *
	 * @param string $args 	   arguments
	 * @param string $instance values of each fields.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @return void
	 */
	public function widget($args, $instance)
    {
        echo $this->createWidget($args, $instance);
    }

    /**
     * Displays the Widget as a shortcode.
     *
     * @param $attributes
     *
     * @return string
     */
    public function displayShortcodeWidget($attributes)
    {
        return $this->createWidget(null, null);
    }

    /**
     * The main Widget configurator and renderer.
     *
     * @param $args
     * @param $instance
     *
     * @return string
     */
    public function createWidget($args, $instance)
    {
        $optionSettings     = array();
		$widgetOptionsArray = get_option(constant($this->widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'));

		if(isset($widgetOptionsArray['widget_width_type']) && $widgetOptionsArray['widget_width_type'] == 'fluid')
		{
            $widgetWidth = '100%'; // Was auto
            $optionSettings['widget_max_width'] = $widgetOptionsArray['widget_max_width'];
		}
		else
		{
    		if(isset($widgetOptionsArray['widget_width']) && $widgetOptionsArray['widget_width'] >=200 && $widgetOptionsArray['widget_width'] <=constant($this->widgetPrefix.'WIDGET_DEFAULT_MAX_WIDTH'))
    		{
    			$widgetWidth = $widgetOptionsArray['widget_width'] . 'px';
    		}
    		else
    		{
    			$widgetWidth = constant($this->widgetPrefix.'WIDGET_DEFAULT_MAX_WIDTH') . 'px';
    		}
		}

        //widget pane to open
        $widgetPaneToOpen = constant($this->widgetPrefix . 'WIDGET_DEFAULT_PANE_TO_OPEN');

        if($widgetPaneToOpen == 'random')
        {
            $random = array('justarrived', 'shop', 'featured');
            $rand = (int)rand(0,9);
            $open =($rand < 6) ? 0 :(($rand < 9) ? 1 : 2);
            $widgetPaneToOpen = $random[$open];
        }
        $optionSettings['widgetPaneToOpen'] = $widgetPaneToOpen;

		//Renders the HTML code for the widget.
		return $this->widgetHTML($widgetWidth, $optionSettings);
	}

	/**
	 * This function is used to display the html for the widget.
	 *
	 * @param integer $width size of the width to display.
	 * @param string $widgetPaneToOpen widget pane to open.
     * @param array $optionSettings more widget options are passed throught as array.
	 * @return void
	 */
	public function widgetHTML($width, $optionSettings)
	{
        global $post;

        //Check for eula agreement if agreed or not.
        $coreWs = new CoreWebservice;

        if(!$coreWs->eulaCheck())
            return;

        wp_reset_query();
        $coreWebservice = new CoreWebservice(new CoreWidgetUpdate());
        $postId         = (((!is_single() && !is_page()) && !in_the_loop()) || is_front_page()) ? -1 : $post->ID;
        $path           = (is_home() || is_search() || get_permalink($postId) === false) ? get_home_url() : get_permalink($postId);
        //$path           = preg_replace("/(\:\d+)/", '', $path);
		$mktMsg         = false;
		$promotion      = false;
		$coreCategories = new CoreCategories();
		$core           = new CoreWidgetBase($this->widgetPrefix);
        $coreCategories->getCategoriesFromCache();
        
        // New Renderer

        $panes = array(
            $this->createPane($postId, 1, $path, $coreCategories, $coreWebservice),
            $this->createPane($postId, 2, $path, $coreCategories, $coreWebservice),
            $this->createPane($postId, 3, $path, $coreCategories, $coreWebservice),
        );

        $query        = $this->getCategoryKeywordLinksQuery($postId, $coreCategories, $coreWebservice);
        $keywordLinks = $coreWebservice->getCategoryKeywordLinks($postId, $path, $query);

        $categoryKeywordLinks = array();
        for ($n=0; $n<count($keywordLinks); $n++) {
            $categoryKeywordLinks[$n] = array('anchorText'=>$keywordLinks[$n]['label'], 'url'=>$keywordLinks[$n]['url'], 'noFollow' => array_key_exists('hasNoFollow', $keywordLinks[$n]) ? ($keywordLinks[$n]['hasNoFollow'] == 1) : true);
        }
        $categoryKeywordLinks[$n-1]['lastItem']=true;

        $analytics = new Ga(
            array(
                'status'    => (bool)$coreWs->getGaCodeControl(),
                'url'       => plugins_url('core_widget_ga.js', constant($this->widgetPrefix . 'PLUGIN_DIR_PATH') . "views/core_widget_ga.js"),
            )
        );
        $ctx = new RenderContext(
            array(
                'id'                            => $this->gen_uid(),
                'width'                         => $width,
                'maxWidth'                      => (is_array($optionSettings) && array_key_exists('widget_max_width', $optionSettings) && isset($optionSettings['widget_max_width'])) ? $optionSettings['widget_max_width'] : false,
                'activePane'                    => $optionSettings['widgetPaneToOpen'],
                'marketingMessage'              => $mktMsg,
                'promotion'                     => $promotion,
                'panes'                         => $panes,
                'categoryKeywordLinks'          => $categoryKeywordLinks,
                'ga'                            => $analytics,
                'freeShippingEverywhereText'    => __("Free Shipping Everywhere", constant($this->widgetPrefix . 'WIDGET_TRANSLATION')),
                'getThisWidgetText'             => __('Get this widget', constant($this->widgetPrefix . 'WIDGET_TRANSLATION')),
                'domain'                        => parse_url(get_site_url(), PHP_URL_HOST),
                'categoryKeywordLinksPrefix'    => __("Shop", constant($this->widgetPrefix . 'WIDGET_TRANSLATION')),
                'categoryKeywordLinksSuffix'    => __("and more", constant($this->widgetPrefix . 'WIDGET_TRANSLATION')),
                "langCode"                      => $core->getLanguage(),
                "shopBotWidgetForceLocation"    => (is_array($GLOBALS) && array_key_exists('shopBotWidgetForceLocation', $GLOBALS)) ? $GLOBALS['shopBotWidgetForceLocation'] : false,
            )
        );

        // Load mustache template
        $templateFilename = constant($this->widgetPrefix . 'PLUGIN_DIR_PATH') . 'views/widget.mustache';
        $template = "<p>Couldn't load view.</p>";
        if (file_exists($templateFilename) && is_readable($templateFilename)) {
            $template = file_get_contents($templateFilename);
        }

        // render mustache template
        $renderer = new Renderer();
        return $renderer->render($template, $ctx);
    }

    protected function gen_uid(){
        if (CoreWidgetBase::$widgetID<11) {
            CoreWidgetBase::$widgetID++;
        }
        return CoreWidgetBase::$widgetID;
    }

    /**
     * Generated the query required to select keyword links
     * by category path for the specified postID. Iterates
     * over each category selector category path and
     * randomly selects one to use.
     *
     * @param $postID
     * @param CoreCategories $coreCategories
     *
     * @return array
     */
    protected function getCategoryKeywordLinksQuery($postID, CoreCategories $coreCategories, CoreWebservice $coreWebservice)
    {
        $catPaths = array();
        for ($selectorID=1; $selectorID<=3; $selectorID++) {

            // is this pane displaying 'just-arrived' content?
            if ($coreCategories->isJustArrivedCategory($postID, $selectorID) === true) {
                continue; // not a category, move on
            }

            $catPath = $coreCategories->getCategoryPath($postID, $selectorID);

            if (is_wp_error($catPath)) {
                continue;
            }

            if (is_null($catPath)) {
                continue; // couldn't get category path, move on
            }

            $catPaths[] = $catPath;
        }

        // currently, we randomly select a category path
        if (count($catPaths) > 0) {
            $selectedCatPath = $catPaths[rand(0, count($catPaths) - 1)];
        } else {
            $selectedCatPath = array();
        }

        return array("category" => str_replace('\u2215', '∕', json_encode($selectedCatPath)));
    }

    /**
     * Returns a populated Pane object.
     *
     * @param int            $postID         The post ID
     * @param int            $selectorID     The category selector ID
     * @param int            $path           The current page path
     * @param CoreCategories $coreCategories The CoreCategories object
     * @param CoreWebservice $coreWebservice The CoreWebsrevice object
     *
     * @throws \Exception
     *
     * @return Pane
     */
    public function createPane($postID, $selectorID, $path, CoreCategories $coreCategories, CoreWebservice $coreWebservice, $originalPostID=null) {

        $paneID = 'pane' . $selectorID;
        $pane   = null;

        $catPath = $coreCategories->getCategoryPath($postID, $selectorID);

		$isJustArrived = $coreCategories->isJustArrivedCategory($postID, $selectorID);
		if ($isJustArrived === true) {
			$title = __("JUST ARRIVED", constant($this->widgetPrefix . 'WIDGET_TRANSLATION'));
			$catQuery = null;
		} else {
			if (\is_wp_error($catPath) || !is_array($catPath)) {
				$title = null;
				$catQuery = null;
			} else {
				$title = sprintf(__("SHOP %s", constant($this->widgetPrefix . 'WIDGET_TRANSLATION')), __($catPath[count($catPath) - 1], constant($this->widgetPrefix . 'WIDGET_TRANSLATION')));
				$catQuery = array("category" => str_replace('\u2215', '∕', json_encode($catPath)));
			}
		}

		if ($originalPostID == null)
			$content = $coreWebservice->getPaneContent($postID, $paneID, $path, $catQuery, null, null, $isJustArrived);
		else
			$content = $coreWebservice->getPaneContent($originalPostID, $paneID, $path, $catQuery, null, null, $isJustArrived);

		if (\is_wp_error($content) || !is_array($content)) {
			$content = array();
		}

        return new Pane(
            array(
                'name' => $paneID,
                'postID' => $postID,
                'title' => $title,
                'viewText' => __("View", constant($this->widgetPrefix . 'WIDGET_TRANSLATION')),
                'items' => $content,
            )
        );
    }
}