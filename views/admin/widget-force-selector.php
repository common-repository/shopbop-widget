
<input id="sp_widget_location_selector" type="text" name="<?php echo $optionsName; ?>[widget_force_selector]" value="<?=$widgetForceSelector?>" class="regular-text">
<select name="<?php echo $optionsName; ?>[widget_force_selector_pos]">
	<option <?php if($widgetForceSelectorPos == 'before') echo 'selected="selected"' ?> value="before">Before</option>
	<option <?php if($widgetForceSelectorPos == 'after') echo 'selected="selected"' ?> value="after">After</option>
</select>
<br>
<span class="description"><a href="https://api.jquery.com/category/selectors/" target="_blank">
	CSS/JS selector</a> of which element you would like the widget adding to
</span>
<script>
	// Shows and hides this row
	(function($) {
		var $locationSelect = $('#sp_widget_location');
		var $selectorRow = $('#sp_widget_location_selector').closest('tr');
		$locationSelect.change(function() {
			$selectorRow[$locationSelect.val() == 'custom' ? 'show' : 'hide']();
		}).change();
	})(jQuery);
</script>