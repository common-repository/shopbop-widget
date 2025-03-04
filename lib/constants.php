<?php
namespace Shopbop;

$widgetPrefix = 'SHOPBOP_';
require_once dirname(__FILE__) . '/version.php';

/*
 * --------------------------------------------------------
 * This section of constants are use for the widget public.
 * --------------------------------------------------------
 */

//CSS top level name
if(!defined($widgetPrefix . 'WIDGET_CSS'))
	define($widgetPrefix . 'WIDGET_CSS', 'shopbop-');

//Public widget display name.
if(!defined($widgetPrefix . 'PUBLIC_WIDGET_NAME'))
	define($widgetPrefix . 'PUBLIC_WIDGET_NAME', 'Shopbop');

//plugin path slug with the base file name.
if(!defined($widgetPrefix . 'PUBLIC_WIDGET_BASE_FILE_AND_SLUG_NAME'))
	define($widgetPrefix . 'PUBLIC_WIDGET_BASE_FILE_AND_SLUG_NAME', 'shopbop-widget/shopbop-widget.php');

//Public widget display name slug.
if(!defined($widgetPrefix . 'PUBLIC_WIDGET_NAME_SLUG'))
	define($widgetPrefix . 'PUBLIC_WIDGET_NAME_SLUG', 'Shopbop-widget');

//Admin widget display name slug.
if(!defined($widgetPrefix . 'ADMIN_WIDGET_NAME_SLUG'))
	define($widgetPrefix . 'ADMIN_WIDGET_NAME_SLUG', 'Shopbop-widget');

//Public widget display DESCRIPTION.
if(!defined($widgetPrefix . 'PUBLIC_WIDGET_DESCRIPTION'))
	define($widgetPrefix . 'PUBLIC_WIDGET_DESCRIPTION', 'Shopbop widget');

//Public widget ID for WP_WIDGET.
if(!defined($widgetPrefix . 'PUBLIC_WIDGET_ID_BASE'))
	define($widgetPrefix . 'PUBLIC_WIDGET_ID_BASE', 'core-public-widget');


/*
 * --------------------------------------------------------
 * This section of constants are use for the widget admin.
 * --------------------------------------------------------
 */

//Widget admin defalut width size
if(!defined($widgetPrefix . 'WIDGET_DEFAULT_MIN_WIDTH'))
    define($widgetPrefix . 'WIDGET_DEFAULT_MIN_WIDTH', 200);

if(!defined($widgetPrefix . 'WIDGET_DEFAULT_WIDTH'))
    define($widgetPrefix . 'WIDGET_DEFAULT_WIDTH', 300);

if(!defined($widgetPrefix . 'WIDGET_DEFAULT_MAX_WIDTH'))
	define($widgetPrefix . 'WIDGET_DEFAULT_MAX_WIDTH', 450);

if(!defined($widgetPrefix . 'WIDGET_DEFAULT_MIN_MAX_WIDTH'))
	define($widgetPrefix . 'WIDGET_DEFAULT_MIN_MAX_WIDTH', 200);

if(!defined($widgetPrefix . 'WIDGET_DEFAULT_MAX_MAX_WIDTH'))
	define($widgetPrefix . 'WIDGET_DEFAULT_MAX_MAX_WIDTH', 1600);

if(!defined($widgetPrefix . 'WIDGET_DEFAULT_WIDTH_TYPE'))
	define($widgetPrefix . 'WIDGET_DEFAULT_WIDTH_TYPE', 'fluid');

if(!defined($widgetPrefix . 'WIDGET_DEFAULT_THEME'))
    define($widgetPrefix . 'WIDGET_DEFAULT_THEME', 'light');

//Widget admin defalut language
if(!defined($widgetPrefix . 'WIDGET_DEFAULT_LANGUAGE'))
	define($widgetPrefix . 'WIDGET_DEFAULT_LANGUAGE', 'en-us');

//Widget admin options page title
if(!defined($widgetPrefix . 'WIDGET_OPTIONS_PAGE_TITLE'))
	define($widgetPrefix . 'WIDGET_OPTIONS_PAGE_TITLE', 'Shopbop Settings');

//Widget admin options menu title
if(!defined($widgetPrefix . 'WIDGET_OPTIONS_MENU_TITLE'))
	define($widgetPrefix . 'WIDGET_OPTIONS_MENU_TITLE', 'Shopbop');

//Widget brand and product name maximum length in characters
if(!defined($widgetPrefix . 'WIDGET_ANCHOR_TEXT_MAX_LENGTH'))
	define($widgetPrefix . 'WIDGET_ANCHOR_TEXT_MAX_LENGTH', 50);

//Widget pane to open
if(!defined($widgetPrefix . 'WIDGET_DEFAULT_PANE_TO_OPEN'))
    define($widgetPrefix . 'WIDGET_DEFAULT_PANE_TO_OPEN', 'random');

/*
 * --------------------------------------------------------
 * This section of constants are use for the widget Webservice.
 * --------------------------------------------------------
 */

//Widget options to store webservice data.
if(!defined($widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME'))
	define($widgetPrefix . 'WIDGET_WS_WP_OPTIONS_NAME', 'ShopbopWidgetWsOptions');

//Widget options to store current version.
if(!defined($widgetPrefix . 'WIDGET_VERSION_OPTION'))
	define($widgetPrefix . 'WIDGET_VERSION_OPTION', 'ShopbopWidgetVersion');

//Widget options to store webservice data.
if(!defined($widgetPrefix . 'ACTIVATE_PLUGIN_REDIRECT'))
	define($widgetPrefix . 'ACTIVATE_PLUGIN_REDIRECT', 'ShopbopActivatePluginRedirect');

//Widget options to store plugin data.
if(!defined($widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME'))
	define($widgetPrefix . 'WIDGET_PLUGIN_WP_OPTIONS_NAME', 'ShopbopWidgetPluginOptions');


//Widget options to store Eula agree.
if(!defined($widgetPrefix . 'WIDGET_PLUGIN_WP_EULA_AGREEMENT'))
    define($widgetPrefix . 'WIDGET_PLUGIN_WP_EULA_AGREEMENT', 'ShopbopWidgetEulaAgreement');

//Widget options enable or disable google analytics.
if(!defined($widgetPrefix . 'WIDGET_PLUGIN_WP_ENABLE_GOOGLE_ANALYTICS'))
    define($widgetPrefix . 'WIDGET_PLUGIN_WP_ENABLE_GOOGLE_ANALYTICS', 'ShopbopWidgetEnableGoogleAnalytics');

//Widget options cache google analytics last fetch time.
if(!defined($widgetPrefix . 'WIDGET_PLUGIN_WP_ENABLE_GOOGLE_ANALYTICS_CACHE_TIME'))
    define($widgetPrefix . 'WIDGET_PLUGIN_WP_ENABLE_GOOGLE_ANALYTICS_CACHE_TIME', 86400);//24 hrs

//Widget options to store plugin data.
if(!defined($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES'))
	define($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES', 'ShopbopWidgetCategories');

// WP Option: Stores the datetime when the categories was last pulled (caching)
if(!defined($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_TIMESTAMP'))
	define($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_TIMESTAMP', 'ShopbopWidgetCategoryTimestamp');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE'))
	define($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_LAST_UPDATE', 'ShopbopWidgetCategoryLastUpdate');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_CACHE_TIMEOUT'))
	define($widgetPrefix . 'WIDGET_WS_WP_CATEGORIES_CACHE_TIMEOUT', 'ShopbopWidgetCategoryCacheTimeout');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_FAIL'))
	define($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_FAIL', 'ShopbopWidgetInternalUpdateLastFail');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_SUCCESS'))
	define($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_LAST_SUCCESS', 'ShopbopWidgetInternalUpdateLastSuccess');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_REQUESTED_DATE'))
	define($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_REQUESTED_DATE', 'ShopbopWidgetInternalUpdateRequestedDate');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_AUTO_EXPIRE_TIME'))
	define($widgetPrefix . 'WIDGET_WS_WP_INTERNAL_UPDATE_AUTO_EXPIRE_TIME', 86400); // 24 hours

if(!defined($widgetPrefix . 'WIDGET_WS_WP_THROTTLE_TIME_START'))
	define($widgetPrefix . 'WIDGET_WS_WP_THROTTLE_TIME_START', 'ShopbopWidgetThrottleTimeStart');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_THROTTLE_TIME'))
	define($widgetPrefix . 'WIDGET_WS_WP_THROTTLE_TIME', 60); // 60 seconds

if(!defined($widgetPrefix . 'WIDGET_WS_WP_NOT_RESPONDING_TIME'))
	define($widgetPrefix . 'WIDGET_WS_WP_NOT_RESPONDING_TIME', 900); // 15 minutes


if(!defined($widgetPrefix . 'WIDGET_WS_WP_NOT_RESPONDING_TIME_START'))
    define($widgetPrefix . 'WIDGET_WS_WP_NOT_RESPONDING_TIME_START', 'ShopbopWidgetNotRespondingTimeStart');

if(!defined($widgetPrefix . 'WIDGET_WS_WP_CACHE_TIMEOUT'))
	define($widgetPrefix . 'WIDGET_WS_WP_CACHE_TIMEOUT', 86400); // 24 hours

if(!defined($widgetPrefix . 'WIDGET_UPDATE_MAX_CRON_RUN_TIME'))
	define($widgetPrefix . 'WIDGET_UPDATE_MAX_CRON_RUN_TIME', 300); // 5 minutes

if(!defined($widgetPrefix . 'WIDGET_UPDATE_TIME_BETWEEN_CRON'))
	define($widgetPrefix . 'WIDGET_UPDATE_TIME_BETWEEN_CRON', 600); // 10 minutes

//Widget options to store plugin data.
if(!defined($widgetPrefix . 'WIDGET_OPTIONS_MENU_SLUG_TITLE'))
	define($widgetPrefix . 'WIDGET_OPTIONS_MENU_SLUG_TITLE', 'Shopbop-core-widget-options');
	

/*
* ----------------------------------------------------------
* This section of constants are use for the calling API Webservice.
* -----------------------------------------------------------
*/

if(array_key_exists('WIDGET_WS_URL', $_SERVER))
	define($widgetPrefix . 'WIDGET_WS_URL', $_SERVER['WIDGET_WS_URL']);

if(array_key_exists('WIDGET_WS_URL', $_ENV))
	define($widgetPrefix . 'WIDGET_WS_URL', $_ENV['WIDGET_WS_URL']);

if(!defined($widgetPrefix . 'WIDGET_WS_URL'))
	define($widgetPrefix . 'WIDGET_WS_URL', 'https://widget-api.stickyeyes.com');


/*
* ----------------------------------------------------------
* Widget Translation.
* -----------------------------------------------------------
*/
if(!defined($widgetPrefix . 'WIDGET_TRANSLATION'))
	define($widgetPrefix . 'WIDGET_TRANSLATION', 'corewidget');