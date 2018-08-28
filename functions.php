<?php

/*
Plugin Name: Oxygen 2.1 Alpha 1 - 06a330e
Author: Soflyy
Author URI: https://oxygenbuilder.com
Description: 2.1 ALPHA 1. USE AT YOUR OWN RISK. 06a330e
Version: 2.1 ALPHA 1 06a330e
Text Domain: oxygen
*/

define("CT_VERSION", 	"1.9.0");
define("CT_FW_PATH", 	plugin_dir_path( __FILE__ )  . 	"component-framework" );
define("CT_FW_URI", 	plugin_dir_url( __FILE__ )  . 	"component-framework" );
define("CT_PLUGIN_MAIN_FILE", __FILE__ );	

global $ct_ignore_post_types;
$ct_ignore_post_types = array(
    'attachment',
    'revision',
    'nav_menu_item',
    'custom_css',
    'customize_changeset',
    'oembed_cache',
    'ct_template',
);

global $ct_component_categories;
$ct_component_categories = array(
	'Headers',
    'Heros & Titles',
    'Content',
    'Showcase',
    'Social Proof',
    'People',
    'Pricing',
    'Call To Action',
    'Contact',
    'Sliders, Tabs, & Accordions',
    'Blog',
    'Footers'
);

global $oxygen_vsb_classic_designsets; // to designate old design sets that do not use variable colors
$oxygen_vsb_classic_designsets = array(
    'atomic',
    'saas2',
    'hyperion',
    'dentist',
    'bnb',
    'winery',
    'onepage2',
    'financial',
    'freelance'
);

global $fake_properties;

$fake_properties = array( 
        'overlay-color', 
        'background-position-left', 
        'background-position-top',
        'background-size-width',
        'background-size-height',
        'ct_content',
        'tag',
        'url',
        'src',
        'alt',
        'target',
        'icon-id',
        "section-width",
        "custom-width",
        "header-width",
        "header-custom-width",
        "container-padding-top",
        "container-padding-right",
        "container-padding-bottom",
        "container-padding-left",
        "custom-css",
        "custom-js",
        "code-css",
        "code-js",
        "code-php",
        "gutter",
        'border-all-color',
        'border-all-style',
        'border-all-width',
        'function_name',
        'friendly_name',
        'shortcode_tag',
        'id',
        // flex
        'flex-reverse',
        // ct_video related
        'video-padding-bottom',
        'use-custom',
        'custom-code',
        'embed-src',
        // ct_link_button related
        'button-style',
        'button-size',
        'button-color',
        'button-text-color',
        // background related
        'gradient',
        "background",
        "overlay-color",
        // ct_icon related
        "icon-size",
        "icon-style",
        "icon-background-color",

        // new columns
        'reverse-column-order',
        'set-columns-width-50',
        'stack-columns-vertically',

        // shadows
        "box-shadow-horizontal-offset",
        "box-shadow-vertical-offset",
        "box-shadow-blur",
        "box-shadow-spread",
        "box-shadow-color",
        "box-shadow-inset",
        "text-shadow-horizontal-offset",
        "text-shadow-vertical-offset",
        "text-shadow-blur",
        "text-shadow-color",

        // filter
        'filter-amount-blur',
        'filter-amount-brightness',
        'filter-amount-contrast',
        'filter-amount-grayscale',
        'filter-amount-hue-rotate',
        'filter-amount-invert',
        'filter-amount-saturate',
        'filter-amount-sepia',
        'filter-amount-blur-unit',
        'filter-amount-brightness-unit',
        'filter-amount-contrast-unit',
        'filter-amount-grayscale-unit',
        'filter-amount-hue-rotate-unit',
        'filter-amount-invert-unit',
        'filter-amount-saturate-unit',
        'filter-amount-sepia-unit',

        // tabs
        'tabs_wrapper',
        'tabs_contents_wrapper',
        'active_tab_class',
    );

global $ct_source_sites;

$source_sites = get_option('oxygen_vsb_source_sites');

$ct_source_sites = array();

if($source_sites) {
	
	$lines = explode("\r\n", $source_sites);

	foreach($lines as $line) {

		$line = trim($line);

		if(empty($line)) {
			continue;
		}

		if(!empty($line) && strpos($line, '=>') !== false) {
			$exploded = explode('=>', $line);
			$ct_source_sites[trim($exploded[0])] = trim($exploded[1]);
		}
	}

}

require_once("component-framework/component-init.php");
