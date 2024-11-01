<?php
namespace Shopbop;

/**
 * Categories.
 *
 * @package CoreWidget
 *
 * @author  stickywidget <widgets@stickyeyes.com>
 */
class CoreCategories
{

    /**
     * Widget prefix string for constants.
     *
     * @var string
     */
    public static $widgetPrefix = 'SHOPBOP_';

    /**
     * Select element name.
     *
     * @var string
     */
    private $_selectElementName = '';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
    	$this->_selectElementName = strtolower(constant(self::$widgetPrefix. 'PUBLIC_WIDGET_NAME')).'_category';
    }

    /**
     * Init for edit post page in admin.
     *
     * @return void
     */
	public function init()
	{
		add_action( 'add_meta_boxes', array( &$this, 'add_some_meta_box' ) );
		add_action( 'save_post', array( &$this, 'savePostdata' ) );
	}

    /**
	 * Adds the meta box container
	 */
	public function add_some_meta_box()
	{
        wp_enqueue_script(constant(self::$widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-Admin-js', constant(self::$widgetPrefix.'PLUGIN_DIR_URL') . 'js/admin_category_selector.js?where=settings&modified=20190801');

        //Check for eula agreement if agreed or not.
        $coreWs = new CoreWebservice;

        if(!$coreWs->eulaCheck())
            return;

        $widgetWs = get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'));
        if (is_array($widgetWs) && array_key_exists('registered', $widgetWs) && $widgetWs['registered'] == true)
        {
            add_meta_box(
                'some_meta_box_name',
                constant(self::$widgetPrefix. 'PUBLIC_WIDGET_NAME')." Category",
                array( &$this, 'render_meta_box_content' ),
                'post',
                'side',
                'high'
            );
        }

	}

	/**
	 * Render Meta Box content.
     *
     * @return void
	 */
	public function render_meta_box_content( $post_id )
	{
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

		for ($selectorID=1; $selectorID<=3; $selectorID++) {
			$result = $this->getCategorySelectOptions($post_id->ID, $selectorID);
			if (is_wp_error($result)) {
				echo "<p>Could not get a list of categories</p>";
				return;
			}

			echo $this->renderSelect($this->_selectElementName . '_' . $selectorID, $result);
		}
	}

	/**
	 * Renders a select box.
	 *
	 * @param string $name    The form name of the select element
	 * @param array  $options An array of options for the select element
	 *
	 * @return string
	 */
	public function renderSelect($name, $options)
	{
		$output  = "<select name=\"{$name}\" class=\"category-selector\">";
		foreach($options as $key => $val) {
			$selected = ($val['selected']) ? " selected=\"selected\"" : "";
			$indent   = str_repeat("&nbsp;", $val['indent']*3);

			if(array_key_exists('label', $val))
				$label = $val['value'];
			else
				$label = ucwords(strtolower($val['value']));

			$output  .= "<option value=\"{$val['label']}\"{$selected}>{$indent}{$label}</option>";
		}
		$output .= "</select>";
		return $output;
	}

	/**
	 * Returns an array of options ready for parsing in a select list.
	 * Will mark the selected category for the post unless selectRandomCategory
	 * is flagged. Will also show 'Use Default' is flagged.
	 *
	 * @param integer $post_id
	 * @param bool    $hasDefaultMenuOption Set to true to show the default select option
	 * @param bool    $selectRandomCategory Set to true to select the 'random' option if random is selected, otherwise will select the randomly assigned value
	 *
	 * @return array
	 */
	public function getCategorySelectOptions( $post_id, $selectorId, $hasDefaultMenuOption=true, $selectRandomCategory=true )
	{
		$result = $this->getCategoriesFromCache();
		if(is_wp_error($result)) {
			return new \WP_Error("1", "Could not get categories from cache or API");
		}
		$cats = $result;

        if ($this->isJustArrivedCategory($post_id, $selectorId)) {
            $selectedCategoryPath = -3;
        } else if ($this->isUseDefaultCategory($post_id, $selectorId)) {
            $selectedCategoryPath = -2;
        } else if ($this->isRandomCategory($post_id, $selectorId)) {
            $selectedCategoryPath = -1;
        } else {

            // First we check that a category path has been assigned to this post and selector.
            // if not, we assign one, taking note to assign the appropriate type based on the
            // postID and selectorID. We then call getCategoryPath() to actually assign
            // the category path to the post and selector.
            $selectedCategoryPath = $this->getCategoryPath($post_id, $selectorId);
            if (is_wp_error($selectedCategoryPath)) {
                // no category path for this post and selector? generate one
                if ($post_id == -1) {
                    $this->createCategoryAssignment($post_id, $selectorId, null, false, ($selectorId != 1), ($selectorId == 1)); // create default global category assignment
                } else {
                    $this->createCategoryAssignment($post_id, $selectorId); // create default category assignment (useDefault)
                }
                // now fetch the category path (and assign it if necessary)
				$selectedCategoryPath = $this->getCategoryPath($post_id, $selectorId);

                if ($this->isJustArrivedCategory($post_id, $selectorId)) {
                    $selectedCategoryPath = -3;
                } else if ($this->isUseDefaultCategory($post_id, $selectorId)) {
                    $selectedCategoryPath = -2;
                } else if ($this->isRandomCategory($post_id, $selectorId)) {
                    $selectedCategoryPath = -1;
                }

				if (is_wp_error($selectedCategoryPath)) {
					$selectedCategoryPath = null;
				}
			}
        }
		return $this->getOptionsArray($cats, $selectedCategoryPath, $hasDefaultMenuOption, $selectRandomCategory); // from cache/API
	}

	/**
	 * Returns the categories from the cache. If the cache is invalid, will fetch from
	 * the Reach API and feed the cache, returning the result.
	 *
	 * @return array
	 */
	public function getCategoriesFromCache($cronRun = false)
	{
		$cacheTimestamp  = get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_TIMESTAMP'));
        $lastUpdate      = (int)get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE'));
		$cacheTimeout    = get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_CACHE_TIMEOUT'));
		$categories      = get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES'));
        $widgetWsOptions = get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'));

        if(!array_key_exists('registered', $widgetWsOptions) || $widgetWsOptions['registered'] === false)
            return;


		if($lastUpdate == 0 || $categories=="" || count($categories)==0 || ((((int)gmdate('U')-$lastUpdate)>$cacheTimeout) && $cronRun))
		{
			$result = $this->getCategoriesFromAPI($cacheTimestamp);
			if(!is_wp_error($result))
			{
                $lastUpdate = (int)gmdate('U');
				update_option(constant(self::$widgetPrefix. 'WIDGET_WS_WP_CATEGORIES_TIMESTAMP'), $cacheTimestamp);
                update_option(constant(self::$widgetPrefix. 'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE'), $lastUpdate);
				update_option(constant(self::$widgetPrefix. 'WIDGET_WS_WP_CATEGORIES'), $result);

				return $result;
			} else if (is_null($categories) || is_string($categories) && $categories == "") {
				return $result; // Return Error
			}
            else
            {
                update_option(constant(self::$widgetPrefix. 'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE'), ((int)gmdate('U') - ($cacheTimeout - 3600)));
            }
		}

		return $categories;
	}

	/**
	 * Fetches the category list from the Reach API, setting the
	 * cacheTimestamp.
	 *
	 * @param integer $cacheTimestamp Cache timestamp
	 * @param string  $lang           The language code
	 *
	 * @return array
	 */
	public function getCategoriesFromAPI(&$cacheTimestamp, $lang='en-us')
	{
        $core       = new CoreWidgetBase(self::$widgetPrefix);
        $lang       = $core->getLanguage();
        $coreWs     = new CoreWebservice();
        $url        = '/clients/'.strtolower(constant(self::$widgetPrefix.'PUBLIC_WIDGET_NAME')).'/categories/latest-products-by-category/' . $lang;
		$response   = $coreWs->prepHmacRequest($url, 'GET');
		$statusCode = wp_remote_retrieve_response_code($response);
		if($statusCode != 200)
		{
			return new \WP_Error('$statusCode', 'API Request was not successful', $response);
		}
		$cacheTimestamp = strtotime(wp_remote_retrieve_header($response, "last-modified"));
		$categories     = json_decode(wp_remote_retrieve_body($response), true);
		return $categories;
	}

	/**
	 * Returns a category path for a post. Will generate a random path
	 * if selected, or will pull from the default category etc.
	 *
	 * @param integer $postId                The post id
	 * @param boolean $selectRandomCategory  If the category path is random, true to mark 'Random' as the category path
     * @param boolean $selectDefaultCategory Select default category
	 *
	 * @return string
	 */
	public function getCategoryPathForPost($postId, $selectorId, $selectRandomCategory=true, $selectDefaultCategory=true)
	{
		global $wpdb;

		$sql    = "SELECT category_path, use_default, is_random, is_justarrived FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
		$params = array($postId, $selectorId);
		$stmt   = $wpdb->prepare($sql, $params);
		$row    = $wpdb->get_row($stmt, ARRAY_A);

		if(is_null($row) || is_array($row) && count($row)==0)
		{
			// No category assignment for this post exists?
            if ($postId == -1) {
                $this->createCategoryAssignment($postId, $selectorId, null, false, ($selectorId != 1), ($selectorId == 1)); // create default global category assignment
            } else {
                $this->createCategoryAssignment($postId, $selectorId); // create default category assignment (useDefault)
            }

            // select the newly created category assignment
            $sql    = "SELECT category_path, use_default, is_random, is_justarrived FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
            $params = array($postId, $selectorId);
            $stmt   = $wpdb->prepare($sql, $params);
            $row    = $wpdb->get_row($stmt, ARRAY_A);
		}

		if((int)$row['use_default'] == 0)
		{
			if((int)$row['is_random'] == 1)
			{
				if($row['category_path'] == "")
				{
					// Set random path and update record
					$result = $this->getCategoriesFromCache();
					if(is_wp_error($result))
					{
						return new \WP_Error("1", "Could not get categories from cache");
					}
					$categories = $result;
                    $alreadyRandomlySelectedCategoryPaths = $this->getRandomCategoryPathsByPostID($postId);
					$categoryPath = $this->getRandomCategoryPath($categories, $alreadyRandomlySelectedCategoryPaths);
					$params       = array($categoryPath, $postId, $selectorId);
					$sql          = "UPDATE " . $wpdb->prefix . "shopbop_category_assignments SET category_path=%s WHERE post_id=%s AND selector_id=%s";
					$wpdb->query($wpdb->prepare($sql, $params)); // Check for errors?

					if(!$selectRandomCategory)
						$categoryPath = null;

				} else {
					if($selectRandomCategory)
					{
						$categoryPath = -1;
					} else {
						$categoryPath = $row['category_path'];
					}
				}
			} else {
				$categoryPath = $row['category_path'];
			}
		}
		else if((int)$row['use_default'] == 1 && $row['category_path'] == "" && $row['is_justarrived'] == 0)
		{
			$result = $this->getDefaultCategoryPath($selectorId);
			if(is_wp_error($result))
			{
				return new \WP_Error("1", "Could not get category path for post");
			}
			$categoryPath = $result;
			$params       = array($categoryPath, $postId, $selectorId);
			$sql          = "UPDATE " . $wpdb->prefix . "shopbop_category_assignments SET category_path=%s WHERE post_id=%s AND selector_id=%s";
			$wpdb->query($wpdb->prepare($sql, $params)); // Check for errors?
			if($selectDefaultCategory)
				$categoryPath = -2;
		}
        else if((int)$row['use_default'] == 1 && $selectDefaultCategory)
        {
			$categoryPath = -2;
		}
        else
        {
			$categoryPath = $row['category_path'];
		}

		return $categoryPath;
	}

	/**
	 * Returns the category path assigned to default. If default is
	 * set to 'random category' then returns a random category path.
	 * The default category has a postId of -1.
	 *
	 * @return string
	 */
	public function getDefaultCategoryPath($selectorId, $generateRandomEntry=true, $hideNonPath=true)
	{
		global $wpdb;

		// The default category has a postId of -1
		$sql          = "SELECT category_path, is_random, is_justarrived FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=-1 AND selector_id=%s";
		$stmt         = $wpdb->prepare($sql, $selectorId);
		$row          = $wpdb->get_row($stmt, ARRAY_A);
        $isRandom     = false;
        $categoryPath = null;

        if(is_null($row) || (is_array($row) && count($row)==0) || !is_array($row))
        {
                // Don't exist? Log an error and create a new default entry set to random
                _shopbop_widget_log("CoreCategories::getDefaultCategoryPath(): Default category does not exist (postId=-1 selectorID=" . $selectorId . "missing)");
                $sql = "INSERT IGNORE INTO " . $wpdb->prefix . "shopbop_category_assignments (post_id, selector_id, is_random, use_default, category_path) VALUES (-1, %s, 1, null, null)";
                $wpdb->query($wpdb->prepare($sql, array($selectorId))); // Check for errors?

                $categoryPath = null;
                $isRandom = true;
		}
        else
        {
			$categoryPath = $row['category_path'];
			$isRandom     = (bool)$row['is_random'];
			$isJustArrived = (bool)$row['is_justarrived'];
		}

		if($isRandom === true && $generateRandomEntry)
		{
			$result = $this->getCategoriesFromCache();
			if(is_wp_error($result) || !is_array($result))
			{
				return new \WP_Error("1", "Could not get categories from cache");
			}
            $alreadyRandomlySelectedCategoryPaths = $this->getRandomCategoryPathsByPostID(-1);
			$categoryPath = $this->getRandomCategoryPath($result, $alreadyRandomlySelectedCategoryPaths);
		}

		if($isRandom === true && $hideNonPath === false)
		{
			return -1;
		}

		if($isJustArrived === true)
        {
            return -3;
        }

		return $categoryPath;
	}

	/**
	 * Returns a random valid category path.
	 *
	 * @return string
	 */
	public function getRandomCategoryPath(array $categories, array $filter=null)
	{
		$catList = array();
		$this->generateFlattenedCategoryList($categories, $catList);

        if ($filter != null && is_array($filter) && count($filter)>0) {
            $catList = array_diff($catList, $filter);
        }

		return $catList[rand(0, count($catList)-1)];
	}

	/**
	 * Returns an array of options built from the category tree, with
	 * an option selected by default.
	 *
	 * @param array  $cats                 Heirarchical array of categories
	 * @param string $catId                A slash seperated string marking the tree path to be selected by default
	 * @param bool   $hasDefaultMenuOption Indicates whether the 'Use default' menu option should be shown
	 * @param bool   $showRandomCategory   Indicates that if 'Random Category' has been chosen then the actual random category be selected
	 *
	 * @return array
	 */
	public function getOptionsArray($cats, $catId, $hasDefaultMenuOption=true, $showRandomCategory=true, $showJustArrived=true)
	{
		$options = array();

		$this->expandNodeDetailsCategoryWalker($cats);

		if($showRandomCategory && ($catId != -1 && $catId != -2 && $catId != -3)) {
		    if (!is_array($catId)) {
                $idents = explode('|||', $catId);
            } else {
		        $idents = $catId;
            }

			$this->setSelectedNodeCategoryWalker($cats, $idents);
		}

		$this->populateFlattenedOptionsListCategoryWalker($cats, $options);

		if(!$showRandomCategory || $catId == -1)
		{
			array_unshift($options, array('label'=>-1, 'value'=>'Random Category', 'selected'=>true, 'indent'=>0));
		}
		else
		{
			$selected = ($catId == -1) ? true : false;
			array_unshift($options, array('label'=>-1, 'value'=>'Random Category', 'selected'=>$selected, 'indent'=>0));
		}

		if($hasDefaultMenuOption)
		{
			$selected = ($catId == -2) ? true : false;
			array_unshift($options, array('label'=>-2, 'value'=>'Use default', 'selected'=>$selected, 'indent'=>0));
		}

		if($showJustArrived)
		{
			$selected = ($catId == -3) ? true : false;
			array_unshift($options, array('label'=>-3, 'value'=>'Just Arrived', 'selected'=>$selected, 'indent'=>0));
		}

		return $options;
	}

	/**
	 * Expands a category tree by adding an array under each node which contains
	 * any sub-trees in 'values' and a 'selected' property to indicate if the
	 * node has been selected.
	 *
	 * @param array $item Heirarchical array of categories
	 *
	 * @return void
	 */
	public function expandNodeDetailsCategoryWalker(&$item)
	{
        if(!is_array($item))
            return;

		foreach($item as &$i)
		{
			$i = array('value'=>$i, 'selected'=>false);
			if(is_array($i['value']))
			{
				$this->expandNodeDetailsCategoryWalker($i['value']);
			}
		}
	}

	/**
	 * Sets the selected node in an heirarchical tree of category nodes.
	 * Expects expandNodeDetailsCategoryWalker() to have been called on the
	 * category tree first!
	 *
	 * @param array  $item Expanded heirarchical array of categories
	 * @param string $ref  A slash seperated string marking the tree path to be selected by default
	 *
	 * @return void
	 */
	public function setSelectedNodeCategoryWalker(&$item, &$ref)
	{
        if(!is_array($item))
            return;

		if(is_array($ref) && count($ref)>0)
		{
			foreach($item as $key=>&$i)
			{
				if(count($ref)>0 && $key == $ref[0] && is_array($i))
				{
					array_shift($ref);

					if(count($ref)==0 && array_key_exists('selected', $i))
					{
						$i['selected'] = true;
						return;
					}

					if(count($ref)>0 && array_key_exists('value', $i) && count($i['value'])>0)
					{
						$this->setSelectedNodeCategoryWalker($i['value'], $ref);
					}
				}
			}
		}
	}

	/**
	 * Flattens an expanded heirarchical category tree into a the options array.
	 * The resulting options array can be passed straight to renderSelect() to
	 * be rendered as a select list.
	 *
	 * @param array $item     Expanded heirarchical array of categories
	 * @param array $options  Array to pass the flatended nodes into
	 * @param integer $indent The node indent
	 *
	 * @return void
	 */
	public function populateFlattenedOptionsListCategoryWalker($item, &$options, $indent=0, $parent="")
	{
        if(!is_array($item))
            return;

		foreach($item as $key=>$i)
		{
			$label = ($parent=="") ? $key : $parent.'|||'.$key;
			$key = ucwords(strtolower($key));
			$options[] = array('label'=>$label, 'value'=>$key, 'selected'=>$i['selected'], 'indent'=>$indent);
			if(is_array($i['value']))
			{
				$this->populateFlattenedOptionsListCategoryWalker($i['value'], $options, $indent+1, $label);
			}
		}
	}

	/**
	 * Generates a flat list of category paths.
	 *
	 * @param array  $categories    The heirarchical category tree
	 * @param array  $options       The array to push the flattented tree into
	 * @param string $currentBranch The current branch
	 *
	 * @return void
	 */
	public function generateFlattenedCategoryList(array $categories, array &$options, $currentBranch="")
	{
		foreach($categories as $key=>$i)
		{
			$c         = ($currentBranch == "") ? $key : $currentBranch.'|||'.$key;
			$options[] = $c;
			if(is_array($i))
			{
				$this->generateFlattenedCategoryList($i, $options, $c);
			}
		}
	}

	/**
	 * Called to handle the saving of the category selected via wp-admin.
	 *
	 * @param int $post_id The post id
	 *
	 * @return void
	 */
	public function savePostdata( $post_id )
	{
		global $wpdb;

		// verify if this is an auto save routine.
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if(array_key_exists('myplugin_noncename', $_POST))
        if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
			return;

		// Check permissions
		if ( 'page' == $_POST['post_type'] )
		{
			if ( !current_user_can( 'edit_page', $post_id ) )
				return;
		}
		else
		{
			if ( !current_user_can( 'edit_post', $post_id ) )
				return;
		}

		// OK, we're authenticated: we need to find and save the data
		$categorySelectors = array($this->_selectElementName . '_1', $this->_selectElementName . '_2', $this->_selectElementName . '_3');
		foreach($categorySelectors as $categorySelectorName) {
            if(array_key_exists($categorySelectorName, $_POST)) {
				$selectorId = substr($categorySelectorName, -1);

				// get the current assigned category
                if($this->isJustArrivedCategory($_POST['post_ID'], $selectorId)) {
                    $currentDefaultCategory = '-3';
                }
                else if ($this->isRandomCategory($_POST['post_ID'], $selectorId))
                {
                    $currentDefaultCategory = '-1';
                }
                else if ($this->isUseDefaultCategory($_POST['post_ID'], $selectorId))
                {
                    $currentDefaultCategory = '-2';
                }
                else
                {
					$currentDefaultCategory = $this->getCategoryPath($_POST['post_ID'], $selectorId);
                    $currentDefaultCategory = join('|||', $currentDefaultCategory);
                }

                if ($_POST[$categorySelectorName] != $currentDefaultCategory) {
                    $this->setSelectedCategory($_POST['post_ID'], $_POST[$categorySelectorName], $selectorId);
                }
			}
		}
		return;
	}

	/**
	 * Set the selected category for a post.
	 *
	 * @param int    $post_id          The post id
	 * @param string $selectedCategory The selected category
	 * @param int	 $selectorId       The selector ordinal ID
	 *
	 * @return void
	 */
	public function setSelectedCategory($post_id, $selectedCategory, $selectorId)
	{
		switch ($selectedCategory) {
            case -3: // Just Arrived
                $this->createCategoryAssignment($post_id, $selectorId, null, false, false, true);
                break;

            case -2: // Use Default
                $this->createCategoryAssignment($post_id, $selectorId, null, true, false, false);
                break;

            case -1: // Random
                $this->createCategoryAssignment($post_id, $selectorId, null, false, true, false);
                break;

            default:
                $this->createCategoryAssignment($post_id, $selectorId, $selectedCategory, false, false, false);
        }

        $this->setCategoryAssignmentLastUpdatedTimestamp($post_id, $selectorId);

        return;
	}

	/**
	 * Resets the category for posts that defer to the default category.
	 * If the default category is set to 'random' then will randomly
	 * assign a category for each post.
	 *
	 * @param string $widgetDefaultCategory The default category
	 *
	 * @return array
	 */
	public function resetDeferingPosts($widgetDefaultCategory, $selectorId)
	{
		global $wpdb;

		$sql     = "SELECT post_id, selector_id FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE use_default = 1 AND selector_id=%d";
        $stmt = $wpdb->prepare($sql, $selectorId);
		$rows = $wpdb->get_results($stmt, ARRAY_A);

		if($rows && count($rows)>0)
		{
			return $rows;
		}
		
		return null;
	}

    /**
     * Returns true if the specified category selector is configured to display Just-Arrived category.
     *
     * @param int $postID     The post ID.
     * @param int $selectorID The selector ID.
     *
     * @return int
     */
    public function isJustArrivedCategory($postID, $selectorID) {
        global $wpdb;

        $sql  = "SELECT is_justarrived FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
        $stmt = $wpdb->prepare($sql, array($postID, $selectorID));
        $row  = $wpdb->get_row($stmt, ARRAY_A);

        return (is_array($row) && array_key_exists('is_justarrived', $row) && $row['is_justarrived'] == 1);
    }

    /**
     * Returns true if the specified category selector is configured to display a random category.
     *
     * @param int $postID     The post ID.
     * @param int $selectorID The selector ID.
     *
     * @return int
     */
    public function isRandomCategory($postID, $selectorID) {
        global $wpdb;

        $sql  = "SELECT is_random FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
        $stmt = $wpdb->prepare($sql, array($postID, $selectorID));
        $row  = $wpdb->get_row($stmt, ARRAY_A);

        return (is_array($row) && array_key_exists('is_random', $row) && $row['is_random'] == 1);
    }

    /**
     * Returns true if the specified category selector is configured to use the default category.
     *
     * @param int $postID     The post ID.
     * @param int $selectorID The selector ID.
     *
     * @return int
     */
    public function isUseDefaultCategory($postID, $selectorID) {
        global $wpdb;

        $sql  = "SELECT use_default FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
        $stmt = $wpdb->prepare($sql, array($postID, $selectorID));
        $row  = $wpdb->get_row($stmt, ARRAY_A);

        return (is_array($row) && array_key_exists('use_default', $row) && $row['use_default'] == 1);
    }

    /**
     * Get cat path (and deal with setting new).
     *
     * @param integer $postID
     * @param integer $selectorID
     *
     * @throws \Exception
     *
     * @return array/integer
     */
    public function getCategoryPath($postID, $selectorID)
    {
        global $wpdb;

        $sql = "SELECT category_path FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
        $stmt   = $wpdb->prepare($sql, array($postID, $selectorID));
        $row    = $wpdb->get_row($stmt, ARRAY_A);

        if(!is_array($row) || !array_key_exists('category_path', $row) || is_null($row['category_path']) || strtolower($row['category_path']) == "null" || trim($row['category_path']) == "")
            $currentCategory = null;
        else
            $currentCategory = explode('|||', $row['category_path']);

        $currentLastUpdatedTimestamp = $this->getCategoryAssignmentLastUpdatedTimestamp($postID, $selectorID);


        if(($this->isUseDefaultCategory($postID, $selectorID) || is_null($currentCategory)) && $postID != -1)
        {
            $defaultCat                  = $this->getDefaultCategoryPath($selectorID, true);
            $defaultLastUpdatedTimestamp = $this->getCategoryAssignmentLastUpdatedTimestamp(-1, $selectorID);

            if($defaultCat == -3)
            {
                $defaultCat = null;
                $justArrived = true;
            }
            else
                $justArrived = false;

            if((int)$defaultLastUpdatedTimestamp > $currentLastUpdatedTimestamp || is_null($currentCategory))
            {
                $this->createCategoryAssignment($postID, $selectorID, $defaultCat, true, $this->isRandomCategory(-1, $selectorID), $this->isJustArrivedCategory(-1, $selectorID));
                $this->setCategoryAssignmentLastUpdatedTimestamp($postID, $selectorID, (int)gmdate("U"));
                $currentCategory = (!is_null($defaultCat)) ? explode('|||', $defaultCat) : $defaultCat;

                if($justArrived == true)
                    $currentCategory = -3;
            }
        }
        elseif($this->isRandomCategory($postID, $selectorID) && is_null($currentCategory))
        {
            $categories = $this->getCategoriesFromCache();

			if(is_wp_error($categories) || !is_array($categories))
				return new \WP_Error("1", "Could not get categories from cache");

            $newCategory = $this->getRandomCategoryPath($categories);
            $this->createCategoryAssignment($postID, $selectorID, $newCategory, false, true, false);
            $this->setCategoryAssignmentLastUpdatedTimestamp($postID, $selectorID, (int)gmdate("U"));
            $currentCategory = explode('|||', $newCategory);
        }
        elseif($this->isJustArrivedCategory($postID, $selectorID))
            return -3;

        if(is_null($currentCategory)) {
			return new \WP_Error("2", "No category assigned as default");
		}

        return $currentCategory;
    }

    /**
     * Create (or update) new category assignment
     *
     * @param integer $postID
     * @param integer $selectorID
     * @param null    $categoryPath
     * @param bool    $useDefault
     * @param bool    $isRandom
     * @param bool    $isJustArrived
     *
     * @return void
     */
    public function createCategoryAssignment($postID, $selectorID, $categoryPath=null, $useDefault=true, $isRandom=false, $isJustArrived=false)
    {
        global $wpdb;

        if (is_array($categoryPath)) {
            $categoryPath = join('|||', $categoryPath);
        }

        // we have to do this because wpdb->replace() will set the lastUpdated time to 0000-00-00!!!
        $currentLastModifiedTS = $this->getCategoryAssignmentLastUpdatedTimestamp($postID, $selectorID);
        if($currentLastModifiedTS == 0) {
			$currentLastModifiedTS = time();
		}

        $wpdb->replace(
            $wpdb->prefix . "shopbop_category_assignments",
            array(
                'post_id'        => $postID,
                'selector_id'    => $selectorID,
                'category_path'  => (is_null($categoryPath)) ? '' : $categoryPath,
                'use_default'    => $useDefault,
                'is_random'      => $isRandom,
                'is_justarrived' => $isJustArrived,
                'lastUpdated'    => date('Y-m-d H:i:s', $currentLastModifiedTS),
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%d',
                '%d',
                '%d',
                '%s'

            )
        );
    }

    /**
     * Returns an array of all the randomly selected
     * category paths currently assigned to the postID.
     *
     * @param $postID
     *
     * @return array
     */
    public function getRandomCategoryPathsByPostID($postID)
    {
        global $wpdb;

        $sql = "SELECT category_path FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND is_random=1";
        $stmt   = $wpdb->prepare($sql, array($postID));
        $rows   = $wpdb->get_results($stmt, ARRAY_A);

        if(is_null($rows) || (is_array($rows) && count($rows) == 0))
        {
            return array();
        }

        $paths = array();
        foreach($rows as $row) {
            if (!array_key_exists('category_path', $row) || empty($row['category_path']))
            {
                continue;
            }

            $paths[] = $row['category_path'];
        }

        return $paths;
    }

    /**
     * Returns the lastUpdated timestamp for the category assignment for a post and selector.
     *
     * @param $postID
     * @param $selectorID
     *
     * @return int timestamp
     */
    public function getCategoryAssignmentLastUpdatedTimestamp($postID, $selectorID)
    {
        global $wpdb;

        $sql  = "SELECT lastUpdated FROM " . $wpdb->prefix . "shopbop_category_assignments WHERE post_id=%s AND selector_id=%s";
        $stmt = $wpdb->prepare($sql, array($postID, $selectorID));
        $ts  = $wpdb->get_col($stmt, 0);

        if (!is_array($ts) || count($ts) == 0) {
            return 0;
        }

        return (int)strtotime($ts[0]);
    }

    /**
     * Sets the lastUpdated timestamp for the category assignment for a post and selector.
     *
     * @param $postID
     * @param $selectorID
     * @param $timestamp
     *
     * @return int timestamp
     */
    public function setCategoryAssignmentLastUpdatedTimestamp($postID, $selectorID, $timestamp = null)
    {
        global $wpdb;

        if(is_null($timestamp))
            $timestamp = gmdate('U');

        $ts = date('Y-m-d H:i:s', (int)$timestamp);
        $table  = $wpdb->prefix . "shopbop_category_assignments";
        $values = array('lastUpdated' => $ts);
        $where  = array('post_id' => $postID, 'selector_id' => $selectorID);

        //error_log(sprintf("[INFO] setCategoryAssignmentLastUpdatedTimestamp timestamp: %s", $ts));
        return $wpdb->update($table, $values, $where, array("%s"), array("%d", "%d"));
    }
}