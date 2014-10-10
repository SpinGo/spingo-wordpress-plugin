<?php
defined('ABSPATH') or die();

/* Sidebar Widget */

class spingo_widget_plugin extends WP_Widget {
	function spingo_widget_plugin() {
		$options = array('classname' => 'spingo-widget-settings');
		parent::WP_Widget(false, __('SpinGo List Widget', 'wp_widget_plugin'), $options);

		$this->properties = array(
			'title' => array(
				'label' => "Title",
				'type' => 'text',
				'split' => true
			),
			'subTitle' => array(
				'label' => 'Subtitle',
				'type' => 'text',
				'split' => true
			),
			'titleLink' => array(
				'label' => "Title Link",
				'type' => 'text'
			),
			'footerTitle' => array(
				'label' => "Footer Title",
				'type' => 'text',
				'split' => true
			),
			'footerLink' => array(
				'label' => "Footer Link",
				'type' => 'text'
			),
			'calendarUrl' => array(
				'label' => "Calendar URL",
				'type' => 'text'
			),
			'genericTicketLink' => array(
				'label' => "Generic Ticket Link",
				'type' => 'text'
			),
			'sparseMode' => array(
				'label' => "Sparse Mode",
				'type' => 'boolean',
				'split' => true,
				'default' => false
			),
			'sparseModeThreshold' => array(
				'label' => "Sparse Mode Threshold",
				'type' => 'int',
				'split' => true
			),
			'headerFontFamily' => array(
				'label' => 'Header Font Family',
				'type' => 'text'
			),
			'bodyFontFamily' => array(
				'label' => 'Body Font Family',
				'type' => 'text'
			),
			'perPage' => array(
				'label' => 'Events Per Page',
				'type' => 'int',
				'split' => true
			),
			'baseFontSize' => array(
				'label' => 'Base Font Size',
				'type' => 'int',
				'split' => true
			),
			'textColor' => array(
				'label' => 'Text Color',
				'type' => 'text'
			),
			'featuredColor' => array(
				'label' => 'Featured Color',
				'type' => 'text'
			),
			'mainColor' => array(
				'label' => 'Main Color',
				'type' => 'text'
			),
			'cssClassPrefix' => array(
				'label' => 'CSS Class Prefix',
				'type' => 'text',
				'split' => true
			),
			'dateLabelWidth' => array(
				'label' => 'Date Label Width',
				'type' => 'int',
				'split' => true
			),
			'startDate' => array(
				'label' => 'Start Date',
				'type' => 'date',
				'split' => true
			),
			'postalCode' => array(
				'label' => 'Postal Code',
				'type' => 'int',
				'split' => true
			),
			'radiusMiles' => array(
				'label' => 'Radius (Miles)',
				'type' => 'int',
				'split' => true
			),
			'sections' => array(
				'label' => 'Section IDs',
				'type' => 'int[]',
				'split' => true
			)
		);
	}

	function form($instance) {
		foreach($this->properties as $key => $info) {
			$fieldClasses = array();
			if (array_key_exists('split', $info) && $info['split']) {
				$fieldClasses[] = 'spingo-split';	
			}
			$id = $this->get_field_id($key);
			$name = $this->get_field_name($key);
			$value = ($instance ? esc_attr($instance[$key]) : '');
			if ($value === "" && array_key_exists('default', $info)) {
				$value = $info['default'];
			}
			?><p class="<?php echo implode(' ', $fieldClasses)?>">
				<label for="<?php echo $id; ?>"><?php _e($info['label'], 'wp_widget_plugin'); ?></label><?php
				if ($info['type'] == 'boolean') { ?>
					<span class="options">
						<input id="<?php echo $id; ?>_yes" name="<?php echo $name; ?>" type="radio" value="1" <?php if (!empty($value)) echo 'checked="checked"'; ?> /> <label for="<?php echo $id; ?>_yes">Yes</label>
						<input id="<?php echo $id; ?>_no" name="<?php echo $name; ?>" type="radio" value="0" <?php if (empty($value)) echo 'checked="checked"'; ?> />  <label for="<?php echo $id; ?>_no">No</label>
					</span><?php
				} else if ($info['type'] == 'select') {
					?><select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="widefat" style="width:100%;">
						<? foreach($info['options'] as $optionValue => $label) { ?>
							<option <?php if ($value == $optionValue) echo 'selected="selected"'; ?> value="<?php echo $optionValue; ?>"><?php echo $label; ?></option>
						<? } ?>
					</select><?php
				} else { 
					?><input class="widefat" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="text" value="<?php echo $value; ?>" /><?php 
				} ?>
			</p><?php 
		}
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		foreach($this->properties as $key => $info) {
			if ($new_instance[$key] !== "") {
				$instance[$key] = strip_tags($new_instance[$key]);
				if ($info['type'] == 'int') {
					$instance[$key] = intval($instance[$key]);
				} else if ($info['type'] == 'boolean') {
					$instance[$key] = !empty($instance[$key]);
				}
			} else {
				$instance[$key] = "";
			}
		}
		return $instance;
	}

	function widget($args, $instance) {
		extract( $args );
		$options = get_option('spingo__settings');
		$params = array(
			'authToken: "'.$options['spingo__authToken'].'"',
			'container: "#spingo-list-widget"'
		);
		foreach($this->properties as $key => $info) {
			if ($instance[$key] !== "") {
				if ($info['type'] == 'int') {
					$params[] = $key.': '.intval($instance[$key]);
				} else if ($info['type'] == 'int[]') {
					$parts = explode(',', $instance[$key]);
					if (count($parts) > 0) {
						$ids = array();
						foreach($parts as $part) {
							if (is_numeric($part)) {
								$ids[] = intval($part);
							}
						}
						if (count($ids) > 0) {
							$params[] = $key.': ['.implode(', ', $ids).']';
						}
					}
				} else if ($info['type'] == 'boolean') {
					$params[] = $key.': '.(!empty($instance[$key]) ? 'true' : 'false');
				} else if ($info['type'] == 'date') {
					$date = strtotime($instance[$key]);
					if ($date !== false) {
						$params[] = $key.': new Date("'.date("Y-m-d", $date).'")';
					}
				} else {
					$params[] = $key.': "'.addslashes($instance[$key]).'"';
				}
			}
		}
		echo '<script src="http://'.$options['spingo__subdomain'].'.spingo.com/list-widget.js"></script>';
		echo "<script>new SpinGoWidget({".implode(",\n", $params)."});</script>";
		echo '<div id="spingo-list-widget"></div>';
	}
}

add_action('widgets_init', create_function('', 'return register_widget("spingo_widget_plugin");'));