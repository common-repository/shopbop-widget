<?php include_once dirname(__FILE__) . "/../vendor/autoload.php"; ?>

<?php $widgetPrefix = 'SHOPBOP_'; ?>

<!-- Begining of EULA-->
<?php
 //Check for eula agreement if agreed or not.
$coreWs = new Shopbop\CoreWebservice();
if(!$coreWs->eulaCheck()):?>
<div class="wrap metabox-holder">
    <div id="icon-users" class="icon32"></div>
    <h1>Shopbop Widget End User License Agreement</h1><br>

    <form method="post" action="options.php" enctype="multipart/form-data">
        <?php settings_fields(constant($widgetPrefix.'WIDGET_PLUGIN_WP_EULA_AGREEMENT')); ?>
        <?php do_settings_sections('core_widget_eula_section'); ?>
        <br>
        <input type="submit" class="button-primary" name="core-widget-eula" value="Agree"/>
        <input type="submit" class="button-primary" name="core-widget-eula" value="Cancel">
    </form>
</div>
<!--    END of EULA-->
<?php else: ?>
<div class="wrap metabox-holder core-widget-admin-table">
<div id="icon-options-general" class="icon32"></div>
<h2><?php echo constant($widgetPrefix .'WIDGET_OPTIONS_PAGE_TITLE') ?></h2><br />
            <?php settings_errors(); ?>
		            <form method="post" action="options.php" enctype="multipart/form-data">
			             <?php settings_fields(constant($widgetPrefix.'WIDGET_PLUGIN_WP_OPTIONS_NAME')); ?>


							    <div id="shopbop-widget-registerbox" class="postbox" >
									<h3>Registration</h3>
								    <?php do_settings_sections('core_widget_register_section'); ?>
								</div>
								<?php
								$widgetWsOptions = get_option(constant($this->widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME'));
								if(isset($widgetWsOptions['registered']) && $widgetWsOptions['registered'] == true):
	        					?>
	        					<div id="shopbop-widget-apperancebox" class="postbox" >
									<h3>Widget Appearance</h3>
								    <?php do_settings_sections('core_widget_apperance_section'); ?>
								</div>

                                <div id="shopbop-widget-forcebox" class="postbox" >
                                    <h3>Force Location</h3>
                                    <?php do_settings_sections('core_widget_force_location'); ?>
								</div>

	        					<div id="shopbop-widget-categoriesbox" class="postbox" >
									<h3>Default Categories</h3>
                                    <table class="form-table">
                                    <tbody>
                                    <tr valign="top">
                                        <td colspan="2">
                                            <span class="description">Please note that it may take several minutes for changes to be reflected across all pages.</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                    </table>
                                    <?php do_settings_sections('core_widget_category_section'); ?>
								</div>


	        					<?php endif; ?>

				    <p class="submit">
						<input name="submit" type="submit" class="button-primary" value="Save Changes" />
					</p>

                    <p class="install-help shopbop-widget-note"><strong>Help?</strong> Visit the <a href="https://www.shopbop.com/go/widgets" target="_blank" title="Offical Shopbop Plugin Page">Official Shopbop Widget Page</a> to find detailed guides and support for using this plugin. </p>
			</form>

            <?php if(array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == "show_advanced"): ?>
            <?php $widgetWsOptions = get_option(constant($this->widgetPrefix.'WIDGET_WS_WP_OPTIONS_NAME')); ?>
                    <div id="shopbop-widget-categoriesbox" class="postbox" >
                        <h3>Advanced</h3>
                        <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">Clear all Settings</th>
                            <td>
                                <a href="<?php menu_page_url(constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_SLUG_TITLE')); ?>&action=clear_all_settings" class="button button-secondary">Clear all Settings</a>
                                <br/><span class="description">Clearing all settings will require you to perform the activation process again to continue using this widget.</span>
                            </td>
                        </tr>
                        <?php if(array_key_exists('widgetId', $widgetWsOptions)): ?>
                        <tr>
                            <th scope="row">Change API Key</th>
                            <td><form method="post" action="<?php menu_page_url(constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_SLUG_TITLE')); ?>&action=set_api_key"><input name="widgetId" type="text" size="50" value="<?php echo $widgetWsOptions['widgetId']; ?>"><br/><input type="submit" value="Change" class="button button-secondary" /></form></td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                        </table>
                    </div>
                    <a href="<?php menu_page_url(constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_SLUG_TITLE')); ?>">Hide Advanced Options</a>
            <?php else: ?>
                <a href="<?php menu_page_url(constant($this->widgetPrefix.'WIDGET_OPTIONS_MENU_SLUG_TITLE')); ?>&action=show_advanced">Show Advanced Options</a>
            <?php endif; ?>

            <p><small>v<?php echo constant($widgetPrefix.'WIDGET_VERSION');?></small></p>
		</div>

<?php endif; ?>