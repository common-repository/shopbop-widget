<select id="sp_widget_location" name="<?php echo $optionsName; ?>[widget_force_location]">
    <?php foreach($locations as $key => $value): ?>
    	<option <?php if($widgetLocation == strtolower($value)) echo 'selected="selected"' ?> value="<?php echo strtolower($value); ?>"><?php echo $key; ?></option>
    <?php endforeach; ?>
</select>