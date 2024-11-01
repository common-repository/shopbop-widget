<?php
namespace Shopbop;

/**
 * A class that is used to prepare settings for the wordpress admin widget options.
 *
 * @package Stickywidget
 *
 * @author  widget <widget@stickyeyes.com>
 */
class CoreWidgetAdmin
{
    /**
     * This variable Holds the stickywidget option values.
     *
     * @var array
     */
    public $options;

    /**
     * Widget prefix string fro sonstants.
     *
     * @var string
     */

    public $widgetPrefix = 'SHOPBOP_';

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'widgetMenuPage'));
    }

    /**
     * This function initiates the initial settings for the wordpress admin options page.
     *
     * @return void
     */
    public function init()
    {
        $this->options = get_option(constant($this->widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'));
        $this->widgetRegisterSettingsAndFields();
    }

    
    /**
     * This function displays the stickywidget option menu on the wordpress admin page.
     *
     * @return void
     */
    public function widgetMenuPage()
    {

        $settings = add_menu_page(constant($this->widgetPrefix.'WIDGET_OPTIONS_PAGE_TITLE'), __(constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_TITLE'), 'corewidget'), 'administrator', constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_SLUG_TITLE'), array( $this, 'optionsPage'),plugins_url('shopbop.png', __FILE__));

        add_action( 'load-toplevel_page_Shopbop-core-widget-options', array($this, "actionHooks"));

        // Add JS to the setting page
        add_action('load-'.$settings, array( $this, 'addSettingsScript' ));

        // Load categories is no categories have been cached
        $options = get_option(constant($this->widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'));
        if(is_array($options) && array_key_exists('widgetId', $options) && !is_null($options['widgetId']))
        {
            $categories = get_option(constant($this->widgetPrefix. 'WIDGET_WS_WP_CATEGORIES'));
            if($categories=="" || count($categories)==0)
            {
                $coreCats = new CoreCategories();
                $coreCats->setSelectedCategory(-1, -3, 1);
                $coreCats->setSelectedCategory(-1, -1, 2);
                $coreCats->setSelectedCategory(-1, -1, 3);
            }
        }
    }

    public function actionHooks()
    {
        $redirectUrl = menu_page_url(constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_SLUG_TITLE'), false);

        if(is_array($_REQUEST) && array_key_exists('action', $_REQUEST))
        {
            switch($_REQUEST['action'])
            {
                case "clear_all_settings":
                    CoreWidget::onUninstall();
                    CoreWidget::onActivate();
                    wp_redirect($redirectUrl);
                    exit;
                    break;

                case "set_api_key":
                    $widgetWsOptions = get_option(constant($this->widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'));
                    $currentOptions  = get_option(constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'));

                    if(is_array($widgetWsOptions) && is_array($currentOptions))
                    {
                        if(array_key_exists('widgetId', $widgetWsOptions) && array_key_exists('widget_user_email', $currentOptions))
                        {
                            $widgetWsOptions = array(
                                    'widgetId'       => $_REQUEST['widgetId'],
                                    'registered'     => true,
                                    'resetRequested' => false,
                            );

                            update_option("ShopbopWidgetWsOptions", $widgetWsOptions);
                        }
                    }
                    wp_redirect($redirectUrl);
                    exit;
                    break;

                default:

                    break;
            }
        }
    }

    /**
     *  Add JS to the plugin's settings page.
     *
     * @return void
     */
    public function addSettingsScript()
    {
        global $wp_version;

        wp_enqueue_style(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-Admin-css', constant($this->widgetPrefix.'PLUGIN_DIR_URL') . 'css/admin_widget.css?where=settings&modified=20190801');
        if(version_compare($wp_version,"3.3","<"))
        {
            wp_enqueue_script(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-Admin-js', constant($this->widgetPrefix.'PLUGIN_DIR_URL') . 'js/admin_widget.js?where=settings&modified=20190801');
            wp_enqueue_script(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-Admin-catsel-js', constant($this->widgetPrefix.'PLUGIN_DIR_URL') . 'js/admin_category_selector.js?where=settings&modified=20190801');
            wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js?where=settings');
        }
        else
        {
            wp_enqueue_script(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-Admin-js', constant($this->widgetPrefix.'PLUGIN_DIR_URL') . 'js/admin_widget.js?where=settings', array('jquery-ui-slider'));
            wp_enqueue_script(constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-Admin-catsel-js', constant($this->widgetPrefix.'PLUGIN_DIR_URL') . 'js/admin_category_selector.js?where=settings');
        }

    }


    public function widgetUserEula()
    {
        $params = array();
        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-admin-eula-doc', $params);
    }


    /**
     * This function that displays the stickywidget admin page with its options.
     *
     * @return void
     */
    public function optionsPage()
    {
        if($this->_isRegistered())
        {
            $coreCats = new CoreCategories();
            $cachedCats = $coreCats->getCategoriesFromCache();
            if(!is_array($cachedCats) || count($cachedCats) == 0)
            {
                add_settings_error(
                    'Stickywidget_fetch_category_first_time',
                    'Stickywidget_fetch_category_first_time_error',
                    'We were unable to fetch the categories from the sever.',
                    'error'
                );
            }
        }
        $adminView               = new CoreWidgetBase($this->widgetPrefix);
        $updater                 = new CoreWidgetUpdate();
        $oldestUpdateRequestTime = $updater->getOldestUpdateRequestTimestamp();
        $canCronCreateALockFile  = $updater->testLockFile();
        $cronLockFilePath        = $updater->getLockFileName();
        $adminView->loadView('admin', array(
                                            'oldestEntryTimestamp' => $oldestUpdateRequestTime,
                                            "canCronCreateALockFile" => $canCronCreateALockFile,
                                            "cronLockFilePath" => $cronLockFilePath
                                        )
        );
    }

    /**
     * This function registers the settings and its fields in to the wordpress options list.
     *
     * @return void
     */
    public function widgetRegisterSettingsAndFields()
    {

        register_setting(constant($this->widgetPrefix.'WIDGET_PLUGIN_WP_EULA_AGREEMENT'), constant($this->widgetPrefix.'WIDGET_PLUGIN_WP_EULA_AGREEMENT'), array($this, 'widgetOptionsEulaValidationCb'));
        register_setting(constant($this->widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'), constant($this->widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'), array($this, 'widgetOptionsValidationCb'));
//         Add Appearence sections
        add_settings_section('core_widget_eula_section', '', array($this, 'widgetEulaSectionCb'), 'core_widget_eula_section');
        add_settings_section('core_widget_register_section', '', array($this, 'widgetRegisterSectionCb'), 'core_widget_register_section');
        add_settings_section('core_widget_apperance_section', '', array($this, 'widgetApperanceSectionCb'), 'core_widget_apperance_section');
        add_settings_section('core_widget_force_location', '', array($this, 'widgetForceLocationCb'), 'core_widget_force_location');
        add_settings_section('core_widget_category_section', '', array($this, 'widgetCategorySectionCb'), 'core_widget_category_section');

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').
            'widget_user_eula', '',
            array(
                 $this,
                 'widgetUserEula',
            ),
            'core_widget_eula_section',
            'core_widget_eula_section'
        );
        //Add fields in Appearence sections
        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').
            'widget_user_email', 'User Email',
            array(
                 $this,
                 'widgetUserEmail',
            ),
            'core_widget_register_section',
            'core_widget_register_section'
        );

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').
            'widget_width', 'Width',
            array(
                 $this,
                 'widgetWidth',
            ),
            'core_widget_apperance_section',
            'core_widget_apperance_section'
        );

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'widget_language',
            'Language',
            array(
                 $this,
                 'widgetLanguage',
            ),
            'core_widget_apperance_section',
            'core_widget_apperance_section'
        );

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'widget_force_location',
            'Force Location',
            array(
                 $this,
                 'widgetForceLocation',
            ),
            'core_widget_force_location',
            'core_widget_force_location'
        );
        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'widget_force_selector',
            'Location Selector',
            array(
                 $this,
                 'widgetForceSelector',
            ),
            'core_widget_force_location',
            'core_widget_force_location'
        );

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'_default_category_1',
            'First Category Pane',
            array(
                 $this,
                 'widgetCustomCategory',
            ),
            'core_widget_category_section',
            'core_widget_category_section',
            array('id' => constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'_default_category_1')
        );

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'_default_category_2',
            'Second Category Pane',
            array(
                 $this,
                 'widgetCustomCategory',
            ),
            'core_widget_category_section',
            'core_widget_category_section',
            array('id' => constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'_default_category_2')
        );

        add_settings_field(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'_default_category_3',
            'Third Category Pane',
            array(
                 $this,
                 'widgetCustomCategory',
            ),
            'core_widget_category_section',
            'core_widget_category_section',
            array('id' => constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG').'_default_category_3')
        );

    }

    /**
     * Widget eula call back method.
     *
     * @return void
     */
    public function widgetEulaSectionCb()
    {
        //no-op
    }
    /**
     * Widget Registration call back method.
     *
     * @return void
     */
    public function widgetRegisterSectionCb()
    {
        //no-op
    }

    /**
     * Widget Appereance call back method.
     *
     * @return void
     */
    public function widgetApperanceSectionCb()
    {
        //no-op
    }

    /**
     * Widget Appereance call back method.
     *
     * @return void
     */
    public function widgetCategorySectionCb()
    {
        //no-op
    }

    /**
     * Return true if the widget has been registered or not.
     *
     * @return boolean
     */
    private function _isRegistered()
    {
        $widgetWsOptions = get_option(constant($this->widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'));
        return !(is_array($widgetWsOptions) && !array_key_exists('registered', $widgetWsOptions) || $widgetWsOptions['registered'] === false);
    }

    public function widgetOptionsEulaValidationCb($input = null)
    {
        $eulaOption = $_POST;
        $eulaOptions = array();
        $validInput      = array();

        if($eulaOption['core-widget-eula'] == "Agree")
        {
            $validInput['widget_eula_accept_version'] = constant($this->widgetPrefix . 'WIDGET_VERSION');
        }
        else
        {
            $validInput['widget_eula_accept_version'] = null;
			wp_redirect(admin_url());exit;			
        }
        $validInput = array_merge($eulaOptions, $validInput);
        return $validInput;
    }

    public function widgetForceLocationCb($input = null) { ?>

<?php }

    /**
     * This function is used to validate the entries given in the wordpress admin sticckywidget options.
     *
     * @param mixed $input is used to pass the options submited on the sitckywidget widget.
     *
     * @return array
     */
    public function widgetOptionsValidationCb($input = null)
    {
        $validInput      = array();
        $ws              = new CoreWebservice();
        $widgetWsOptions = get_option(constant($this->widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'));
        $currentOptions  = get_option(constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'));

        if(is_array($widgetWsOptions) && !array_key_exists('registered', $widgetWsOptions) || $widgetWsOptions['registered'] === false || !array_key_exists('widgetId', $widgetWsOptions) || is_null($widgetWsOptions['widgetId']))
        {
            $ws    = new CoreWebservice();
            $url   = get_option('siteurl', null);
            if(is_null($url))
            {
                if(array_key_exists('HTTP_HOST', $_SERVER))
                {
                    $url = (array_key_exists('HTTPS', $_SERVER) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
                }
            }

            $token                             = $ws->widgetRequestToken($input['widget_user_email'], $this->getHost($url));
            $widgetWsOptions['resetRequested'] = false;

            if($token === false)
            {
                $resetEmailResult = $ws->widgetRequestReset($input['widget_user_email'], $this->getHost($url));

                if($resetEmailResult === true)
                    $widgetWsOptions['resetRequested'] = true;
            }
            else
            {
                $widgetWsOptions['widgetId']   = $token;
                $widgetWsOptions['registered'] = false;
            }

            update_option(constant($this->widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'), $widgetWsOptions);

            $validInput['widget_user_email']            = $input['widget_user_email'];
            $validInput['widget_width_type']            = constant($this->widgetPrefix . 'WIDGET_DEFAULT_WIDTH_TYPE');
            $validInput['widget_width']                 = constant($this->widgetPrefix . 'WIDGET_DEFAULT_WIDTH');
            $validInput['widget_max_width']             = constant($this->widgetPrefix . 'WIDGET_DEFAULT_MAX_MAX_WIDTH');
            $validInput['widget_language']              = constant($this->widgetPrefix . 'WIDGET_DEFAULT_LANGUAGE');
            $validInput['widget_force_location']        = $input['widget_force_location'];
            $validInput['widget_force_selector']        = $input['widget_force_selector'];
            $validInput['widget_force_selector_pos']    = $input['widget_force_selector_pos'];

            return $validInput;
        }

        $widgetOptions = $_POST;

        if(array_key_exists('width-type', $widgetOptions))
            $validInput['widget_width_type'] = $widgetOptions['width-type'];

        if(array_key_exists('widget_width_type', $input) && $input['widget_width_type'] == 'fluid')
        {
            $validInput['widget_width'] = null;
        }
        elseif(array_key_exists('widget_width', $input) && ($input['widget_width'] == 'fixed' || $input['widget_width']== null))
        {
            $validInput['widget_width'] = constant(self::$widgetPrefix.'WIDGET_DEFAULT_WIDTH');
        }
        elseif(array_key_exists('widget_width', $input))
        {
            $validInput['widget_width'] = $input['widget_width'];
        }
        if(array_key_exists('widget_max_width', $input))
        {
            $validInput['widget_max_width'] = $input['widget_max_width'];
        }

        if(array_key_exists('widget_language', $input))
            $validInput['widget_language'] = $input['widget_language'];

        if(array_key_exists('widget_force_location', $input))
            $validInput['widget_force_location'] = $input['widget_force_location'];
        
        if(array_key_exists('widget_force_selector', $input))
            $validInput['widget_force_selector'] = $input['widget_force_selector'];

        if(array_key_exists('widget_force_selector_pos', $input))
            $validInput['widget_force_selector_pos'] = $input['widget_force_selector_pos'];

        $categorySelectors = array(
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG')."_default_category_1", 
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG')."_default_category_2", 
            constant($this->widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG')."_default_category_3"
        );
        foreach($categorySelectors as $categorySelectorName) {
            $selectorId = substr($categorySelectorName, -1);
            if(array_key_exists($categorySelectorName, $input))
            {
                $coreCats = new CoreCategories();

                if($coreCats->isJustArrivedCategory(-1, $selectorId)) {
                    $currentDefaultCategory = '-3';
                }
                else if ($coreCats->isRandomCategory(-1, $selectorId))
                {
                    $currentDefaultCategory = '-1';
                }
                else
                {
                    $currentDefaultCategory = $coreCats->getCategoryPath(-1, $selectorId);
					if (is_wp_error($currentDefaultCategory)) {
						$currentDefaultCategory = null;
					} else {
						$currentDefaultCategory = join('|||', $currentDefaultCategory);
					}
                }

                if($input[$categorySelectorName] != $currentDefaultCategory)
                {
                    $coreCats->setSelectedCategory(-1, $input[$categorySelectorName], $selectorId);
                }
                $validInput[$categorySelectorName] = $input[$categorySelectorName];
            }
        }
        if(array_key_exists('widget_language', $currentOptions) && array_key_exists('widget_language', $validInput) && $validInput['widget_language'] != $currentOptions['widget_language'])
        {
            $webService = new CoreWebservice();
            $webService->clearCache();
        }
        $validInput = array_merge($currentOptions, $validInput);

        return $validInput;
    }

    /**
     * Array walker used to get the permalink from an array
     * of post objs.
     *
     * @param array|int $item Post array obj or post id
     *
     * @return void
     */
    private function getPermalink(&$item)
    {
        $item = get_permalink($item);
    }

    /**
     * Array walker used to strip the http scheme from an array or urls.
     *
     * @param array $item Array of urls
     *
     * @return void
     */
    private function stripScheme(&$path)
    {
        $path = str_replace("http://", "", $path);
        $path = str_replace("https://", "", $path);
    }

    /**
     * This is the callback function for the main section.
     *
     * @return void
     */
    public function widgetMainSectionCb()
    {

    }

    /**
     * This function is used to give the user email field to the wordpress admin page.
     *
     * @return void
     */
    public function widgetUserEmail()
    {
        $adminEmail = get_option('admin_email');

        if(isset($this->options['widget_user_email']) && !empty($this->options['widget_user_email']) )
            $adminEmail = $this->options['widget_user_email'];

        $widgetWsOptions = get_option(constant($this->widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'));

        $params = array(
            'locked'      => isset($widgetWsOptions['registered']) && $widgetWsOptions['registered'] == true,
            'optionsName' => constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'),
            'adminEmail'  => $adminEmail,
        );

        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-user-email', $params);
    }

    /**
     * This function is used to specify the width of the widget on the wordpress admin page.
     *
     * @return void
     */
    public function widgetWidth()
    {
        global $wp_version;
        $type   = (array_key_exists('widget_width_type', $this->options)) ? $this->options['widget_width_type'] : constant($this->widgetPrefix . 'WIDGET_DEFAULT_WIDTH_TYPE');
        $width  = (array_key_exists('widget_width', $this->options)) ? $this->options['widget_width'] : null;
        $maxWidth  = (array_key_exists('widget_max_width', $this->options)) ? $this->options['widget_max_width'] : null;
        $params = array(
            'fluid'       => $type,
            'optionsName' => constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'),
            'widgetWidth' => $width,
            'widgetMaxWidth' => $maxWidth,
            'wp_version'  => $wp_version,
        );

        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-width', $params);
    }

    /**
     * Widget pane to open.
     * @return void
     */
    public function widgetPaneToOpen()
    {
        $pane   = constant($this->widgetPrefix . 'WIDGET_DEFAULT_PANE_TO_OPEN');
        $params = array(
            'pane'       => $pane,
        );
        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-pane-to-open', $params);
    }

    /**
     * This function is used to select the category of the widget on the wordpress admin page.
     *
     * @return void
     */
    public function widgetCustomCategory($args)
    {
        $selectorId = substr($args['id'], -1);
        $coreCats = new CoreCategories();
        $result   = $coreCats->getCategorySelectOptions( -1 , $selectorId, false, true );
        if(is_wp_error($result))
        {
            echo "<p>Could not get a list of categories</p>";
            return;
        }

        echo $coreCats->renderSelect('ShopbopWidgetPluginOptions[' . $args['id'] . ']', $result);
        return;
    }

    /**
     * This function iterates through the the array and prepares the categories options.
     *
     * @param array   $category an array of categories.
     * @param integer $indent 	indents the space.
     * @param string  $parent   The parent category branch.
     * @param mixed   $catPath  path to category
     *
     * @return string
     */
    public function categoryRecurse($category, $indent, $parent = null, $catPath = null)
    {
        $ret = '';

        foreach($category as $key => $val)
        {
            $currentPosition = (is_null($parent)) ? $key : $parent . "|||" . $key;

            if($catPath == $currentPosition)
                $ret .= "<option selected=selected";
            else
                $ret .= "<option";

            $ret .= " value=\"$currentPosition\"><b>".str_repeat("&nbsp&nbsp", $indent).'&#746 ' . $key."</option>";

            if(is_array($val))
                $ret .= $this->categoryRecurse($val, $indent+1, $currentPosition, $catPath);
        }

        return $ret;
    }

    /**
     * Given a category tree and a category ID returns the category path
     * e.g. CLOTHES/TOPS/TSHIRTS.
     *
     * @param stdClass $category Category tree
     * @param integer  $id       The category ID
     * @param string   &$catPath The category tree string
     *
     * @return boolean
     */
    public function getCatPath($category, $id, &$catPath)
    {
        foreach($category as $val)
        {
            if($val->term_id == $id)
            {
                $catPath = ($catPath == "") ? $val->name : $catPath . '|||' . $val->name;
                $catPath = strtoupper($catPath);
                return true;
            }

            if(isset($val->children))
            {
                $res = $this->getCatPath($val->children, $id, $catPath);
                if($res == true)
                {
                    $catPath = strtoupper($val->name . '|||' . $catPath);
                    return true;
                }
            }
        }
    }

    /**
     * This function is used to select the language of widget on the wordpress admin page.
     *
     * @return void
     */
    public function widgetLanguage()
    {
        $language = (array_key_exists('widget_language', $this->options)) ? $this->options['widget_language'] : null;
        $params   = array(
            'optionsName'    => constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'),
            'languages'      => array(
                "English (US)"      => "en-us",
                "French (Français)" => "fr-fr",
                "German (Deutsch)"  => "de-de",
                "Chinese (中文)"     => "zh-cn",
                "Japanese (日本語)"  => "ja-jp",
                "Russian (русский)" => "ru-ru",
                "Spanish (Español)" => "es-es",
                "Swedish (Svenska)" => "sv-se",
                "Danish (Dansk)"    => "da-dk",
                "Norwegian (Norsk)" => "no-no",
            ),
            'widgetLanguage' => $language,
        );

        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-language', $params);
    }

    /**
     * This function is force location of widget
     *
     * @return void
     */
    public function widgetForceLocation()
    {
        $forceLocation = (array_key_exists('widget_force_location', $this->options)) ? $this->options['widget_force_location'] : null;
        $params   = array(
            'optionsName'    => constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'),
            'locations'      => array(
                "None (use widget)" => '',
                'After post content' => 'after_post',
                "Before Footer" => 'footer',
                "Custom (Advanced)" => 'custom',
            ),
            'widgetLocation' => $forceLocation,
        );

        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-force-location', $params);
    }
    public function widgetForceSelector()
    {
        $forceSelector = (array_key_exists('widget_force_selector', $this->options)) ? $this->options['widget_force_selector'] : null;
        $forceSelectorPos = (array_key_exists('widget_force_selector_pos', $this->options)) ? $this->options['widget_force_selector_pos'] : null;
        $params   = array(
            'optionsName'    => constant($this->widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'),
            'widgetForceSelector' => $forceSelector,
            'widgetForceSelectorPos' => $forceSelectorPos,
        );
        $widgetView = new CoreWidgetBase($this->widgetPrefix);
        $widgetView->loadView('admin/widget-force-selector', $params);
    }

    /**
     * This function just returns the host name form the given url.
     *
     * @param string $siteUrl this is the blog url.
     *
     * @return string
     */
    function getHost($siteUrl)
    {
        $parseUrl = parse_url(trim($siteUrl));
        return trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
    }
}

new CoreWidgetAdmin();