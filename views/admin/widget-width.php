<p>
	Fluid <input type="radio" name="width-type" value="fluid" <?php if($fluid=='fluid') echo 'checked="checked"'; ?> />
	Max <input id="widget-max-width" name="<?php echo $optionsName; ?>[widget_max_width]" type="number"
		value="<?php echo is_null($widgetMaxWidth ) ? constant($this->widgetPrefix.'WIDGET_DEFAULT_MAX_MAX_WIDTH') : $widgetMaxWidth; ?>"
		min="<?=constant($this->widgetPrefix.'WIDGET_DEFAULT_MIN_MAX_WIDTH')?>"
		max="<?=constant($this->widgetPrefix.'WIDGET_DEFAULT_MAX_MAX_WIDTH')?>"
		size="5" style="width:100px;" value="<?php echo is_null($widgetMaxWidth ) ? '' : $widgetMaxWidth; ?>">pixels <span class="description">(min: <?php echo constant($this->widgetPrefix.'WIDGET_DEFAULT_MIN_MAX_WIDTH'); ?>, max: <?php echo constant($this->widgetPrefix.'WIDGET_DEFAULT_MAX_MAX_WIDTH'); ?>)</span>
</p>
<p>
	Fixed <input type="radio" name="width-type" value="fixed" <?php if($fluid=='fixed') echo 'checked="checked"'; ?> />
	<span id="lf_width_input_hide">
	<input id="widget-width-px" name="<?php echo $optionsName; ?>[widget_width]" type="text" readonly="readonly"
		size="5" style="width:100px;" value="<?php echo is_null($widgetWidth ) ? constant($this->widgetPrefix.'WIDGET_DEFAULT_WIDTH') : $widgetWidth; ?>">pixels <span class="description">(min: <?php echo constant($this->widgetPrefix.'WIDGET_DEFAULT_MIN_WIDTH'); ?>, max: <?php echo constant($this->widgetPrefix.'WIDGET_DEFAULT_MAX_WIDTH'); ?>)</span>

	<div id="slider" style="width:300px"></div>
	<span class="description">Choose either a fixed width or "fluid" to automatically select the most appropriate size.</span>
	</span>
</p>