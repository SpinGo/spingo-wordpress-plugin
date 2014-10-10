<?php
defined('ABSPATH') or die();

/* Posts/Pages Shortcode */

add_shortcode( 'spingo_calendar', 'calendar_shortcode' );

function addAttributes($attrs, $map) {
	$params = array();
	foreach($map as $shortcodeParam => $embedParam) {
		if (array_key_exists($shortcodeParam, $attrs)) {
			$params[$embedParam] = $attrs[$shortcodeParam];
			if ($params[$embedParam] === "true") {
				$params[$embedParam] = true;
			} else if ($params[$embedParam] === "false") {
				$params[$embedParam] = false;
			} else if (is_numeric($params[$embedParam])) {
				$params[$embedParam] = intval($params[$embedParam]);
			}
		}
	}
	return $params;
}

function calendar_shortcode($attrs) {
	$options = get_option('spingo__settings');

	$calendarParams = array_merge(
		array(
			'subdomain' => $options['spingo__subdomain']
		),
		addAttributes($attrs, array(
			'calendar_id' => 'id',
			'calendar_parent_url' => 'parentUrl',
			'calendar_twitter_handle' => 'twitterHandle',
			'calendar_user_location' => 'userLocation',
			'calendar_postal_code' => 'postalCode',
			'calendar_radius_miles' => 'radiusMiles'
		))
	);

	$modeParams = addAttributes($attrs, array(
		'calendar_mode_type' => 'type',
		'calendar_mode_id' => 'id'
	));
	if (count($modeParams) > 0) {
		$calendarParams['mode'] = $modeParams;
	}

	$params = array_merge(
		array(
			'authToken' => $options['spingo__authToken'],
			'calendar' => $calendarParams
		), 
		addAttributes($attrs, array(
			'pushstate' => 'pushState',
			'default_view' => 'defaultView',
			'section_ids' => 'sectionIDs',
			'default_color' => 'defaultColor',
			'accent_color' => 'accentColor',
			'fixed_top_offset' => 'fixedTopOffset',
			'dark_background' => 'darkBackground'
		))
	);
	if (array_key_exists('section_ids', $attrs)) {
		$ids = explode(',', $attrs['section_ids']);
		$paramIds = array();
		foreach($ids as $id) {
			$id = intval($id);
			if (!empty($id)) {
				$paramIds[] = $id;
			}
		}
		if (count($ids) > 0) {
			$params['sectionIDs'] = $paramIds;
		}
	}

	echo '<div id="spingo-container"></div>';
	echo "<script>
	var SpinGo = { config: ".json_encode($params)." };</script>";
	echo '<script src="http://spingo.spingo.com/embed.js"></script>';
}