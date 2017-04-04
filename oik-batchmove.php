<?php
/*
Plugin Name: oik batchmove
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-batchmove
Description: Batch change post categories or published date incl. CRON rescheduling
Version: 2.4.3
Author: bobbingwide
Author URI: https://www.oik-plugins.com/author/bobbingwide
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2013-2017 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Implement "oik_loaded" for oik-batchmove
 * 
 * CRON logic moved to admin pages - enabling the user to schedule / deschedule the processing
 */
function oik_batchmove_loaded() {
}

/**
 * Implement "oik_fields_loaded" for oik-batchmove
 */
function oik_batchmove_fields_loaded() {
  bw_register_field( "_do_not_reschedule", "checkbox", "Do not reschedule", array( "#theme" => false, "#form" => false ) );
  bw_register_field_for_object_type( "_do_not_reschedule", "post" ); 
}

/**
 * Adjust a date using PHP's date_add() function 
 *
 * This function can be used to apply date adjustments such as
 *
 * <pre>
 * +1 year
 * +1 year 6 months
 * +2 years
 * </pre>
 *
 * @use date_interval_create_from_date_string() (PHP 5.3 and above)
 * 
 * @link http://uk3.php.net/manual/en/datetime.formats.relative.php 
 *
 * @param string $adjustment - the date adjustment to apply
 * @param string $date - date to adjust
 * @param string $format - the required format for the new date
 * @return string the new date
 */
if( !function_exists( "bw_date_adjust" ) ) {
function bw_date_adjust( $adjustment="1 year", $date=null, $format='Y-m-d' ) {
  $adate = date_create( $date );
  date_add( $adate, date_interval_create_from_date_string( $adjustment ));
  return( date_format( $adate, $format ) );
}
}

/**
 * Implement "oik_batchmove_hook" action to process the cron event for Category republishing, Tag republishing and Scheduled republishing
 *
 * We're dependent upon oik but we don't have to worry about APIs not being available
 * since "oik_loaded" will have been invoked before the WordPress cron code actions the scheduled event.
 */
function oik_batchmove_cron_hook() {
  //bw_trace2();
  //bw_backtrace();
  oik_require( "admin/oik-batchmove-cron.php", "oik-batchmove" ); 
  oik_batchmove_lazy_category_republish();
  oik_batchmove_lazy_tag_republish();
  oik_batchmove_lazy_cron();
} 

/**
 * Implement "oik_admin_menu" action for oik-batchmove
 */
function oik_batchmove_admin_menu() {
  // oik_register_plugin_server( __FILE__ );
  oik_require( "admin/oik-batchmove.php", "oik-batchmove" );
  oik_batchmove_lazy_admin_menu();
}

/**
 * Implement "admin_notices" action for oik-batchmove"
 * 
 * v2.4.2 now depends on oik v3.0.0 or higher 
 * v2.4.3 now depends on oik v3.1 or higher
 */ 
function oik_batchmove_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-batchmove/oik-batchmove.php", "oik_batchmove_activation" );   
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }
  }  
  $depends = "oik:3.1";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Initialisation when the plugin file is loaded
 */
function oik_batchmove_plugin_loaded() {
  add_action( "oik_admin_menu", "oik_batchmove_admin_menu" );
  add_action( "admin_notices", "oik_batchmove_activation" );
  add_action( "oik_loaded", "oik_batchmove_loaded" );
  add_action( "oik_fields_loaded", "oik_batchmove_fields_loaded" );
  add_action( "oik_batchmove_hook", "oik_batchmove_cron_hook" );
}

oik_batchmove_plugin_loaded();




