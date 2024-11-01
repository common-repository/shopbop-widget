<?php
namespace Shopbop;

    /**
     * //action handler.
     *
     * @package CoreWidget
     *
     * @author  stickywidget <widgets@stickyeyes.com>
     */
    class CoreWidget
    {

    	/**
    	 * Widget prefix string for constants.
    	 *
    	 * @var string $widgetPrefix
    	 */
    	public static $widgetPrefix = 'SHOPBOP_';

    	/**
    	 * Instantation constructor.
         *
         * @return void
    	 */
    	function __construct()
    	{
    		// Add all action, filter and shortcode hooks
			$this->_addHooks();
			
			$this->registerShortcodes();			
    	}

    	/**
    	 * Add all action, filter and shortcode hooks.
    	 *
    	 * @return void
    	 */
    	private function _addHooks()
    	{
    		/******************************************
    		 * 	 ADD ACTION HOOKS
    		 *****************************************/
    		//Adds admin notices which is used to shows information
    		// like update message, errors messages to alert user
    		add_action('admin_notices', array($this, 'adminNotice'));

    		add_action('admin_init', array($this, 'coreWidgetPluginRedirect'));

    		// register Foo_Widget widget
    		add_action('widgets_init', array($this, 'loadPublicWidget'));

    		//add required files to header.
    		add_action("template_redirect", array($this, 'coreWidgetStylesScripts'));

            //Check if a fresh install has occcured.
            add_action('admin_init', array($this, 'pluginInstalled'));

            //Check if an update has occcured.
            add_action('admin_init', array($this, 'checkUpgradeRequired'));

    		add_action('mktMsgCacheScheduler', array($this, 'mktMsgCache'));

    		add_filter('http_request_timeout', array($this, 'coreFilterTimeoutTime'));
    	}

        /**
         * Run on Post upgrade or post update
         */
        public function runPostUpgradeCode($previousVersion)
        {
            //eula option delete when upgrade to re-agree.
            delete_option(constant(self::$widgetPrefix.'WIDGET_PLUGIN_WP_EULA_AGREEMENT'));

            //Update widget settings to fix 4.3 bug
            $widget   = new CoreWidgetPublic();
            $settings = $widget->get_settings();

            if(is_array($settings))
            {
                foreach($settings as $key => $value)
                {
                    if(is_numeric($key) && !is_array($value))
                    {
                        $settings[$key] = array();
                    }
                }

                if (array_key_exists('widget_width', $settings) && $settings['widget_width'] < constant(self::$widgetPrefix.'WIDGET_DEFAULT_MIN_WIDTH')) {
                    $settings['widget_width'] = constant(self::$widgetPrefix . 'WIDGET_DEFAULT_MIN_WIDTH');
                }
                
                $widget->save_settings($settings);
            }


            if((int)str_replace(".","", $previousVersion) >= 300) {
                /* do nothing */
			} else {
				// upgrade small widget width to minimum acceptable size
				$settings = get_option('ShopbopWidgetPluginOptions');
				if (array_key_exists('widget_width', $settings) && (int)$settings['widget_width'] < (int)constant(self::$widgetPrefix . 'WIDGET_DEFAULT_MIN_WIDTH')) {
					$settings['widget_width'] = constant(self::$widgetPrefix . 'WIDGET_DEFAULT_MIN_WIDTH');
					update_option('ShopbopWidgetPluginOptions', $settings);
				}

				self::loadUpdateQueries();
			}
        }

    	/**
    	 * Http request time out.
    	 *
    	 * @param integer $time time in seconds
    	 *
    	 * @return number
    	 */
    	public function coreFilterTimeoutTime($time)
    	{
    		$time = 25; //new number of seconds
    		return $time;
    	}

    	/**
    	 * Cron job.
    	 *
    	 * @param mixed $schedules every min.
    	 *
    	 * @return array
    	 */
    	public function coreCronWidget($schedules)
    	{
    		//create a 'weekly' recurrence schedule
    		$schedules['every_minute'] = array(
    									  'interval' => 60,
    									  'display'  => 'Every Once Minute',
    							         );

    		return $schedules;
    	}

    	/**
    	 * Loads the scripts and styles in to the public widget.
    	 *
    	 * @return void
    	 */
    	public function coreWidgetStylesScripts()
    	{
    		//CSS links
    		wp_enqueue_style(constant(self::$widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-customjs', constant(self::$widgetPrefix.'PLUGIN_DIR_URL') . 'css/public_widget.css?where=blog&modified=20190801');

    		//javascript librarires
            wp_enqueue_script('jquery', constant(self::$widgetPrefix.'PLUGIN_DIR_URL') . 'js/jquery-1.7.1.min.js');
    		wp_enqueue_script(constant(self::$widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-components', constant(self::$widgetPrefix.'PLUGIN_DIR_URL') . 'js/lib/components.js?where=blog&modified=20190801', array('jquery'));
    		wp_enqueue_script(constant(self::$widgetPrefix.'PUBLIC_WIDGET_NAME_SLUG') . '-customjs', constant(self::$widgetPrefix.'PLUGIN_DIR_URL') . 'js/public_widget.js?where=blog&modified=20190802', array('jquery'), false, false);
    	}

    	/**
    	 * Adds admin notices which is used to shows information like update message, errors messages to alert user.
    	 *
    	 * @return void
    	 */
    	public function adminNotice()
    	{
            //Check for eula agreement if agreed or not.
            $EulaAgree = new CoreWebservice;

            if(!$EulaAgree->eulaCheck())
            {
                ?>
                <div class="updated">
                    <p><?php _e( 'To continue using the Shopbop Widget please read and agree the ' ); ?><a class="button" href="<?php echo admin_url('admin.php?page='.constant(self::$widgetPrefix.'PUBLIC_WIDGET_NAME').'-core-widget-options') ?>">END USER LICENSE AGREEMENT</a></p>
                </div>
                <?php
            }

    		$widgetWs = get_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'));

    		if(!is_array($widgetWs) || (array_key_exists('registered', $widgetWs) && $widgetWs['registered'] == false))
    		{
    		    if(is_null($widgetWs['widgetId']))
    		    {
                    add_settings_error(
                        'Stickywidget_user_registration',
                        'Stickywidget_user_registration_awaiting_id',
                        'Thanks for installing the shopbop widget. Please enter your email address into the ShopBop widget settings and then click "Save Changes".',
                        'updated'
                    );
    		    }
    		    else
    		    {
                    add_settings_error(
                        'Stickywidget_user_registration',
                        'Stickywidget_user_registration_awaiting_email',
                        'Please check your email for an activation link.',
                        'updated'
                    );
    		    }
    		}
    		elseif(array_key_exists('resetRequested', $widgetWs) && $widgetWs['resetRequested'] == true)
    		{
                add_settings_error(
                    'Stickywidget_user_registration',
                    'Stickywidget_user_registration_awaiting_email_reset',
                    'Please check your email for a registration reset link.',
                    'updated'
                );
    		}
    	}

    	/**
    	 * CoreWidgetPluginRedirect.
    	 *
    	 * @return void
    	 */
    	public function coreWidgetPluginRedirect()
    	{
    		
    	}

    	/**
    	 * Wordpress widget activate function.
    	 *
    	 * @return void
    	 */
    	public static function onActivate()
        {
            add_option( 'Shopbop_Activated_Plugin', 'Shopbop_Plugin' );
        }

        public function pluginInstalled() {

            $shopbopActivatedPlugin = get_option( 'Shopbop_Activated_Plugin' );
            if ( is_admin() && $shopbopActivatedPlugin == 'Shopbop_Plugin' ) {
                delete_option('Shopbop_Activated_Plugin');

                self::loadInstallTables();

                $widgetPluginOptions = get_option(constant(self::$widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'), null);

                if (!is_array($widgetPluginOptions)) {
                    $widgetPluginOptions = array();
                }

                add_option(constant(self::$widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'), $widgetPluginOptions);
                update_option(constant(self::$widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'), $widgetPluginOptions);
                update_option(constant(self::$widgetPrefix . 'WIDGET_VERSION_OPTION'), constant(self::$widgetPrefix . 'WIDGET_VERSION'));

                $widgetWsOptions = get_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'));

                if ($widgetWsOptions == false) {
                    add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'), array());
                } else if (!isset($widgetWsOptions['registered']) || $widgetWsOptions['registered'] != true) {
                    add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'), null);

                    $widgetWsOptions = array(
                        'widgetId' => null,
                        'registered' => false,
                    );

                    update_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'), $widgetWsOptions);
                }

                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES'), null);
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_TIMESTAMP'), null);
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE'), null);
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_CACHE_TIMEOUT'), (int)constant(self::$widgetPrefix . 'WIDGET_WS_WP_CACHE_TIMEOUT'));
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_FAIL'), null);
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_SUCCESS'), null);
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_REQUESTED_DATE'), null);
                add_option(constant(self::$widgetPrefix . 'WIDGET_WS_WP_THROTTLE_TIME_START'), null);
                add_option(constant(self::$widgetPrefix . 'ACTIVATE_PLUGIN_REDIRECT'), true);
            }
    	}

    	/**
    	 * Wordpress widget deactivate function.
    	 *
    	 * @return void
    	 */
    	public static function onDeactivate()
    	{
            update_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_FAIL'), null);
			update_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_SUCCESS'), null);
    	}

		public function registerShortcodes()
		{
			$widget = new CoreWidgetPublic();
			add_shortcode('shopbop', array($widget, 'displayShortcodeWidget'));
		}

		public function deregisterShortcodes()
		{
			remove_shortcode('shopbop');
		}

    	/**
    	 * Wordpress widget uninstall function.
    	 *
    	 * @return void
    	 */
    	public static function onUninstall()
    	{
            $widgetWsOptions = get_option(constant(self::$widgetPrefix. 'WIDGET_WS_WP_OPTIONS_NAME'));

            if(is_array($widgetWsOptions) && array_key_exists('widgetId', $widgetWsOptions) && !is_null($widgetWsOptions['widgetId']))
            {
        		//Delete the widget (API call) if we have a key.
        		$webService = new CoreWebservice();
        		$webService->deleteWidget();
            }

    		//Uninstall the tables.
    		self::loadUninstallTables();

    		//Delete the options
    		delete_option(constant(self::$widgetPrefix.'WIDGET_PLUGIN_WP_EULA_AGREEMENT'));
    		delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'));
    		delete_option(constant(self::$widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'));
    		delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_CATEGORIES'));
    		delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_CATEGORIES_TIMESTAMP'));
            delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE'));
    		delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_CATEGORIES_CACHE_TIMEOUT'));
            delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_FAIL'));
            delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_SUCCESS'));
            delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_INTERNAL_UPDATE_REQUESTED_DATE'));
            delete_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_THROTTLE_TIME_START'));
            delete_option(constant(self::$widgetPrefix.'WIDGET_PLUGIN_WP_ENABLE_GOOGLE_ANALYTICS'));
    	}



        /**
         * Adds the Widget tables to the WordPress Database.
         * Also applies a simple patch to add a missing column for
         * more recent versions of the Widget.
         *
         * @return void
         */
        public function loadInstallTables()
        {
            global $wpdb;

            $queries = explode('|', file_get_contents(constant('SHOPBOP_PLUGIN_DIR_PATH') .'sql/tables.sql'));

            $wpdb->show_errors = TRUE;
            $wpdb->suppress_errors = FALSE;

            foreach($queries as $q) /* @var $q string */
            {
                $q = str_replace('%PREFIX%', $wpdb->prefix, $q);
                $wpdb->query($q);

                if ($wpdb->last_error) {
                    die('error=' . var_dump($wpdb->last_query) . ',' . var_dump($wpdb->error));
                }
            }

        }
        /**
         * Adds the Widget tables to the WordPress Database.
         * Also applies a simple patch to add a missing column for
         * more recent versions of the Widget.
         *
         * @return void
         */
        public function loadUpdateQueries()
        {
            global $wpdb;

            $queries = explode("\n", file_get_contents(constant(self::$widgetPrefix.'PLUGIN_DIR_PATH') .'sql/updateQueries.sql'));

            foreach($queries as $q) /* @var $q string */
            {
				$q = trim(str_replace('%PREFIX%', $wpdb->prefix, $q));
				if(!is_null($q) && (is_string($q) && strlen($q)>0)) {
                    $wpdb->query($q);
                }
            }

        }

        /**
    	 * Initilizes and loads the public widget.
    	 *
    	 * @return void
    	 */
    	public function loadPublicWidget()
    	{
            $widgetWsOptions = get_option(constant(self::$widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'));
    		if(isset($widgetWsOptions['registered']) && $widgetWsOptions['registered'] == true)
    		{
    			register_widget('Shopbop\CoreWidgetPublic');
			}
			
			// Are we forcing placement?
			$widgetPluginOptions = get_option(constant(self::$widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'));
			$forceLocation = (is_array($widgetPluginOptions) && array_key_exists('widget_force_location', $widgetPluginOptions)) ? $widgetPluginOptions['widget_force_location'] : null;
			// After the_content()
			if($forceLocation == 'after_post') {
				add_filter('the_content', function($content) {
					ob_start();
					the_widget('Shopbop\CoreWidgetPublic');
					$widgetHTML = ob_get_clean();
					return $content.$widgetHTML;
				});
			}
			// Before get_footer()
			elseif($forceLocation == 'footer') {
				add_action('get_footer', function() {
					the_widget('Shopbop\CoreWidgetPublic');
				});
			}
			// Custom goes in get_footer() too, but with args
			elseif($forceLocation == 'custom') {
				add_action('get_footer', function() {
					$widgetPluginOptions = get_option(constant(self::$widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME'));
					global $shopBotWidgetForceLocation;
					$shopBotWidgetForceLocation = $widgetPluginOptions['widget_force_selector'].':'.$widgetPluginOptions['widget_force_selector_pos'];
					the_widget('Shopbop\CoreWidgetPublic');
					unset($shopBotWidgetForceLocation);
				});
			}
    	}

        /**
         * Check if an upgrade has occurred.
         *
         * @return void
         */
        public function checkUpgradeRequired()
        {
			$currentVersion = get_option(constant(self::$widgetPrefix.'WIDGET_VERSION_OPTION'));
            if($currentVersion != constant(self::$widgetPrefix.'WIDGET_VERSION'))
            {
                update_option(constant(self::$widgetPrefix.'WIDGET_VERSION_OPTION'), constant(self::$widgetPrefix.'WIDGET_VERSION'));
                $this->runPostUpgradeCode($currentVersion);
            }
        }

    	/**
    	 * Uninstall the tables.
    	 *
    	 * @return void
    	 */
    	public static function loadUninstallTables()
    	{
    		global $wpdb;
    		$sqldir     = constant(self::$widgetPrefix.'PLUGIN_DIR_PATH') . 'sql/uninstall.sql';
    		$installSql = file_get_contents($sqldir);
    		$queries    = explode('|', $installSql);

    		foreach($queries as $q)
    		{
    		    $q = str_replace('%PREFIX%', $wpdb->prefix, $q);
    			$wpdb->query($q);
    		}
    	}

    }


