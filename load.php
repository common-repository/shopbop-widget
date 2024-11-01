<?php
include_once dirname(__FILE__) . "/vendor/autoload.php";

$widgetPrefix = 'SHOPBOP_';

//Widget relative path.
if (!defined($widgetPrefix . 'PLUGIN_DIR_PATH'))
	define($widgetPrefix . 'PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));

//Widget absolute path.
if (!defined($widgetPrefix . 'PLUGIN_DIR_URL'))
	define($widgetPrefix . 'PLUGIN_DIR_URL', plugin_dir_url(__FILE__));


require_once constant($widgetPrefix . 'PLUGIN_DIR_PATH') . 'lib/constants.php';
require_once constant($widgetPrefix . 'PLUGIN_DIR_PATH') . 'lib/core.php';

$cw = new Shopbop\CoreWidget();

load_plugin_textdomain('corewidget', false, 'corewidget/languages');


if (is_admin()) {
	require_once constant($widgetPrefix . 'PLUGIN_DIR_PATH') . 'lib/widget_admin.php';
}

function shopbop_core_plugin_update_info()
{
	echo '<br />Before upgrading, please read the upgrade notes here <a href="http://wordpress.org/plugins/shopbop-widget/upgrade/" target="_blank">http://wordpress.org/plugins/shopbop-widget/upgrade/</a>';
}

add_action('in_plugin_update_message-' . constant($widgetPrefix . 'PUBLIC_WIDGET_BASE_FILE_AND_SLUG_NAME'), 'shopbop_core_plugin_update_info');


/**
 * Improved error logging.
 *
 * @return void
 */
function _shopbop_widget_log()
{
	$msg = "";
	foreach (func_get_args() as $i) {
		$msg .= var_export($i, true) . "\n";
	}
	error_log($msg);
}

/**
 * Widget activation and deactivation hook registration.
 */
register_activation_hook(dirname(__FILE__) . "/shopbop-widget.php", array('Shopbop\CoreWidget', 'onActivate'));
register_deactivation_hook(dirname(__FILE__) . "/shopbop-widget.php", array('Shopbop\CoreWidget', 'onDeactivate'));
register_uninstall_hook(dirname(__FILE__) . "/shopbop-widget.php", array('Shopbop\CoreWidget', 'onUninstall'));

// Hooks for Cron Updater
register_deactivation_hook(dirname(__FILE__) . "/shopbop-widget.php", array('Shopbop\CoreWidgetUpdate', 'deregisterScheduledEvent'));

/**
 * Initialise meta field for posts.
 *
 * @return CoreCategories
 */
function shopbopTriggerCoreCategories()
{
	$obj = new Shopbop\CoreCategories();
	$obj->init();
	return $obj;
}

if (is_admin()) {
	add_action('load-post.php', 'shopbopTriggerCoreCategories');
}


/**
 * Run the update.
 *
 * @return void
 */
function shopbop_widget_update()
{
	//Check for eula agreement if agreed or not.
	$coreWs = new Shopbop\CoreWebservice;

	if(!$coreWs->eulaCheck())
		return;

	// Run updater
	$updater = new Shopbop\CoreWidgetUpdate();
	$updater->runUpdate();
}

$coreWidgetUpdate = new Shopbop\CoreWidgetUpdate();

add_action(Shopbop\CoreWidgetUpdate::SCHEDULE_HOOK, 'shopbop_widget_update');
add_filter('cron_schedules', array($coreWidgetUpdate, 'addCronSchedule'));

//Register cron schedul event on every page.
//But this is registered only once and will not register if it already exist.
add_action( 'wp', array($coreWidgetUpdate,'registerScheduledEvent' ));