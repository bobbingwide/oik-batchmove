<?php // (C) Copyright Bobbing Wide 2013, 2014

/** 
 * Display the "Scheduled republish" page 
 *
 * This page:
 * - displays the settings for "scheduled republish"
 * - displays the settings for category reschedule
 * - displays the settings for tag reschedule
 * - runs the cron process if manually requested
 * - displays any posts that should have been rescheduled today
 * - displays any posts that will get rescheduled tomorrow
 * - displays posts that are published/scheduled for the future date 
 * - displays CRON information and test buttons 
 */
function oik_batchmove_lazy_scheduled_page() {
  oik_menu_header( "Scheduled republish", "w90pc" );
  oik_box( null, null, "Settings", "oik_batchmove_settings" );
  oik_box( null, null, "Reschedule oldest in category", "oik_batchmove_reschedule_oldest" );
  oik_box( null, null, "Reschedule oldest with tag", "oik_batchmove_reschedule_oldest_tags" );
  
  oik_batchmove_run_cron_maybe();
  oik_box( null, null, "Reposts for today", "oik_batchmove_repost_today" );
  oik_box( null, null, "Reposts for tomorrow", "oik_batchmove_reposts" );
  oik_box( null, null, "Rescheduled posts", "oik_batchmove_rescheduled" );
  oik_box( null, null, "CRON", "oik_batchmove_cron" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Display the scheduled republish settings
 * 
 * $activated = true/false
 * $look_back = "-450 days"
 * $reschedule = "+451 days" 
 * 
 * Three fields apply when the post has comments
 *
 * @TODO $ignore_tags = select list of tags to ignore  e.g. "coming soon"
 * @TODO $ignore_categories = select list of categories to ignore e.g. "Archives"
 * 
 */
function oik_batchmove_settings() {
  $option = 'bw_scheduled'; 
  $options = bw_form_start( $option, 'bw_scheduled' );
  $options['activated'] = oik_batchmove_scheduled_option( 'activated' );
  $options['look_back'] = oik_batchmove_scheduled_option( 'look_back' );
  $options['reschedule'] = oik_batchmove_scheduled_option( 'reschedule' );
  $options['reschedule_time'] = oik_batchmove_scheduled_option( 'reschedule_time' );
  $options['title_prefix'] = oik_batchmove_scheduled_option( 'title_prefix' );
  $options['prepend_content_pre_date'] = oik_batchmove_scheduled_option( 'prepend_content_pre_date' );
  $options['prepend_content_post_date'] = oik_batchmove_scheduled_option( 'prepend_content_post_date' );
  bw_checkbox_arr( $option, "Activated?", $options, 'activated' );
  bw_textfield_arr( $option, "Look back", $options, 'look_back', 40 );
  bw_textfield_arr( $option, "Reschedule", $options, 'reschedule', 40 );
  bw_textfield_arr( $option, "Reschedule time, <i>format hh:mm:ss e.g. 09:00:00</i>", $options, 'reschedule_time', 8, array( '#hint' => 'format hh:mm:ss e.g. 09:00:00' ) );
  bw_textfield_arr( $option, "Title prefix, <i>if post has comments</i>", $options, 'title_prefix', 40, array( "#hint" => "if post has comments" ) );
  bw_textfield_arr( $option, "Prepend content before date, if comments", $options, "prepend_content_pre_date", 40 ); 
  bw_textfield_arr( $option, "Prepend content after date, if comments", $options, "prepend_content_post_date", 40 ); 
  etag( "table" );   
  p( isubmit( "ok", "Update", null, "button-primary" ) );
  etag( "form" );
  bw_flush();
}

/**
 * To choose categories to be rescheduled by cycling the oldest to the current date.
 * Optionally, specifying a new publication time. e.g 02:00, set by Category
 */ 
function oik_batchmove_reschedule_oldest() {
  oik_batchmove_run_category_republish_maybe();
  oik_require( "admin/oik-batchmove-categories.php", "oik-batchmove" );
  oikbmc_lazy_do_page();
}


/**
 * To choose categories to be rescheduled by cycling the oldest to the current date.
 * Optionally, specifying a new publication time. e.g 02:00, set by Category
 */ 
function oik_batchmove_reschedule_oldest_tags() {
  oik_batchmove_run_tag_republish_maybe();
  oik_require( "admin/oik-batchmove-tags.php", "oik-batchmove" );
  oikbmt_lazy_do_page();
}
 
/**
 * Invoke the cron event if requested 
 * 
 * Note: Only admin users will have been able to get this far
 *
 */ 
function oik_batchmove_run_cron_maybe() {
  $action = bw_array_get( $_REQUEST, "_oik_batchmove_run_cron", null );
  if ( $action ) {
    oik_batchmove_lazy_cron( true );
  }
}

/**
 * Invoke category republish if requested 
 */
function oik_batchmove_run_category_republish_maybe() {
  $action = bw_array_get( $_REQUEST, "_oik_batchmove_category_republish", null );
  if ( $action ) {
    oik_batchmove_lazy_category_republish( true );
  }
}

/**
 * Invoke tag republish if requested 
 */
function oik_batchmove_run_tag_republish_maybe() {
  $action = bw_array_get( $_REQUEST, "_oik_batchmove_tag_republish", null );
  if ( $action ) {
    oik_batchmove_lazy_tag_republish( true );
  }
}
 

/**
 * Return the bw_scheduled default option value
 *
 * @param string $option - the name of the settings field
 * @return string - the default value or null
 */
function oik_batchmove_scheduled_option_default( $option ) {
  $scheduled_option_defaults = array( "look_back" => "-450 days"
                                    , "reschedule" => "451 days"
                                    , "title_prefix" => __( "From the archives: ", "oik-batchmove" )
                                    , "prepend_content_pre_date" => __( "Previously published on ", "oik-batchmove" )
                                    , "prepend_content_post_date" => __( "<br />", "oik-batchmove" )
                                    , "activated" => false 
                                    , "reschedule_time" => null
                                    );
  $value = bw_array_get( $scheduled_option_defaults, $option, null );
  return( $value );
}

/**
 * Return the value for the bw_scheduled option field 
 * 
 * Note: A stored value of null will cause the default value to be returned
 *
 * @param string $option - the name of the option field
 * @return string - the value of the field. 
 */
function oik_batchmove_scheduled_option( $option, $set="bw_scheduled" ) {
  $value = bw_get_option( $option, $set  );
  if ( $value ) {
    // OK, return this. Don't worry about sanitization
  } else {
    $value = oik_batchmove_scheduled_option_default( $option );
  }
  return( $value );
}

/**
 * List the set of posts
 * 
 * Uses the  same logic as in 'Batch move' although the checkbox field has no effect on this page.
 */
function oik_batchmove_list_posts( $posts ) {
  if ( $posts ) {
    p( "Total posts selected: " . count( $posts ) );
    oik_batchmove_display_selection( $posts );
  }
} 

/**
 * Determine the post_date for the query
 *
 * @param string $option - option field that contains the date adjustment
 * @param string $option - any further date adjustment
 */
function oik_batchmove_query_post_date( $option="look_back", $test_adjust=null ) {
  $post_date = bw_date_adjust( oik_batchmove_scheduled_option( $option ) );
  if ( $test_adjust ) {
    $post_date = bw_date_adjust( $test_adjust, $post_date );
  }
  e( "Post date: " );
  e( $post_date );
  return( $post_date );
}
  
/**
 * Show the posts that should have been reposted today
 * 
 * Add nothing to the "look_back" in order to list the posts that should have been scheduled for today.
 * We might want to use this when we have turned off scheduled republishing or when we are searching for the right value to set for "look_back"
 *
 */
function oik_batchmove_repost_today() {
  $post_date = oik_batchmove_query_post_date( "look_back" );
  $posts = oik_batchmove_query_reposts( $post_date );
  oik_batchmove_list_posts( $posts );
}
  
/**
 * Show the posts that will get reposted tomorrow
 * 
 * Add 1 day to the "look_back" in order to list the posts that will be rescheduled tomorrow
 */
function oik_batchmove_reposts() {
  $post_date = oik_batchmove_query_post_date( "look_back", "+1 day" );
  $posts = oik_batchmove_query_reposts( $post_date );
  oik_batchmove_list_posts( $posts );
}

/**
 * Show the posts that were rescheduled
 *
 * Add the "reschedule" amount to the look back to find the posts that have been rescheduled.
 * Note: This list includes any new posts that have been scheduled for this date.
 */
function oik_batchmove_rescheduled() {
  $post_date = oik_batchmove_query_post_date( "look_back", oik_batchmove_scheduled_option( "reschedule" ));
  $posts = oik_batchmove_query_reposts( $post_date );
  oik_batchmove_list_posts( $posts );
}

/**
 * Display the CRON information for "oik_batchmove_hook" 
 *
 */
function oik_batchmove_cron() {
  p( "If activated scheduled republish is expected to be performed daily just after midnight (UTC)" );
  //do_action( "oik_pre_theme_field" );
  //stag( "table", "widefat" );
  $activated = oik_batchmove_scheduled_option( "activated" );
  //bw_tablerow( array( "Activated?", $activated ) );
  if ( $activated ) {
    p( "Scheduled republish is activated" );
  } else { 
    p( "Scheduled republish is not activated" );
  }
  //  bw_format_custom(  
  //etag( "table" );
  $next_time = oik_batchmove_schedule( $activated );
  if ( $next_time ) {
    $next_time = bw_format_date( $next_time, "Y-m-d H:i:s" );
    p( sprintf( __( 'Next scheduled to run: %1$s' ), $next_time ) );
  } else {
    p( "Not scheduled." );
  }
  $last_run = oik_batchmove_scheduled_option( "last_run", "bw_scheduled_log" ); 
  if ( $last_run ) {
    $last_run = bw_format_date( $last_run, "Y-m-d H:i:s" );
    p( sprintf( __( 'Last run: %1$s' ), $last_run ) );
    // bw_theme_field( "Last run", $last_run );
    $post_date = oik_batchmove_scheduled_option( "post_date", "bw_scheduled_log" ); 
    // bw_theme_field_date( "For post date:", $post_date );
    p( "Post date: $post_date" );
  }
  bw_form();
  p( isubmit( "_oik_batchmove_run_cron", "Run scheduled republish now", null, "button-secondary" ) );
  p( isubmit( "_oik_batchmove_category_republish", "Run category republish now", null, "button-secondary" ) );
  p( isubmit( "_oik_batchmove_tag_republish", "Run tag republish now", null, "button-secondary" ) );
  etag( "form" );
}

/**
 * Schedule "oik_batchmove_hook" to run daily at midnight
 * 
 * Note: The $force logic is for testing only 
 *
 * @param bool $force - whether or not to force it to run in the next hour
 */
function oik_batchmove_schedule_event( $force=false ) {
  if ( $force ) {
    $start_time = strtotime( "now +1 minute" );
    $recurrence = "hourly";
  } else { 
    $start_time = strtotime('today midnight');
    $recurrence = 'daily';
  }
  wp_schedule_event( $start_time, $recurrence, 'oik_batchmove_hook' );
  bw_trace2( "scheduled $recurrence", $start_time );
  return( $start_time );
} 

/**
 * Schedule or deschedule the CRON job
 *
 * Make sure that "oik_batchmove_hook" is scheduled or not.
 *
 * activated  next_time   action        return
 * false      any         unschedule    false
 * false      false       OK            false
 * true       any         nothing       next_time
 * true       false       schedule      next_time from schedule
 * 
 * @link http://stackoverflow.com/questions/13129817/getting-a-timestamp-for-today-at-midnight
 * @link http://codex.wordpress.org/Function_Reference/wp_unschedule_event 
 *
 * @param bool $activated - true if oik_batchmove_hook should be scheduled, false if it's supposed to be stopped
 * @return time - the next scheduled time... if scheduled or false.
 *
 */
function oik_batchmove_schedule( $activated=false ) {
  $next_time = wp_next_scheduled( 'oik_batchmove_hook' );
  //if ( $next_time ) {
  bw_trace2( $next_time, date("Y-m-d H:i:s", $next_time) );
  // p( "nt: $next_time" );
  if ( $activated ) {
    if ( $next_time === false ) {
      // p( "Scheduling..." );
      $next_time = oik_batchmove_schedule_event();
    } else {
      // Good - we're scheduled
      //p( "Good. we're scheduled" );
    }
  } else {
    if ( $next_time === false ) {
      // Good - we-re not scheduled
      // p( "Good, we're not scheduled" );
    } else {
      // p( "Unscheduling..." );
      // wp_unschedule_event( $next_time, 'oik_batchmove_hook' );
      wp_clear_scheduled_hook( 'oik_batchmove_hook' );
      $next_time = false; 
    }
  }
  bw_trace2( $next_time, "next time?" );
  return( $next_time );
}

/**
 * Perform scheduled republishing for selected posts
 * 
 */
function oik_batchmove_lazy_cron( $verbose=false) {
  $post_date = oik_batchmove_query_post_date();
  $posts = oik_batchmove_query_reposts( $post_date, 0 );
  if ( $verbose ) {
    p( "Rescheduling posts for $post_date" );
  } 
  
  if ( $posts ) {
    foreach ( $posts as $post ) {
      if ( $verbose ) {
        p( "Rescheduling: " . $post->ID );
        
      }
      oik_batchmove_republish_post( $post );
    }
  } else {
    $posts = null;
    p( "No posts to reschedule" );
  }
  oik_batchmove_log_reposted( $post_date, $posts );
}

/**
 * Delete unnecessary "_do_not_reschedule" metadata
 *
 * e.g. delete from wp_postmeta where meta_key = '_do_not_reschedule' and meta_value = '0'
 * @link http://codex.wordpress.org/Class_Reference/wpdb#DELETE_Rows
 *
 * @param string $meta_key - the post meta field name
 * @param string $meta_value - the post meta field value
 *
 */
function bw_delete_all_meta_key( $meta_key, $meta_value ) {
  global $wpdb;
  if ( null != $meta_key && null != $meta_value ) {
    $rows_deleted = $wpdb->delete( $wpdb->postmeta, array( "meta_key" => $meta_key, "meta_value" => $meta_value ), array( "%s", "%s" ) );
    bw_trace2( $rows_deleted, "Deleted rows" );
  } else {
    bw_trace2( "Invalid call - meta_key and meta_value MUST be set" );
  }
}

/**
 * Perform category republishing for selected categories
 * 
 * Call oik_batchmove_category_republish for each category and target time
 * The rescheduling is performed in the order specified on the admin page
 * which is not necessarily by Category name OR time.
 * If you use multiple categories on your posts it is potentially possible to reschedule posts in a particular category for multiple oldest dates.
 * Left as an exercise for the reader to create a test case that will demonstrate this. 
 * 
 * Prior to the republishing by category we delete ALL post meta records which have a velue of 0.
 *
 * By deleting all the unnecessary keys we can perform a simpler query to avoid the rows where the meta_value is set.
 * In other words it makes it easier for us to find posts that we can reschedule.
 * Over time there may be a whole bunch of archive posts with "_do_not_reschedule" set. 
 * We could assign them to a different category, but it probably won't affect the database access.
 *
 * @param bool $verbose - set true if you want messages in admin, false for CRON
 */
function oik_batchmove_lazy_category_republish( $verbose=false) {
  $bw_bmcs = get_option( "bw_bmcs" );
  if ( is_array( $bw_bmcs) && count( $bw_bmcs )) {
    oik_require( "includes/bw_posts.inc" );
    bw_delete_all_meta_key( "_do_not_reschedule", "0" );
    foreach ( $bw_bmcs as $bmc => $data ) {
      oik_batchmove_category_republish( $data['args']['category'], $data['args']['time'], $verbose );
    }
  }  
}

/**
 * Perform tag republishing for selected tags
 * 
 * Call oik_batchmove_tag_republish for each tag and target time
 * The rescheduling is performed in the order specified on the admin page
 * which is not necessarily by Tag name OR time.
 * If you use multiple tags on your posts it is potentially possible to reschedule posts in a particular tag for multiple oldest dates.
 * Left as an exercise for the reader to create a test case that will demonstrate this. 
 * 
 * Prior to the republishing by category/tag we delete ALL post meta records which have a velue of 0.
 *
 * By deleting all the unnecessary keys we can perform a simpler query to avoid the rows where the meta_value is set.
 * In other words it makes it easier for us to find posts that we can reschedule.
 * Over time there may be a whole bunch of archive posts with "_do_not_reschedule" set. 
 * We could assign them to a different tag, but it probably won't affect the database access.
 *
 * @param bool $verbose - set true if you want messages in admin, false for CRON
 */
function oik_batchmove_lazy_tag_republish( $verbose=false) {
  $bw_bmts = get_option( "bw_bmts" );
  if ( is_array( $bw_bmts) && count( $bw_bmts )) {
    oik_require( "includes/bw_posts.inc" );
    bw_delete_all_meta_key( "_do_not_reschedule", "0" );
    foreach ( $bw_bmts as $bmc => $data ) {
      oik_batchmove_tag_republish( $data['args']['tag'], $data['args']['time'], $verbose );
    }
  }  
}

/**
 * Find the oldest date for posts in this category and/or with this tag which are not marked as "_do_not_reschedule"
 *
 * Q. Do we have to delete all those that don't have the "_do_not_reschedule" set?
 * 
 *
 * @param string $cat - the category ID
 * @param string $tag - the tag ID
 * @return string - the post date or null
 */ 
function oik_batchmove_query_oldest_date( $cat=null, $tag=null ) {
  $post_date = null;
  $atts = array( "post_type" => "post" 
               , "meta_key" =>  "_do_not_reschedule"
               , "meta_compare" => "NOT EXISTS" 
               , "meta_value" => "1"
               , "order" => "asc"
               , "orderby" => "date"
               , "numberposts" => 1
               );
  if ( $cat ) {
    $atts['cat'] = $cat;
  }
  if ( $tag ) {
    $atts['tag_id'] = $tag;
  }  
  $posts = bw_get_posts( $atts ); 
  if ( $posts ) {
    $post = $posts[0];
    $post_date = $post->post_date;
  }
  return( $post_date );
}

/**
 * Republish posts from a chosen category, setting a new published time if required
 *
 * @param string $cat - The Category ID to republish
 * @param string $time - the target time in format hh:mmm
 * @param bool $verbose - whether or not to display activity 
 *
 */
function oik_batchmove_category_republish( $cat, $time, $verbose=false ) {
  $post_date = oik_batchmove_query_oldest_date( $cat );
  if ( $post_date ) {
    $posts = oik_batchmove_query_reposts( $post_date, $cat );
    if ( $verbose ) {
      p( "Rescheduling posts for $post_date, category: $cat, time: $time" );
    } 
    if ( $posts ) {
      foreach ( $posts as $post ) {
        if ( $verbose ) {
          p( "Rescheduling: " . $post->ID );
        
        }
        oik_batchmove_republish_post( $post, $cat, $time );
      }
    } else {
      $posts = null;
      p( "No posts to reschedule: $cat" );
    }
  
  } else {
    p( "No posts to reschedule for $cat" );
  }
}


/**
 * Republish posts with a chosen tag, setting a new published time if required
 *
 * @param string $tag - The tag ID to republish
 * @param string $time - the target time in format hh:mmm
 * @param bool $verbose - whether or not to display activity 
 *
 */
function oik_batchmove_tag_republish( $tag, $time, $verbose=false ) {
  $post_date = oik_batchmove_query_oldest_date( null, $tag );
  if ( $post_date ) {
    $posts = oik_batchmove_query_reposts( $post_date, null, $tag );
    if ( $verbose ) {
      p( "Rescheduling posts for $post_date, tag: $tag, time: $time" );
    } 
    if ( $posts ) {
      foreach ( $posts as $post ) {
        if ( $verbose ) {
          p( "Rescheduling: " . $post->ID );
        
        }
        oik_batchmove_republish_post( $post, $tag, $time );
      }
    } else {
      $posts = null;
      p( "No posts to reschedule: $tag" );
    }
  
  } else {
    p( "No posts to reschedule for $tag" );
  }
}

if ( !function_exists( "bw_update_option" ) ) {
/** Set the value of an option field in the options group
 *
 * @param string $field the option field to be set
 * @param mixed $value the value of the option
 * @param string $options - the name of the option field
 * @return mixed $value
 *
 * Parms are basically the same as for update_option
 */
function bw_update_option( $field, $value=NULL, $options="bw_options" ) {
  $bw_options = get_option( $options );
  $bw_options[ $field ] = $value;
  bw_trace2( $bw_options );
  update_option( $options, $bw_options );
  return( $value );
}
}  

/** 
 * Update the log of oik_batchmove_lazy_cron
 * 
 * We use a different set of option fields to log this information
 * 
 */
function oik_batchmove_log_reposted( $post_date, $posts ) {
  bw_update_option( "last_run", time(), "bw_scheduled_log" );
  bw_update_option( "post_date", $post_date, "bw_scheduled_log" );
}

/**
 * Query the posts to be published or republished 
 *
 * Note: We don't expect there to be so many posts that we can't apply the updates in one invocation.
 * Note: We look for posts in "future" status, even if the post_date is in the past.
 * When $cat is null we look for all posts, even those marked as "_do_not_reschedule".
 * This will enable the admin user to decide what the value of the flag should be?
 * When $cat or $tag is not null then we only reschedule one post.
 
 * @TODO Enhance UI to allow setting of this field.
 *
 * @param string $post_date - post_date to be used for the query
 * @param string $cat - Category ID - for Category. If not null then we only lists posts without "_do_not_reschedule" 
 * The value 0, which is not a valid category, is used by scheduled batchmove when actually performing the move.
 * @param string $tag - Tag ID - for the selected tag. If not null then we only lists posts without "_do_not_reschedule" 
 * @return array - array of posts found that satisfy the query
 * 
 */
function oik_batchmove_query_reposts( $post_date, $cat=null, $tag=null ) {
  bw_trace2();
  oik_require( "includes/bw_posts.inc" );
  $atts = array();
  $atts['post_type'] = "post";
  $atts['year'] = bw_format_date( $post_date, "Y" ); 
  $atts['monthnum'] = bw_format_date( $post_date, "n" );
  $atts['day' ] = bw_format_date( $post_date, "j" ); 
  $atts['orderby'] =  "date";
  $atts['order'] = "ASC";
  $atts['numberposts'] = -1;
  $atts['post_status'] = array( 'publish', 'future' );
  if ( $cat !== null ) {
    if ( $cat ) {
      $atts['cat'] = $cat;
      $atts['numberposts'] = 1;
    }  
    $atts['meta_key'] = "_do_not_reschedule";
    $atts['meta_compare'] = "NOT EXISTS"; 
    $atts['meta_value'] = "1";
  }
  
  if ( $tag !== null ) {
    //if ( $tag ) {
      $atts['tag_id'] = $tag;
      $atts['numberposts'] = 1;
    //}  
    $atts['meta_key'] = "_do_not_reschedule";
    $atts['meta_compare'] = "NOT EXISTS"; 
    $atts['meta_value'] = "1";
  }
  $posts = bw_get_posts( $atts );
  return( $posts );
}

/**
 * Republish a post
 *
 * Actions to perform during republishing
 * - if there are comments
 *   - change post_title: "From the archives: " $post_title
 *   - prepend post_content: "Originally published on " $post_date " <br />"
 * - update post_date by applying $reschedule using bw_date_adjust()
 *   or, for category reschedule, set date to current date and the optionally specified time.
 * - set post_date_gmt to the new post date. This is done to ensure that if the post date is in the future then the post_status will become 'future'
 * 
 * - remove postmeta fields with the following keys: _wpas_done_all, _wpas_skip_nnnnn, etc
 * 
 * @TODO Also delete any revisions ... good thing we didn't in an earlier version since it would have made problem determination harder
 * 
 */ 
function oik_batchmove_republish_post( $post, $cat=null, $time=null ) {
  oik_batchmove_handle_comments( $post ); 
  if ( $cat ) {
    $post->post_date = oik_batchmove_reschedule_cat( $post->post_date, $time ); 
  } else { 
    $post->post_date = oik_batchmove_reschedule( $post->post_date );
  }  
  $post->post_date_gmt = $post->post_date; //oik_batchmove_reschedule( $post->post_date_gmt );
  // $post->post_status = "future";
  wp_update_post( $post );
  oik_batchmove_delete_postmeta( $post->ID );
}

/** 
 * Alter the post date by applying the "reschedule" option
 *
 * This also takes into account the "reschedule_time" option, if set
 * 
 * A Scheduled post may be in the following status
 * 
 
 * Status    post_date  post_date_gmt post_modified post_modified_gmt   Notes
 * --------- ---------- ------------- ------------- -----------------   --------------------------------
 * draft     future     0             now           now
 * published future                                                     
 * future    future     =             now           now                 Post scheduled for "future" date
 *
 * 
 * If a post is scheduled then there should be a one-off CRON event 'publish_future_post'
 * e.g. extract from cron-view
 * 
 * Jan 1, 2014 @ 9:14 (1388567692)	One-off event	publish_future_post   [0]: 9422
 *
 * @param string $post_date - the current post date in WordPress post_date format
 * @return string - the post date after the "reschedule" amount has been applied
 */
function oik_batchmove_reschedule( $post_date ) {
  // $reschedule = "+451 days";
  $reschedule = oik_batchmove_scheduled_option( "reschedule" );
  $reschedule_time = oik_batchmove_scheduled_option( "reschedule_time" );
  if ( $reschedule_time ) {
    $format = "Y-m-d ${reschedule_time}"; 
  } else {
    $format = "Y-m-d H:i:s";
  }  
  $new_date = bw_date_adjust( $reschedule, $post_date, $format );
  bw_trace2( $new_date, $post_date );
  return( $new_date );
}

/**
 * Reschedule to the current date, setting the time to the desired time if necessary
 *
 * Note: This function is used for both category and tag rescheduling
 */
function oik_batchmove_reschedule_cat( $post_date, $time ) {
  if ( "" == $time ) {
    $new_date = bw_format_date();
    $new_date .= bw_format_date( $post_date, " H:i" );
  } else {
    $new_date = bw_format_date( null, "Y-m-d ${time}" );
  }
  bw_trace2( $new_date, "new_date" );
  return( $new_date );
}

/**
 * Return a localized version of the date
 * 
 * WordPress's date_i18n() function appears misnamed
 * To internationalize a date we'd expect to store it as something that is UTC enabled
 * To localize it we'd then put it into the preferred format and translate it
 * 
 */
if ( !function_exists( "bw_date_i18n" ) ) { 
function bw_date_i18n( $date ) {
  $format = get_option( 'date_format' );
  $date = strtotime( $date );
  $l10n_date = date_i18n( $format, $date );
  return( $l10n_date ); 
}
}

/**
 * Update the title and content when a post with comments is being republished
 *
 * @param object $post - post object
 *
 */
function oik_batchmove_handle_comments( &$post ) {
  bw_trace2(); 
  if ( $post->comment_count > 0 ) {
    $title_prefix = oik_batchmove_scheduled_option( "title_prefix" );
    $prepend_content_pre_date = oik_batchmove_scheduled_option( "prepend_content_pre_date" );
    $prepend_content_post_date = oik_batchmove_scheduled_option( "prepend_content_post_date" );
    $post->post_title = $title_prefix . $post->post_title;
    $prepend = $prepend_content_pre_date;
    // $prepend .= "&nbsp;";
    $prepend .= bw_date_i18n( $post->post_date );  
    // $prepend .= "&nbsp;";
    $prepend .= $prepend_content_post_date;
    $post->post_content = $prepend . $post->post_content;
  }
}   

/**
 * 
 *  $metadata
 options_page_oik_batchmove_scheduled 41 0 27398904/27618192 oik_batchmove_delete_postmeta(4)  Array
(
    [_edit_last] => Array

    [_wp_old_slug] => Array

    [_edit_lock] => Array
    [_sexybookmarks_shortUrl] => Array
    [_sexybookmarks_permaHash] => Array
    [_yoast_wpseo_linkdex] => Array

)
 */ 
function oik_batchmove_delete_postmeta( $id ) {
  $metadata = get_metadata( "post", $id );
  bw_trace2( $metadata );
  if  ($metadata ) {
    foreach ( $metadata as $key => $values ) {
      $delete_this = oik_batchmove_check_postmeta_key( $key );
      if ( $delete_this ) {
        delete_post_meta( $id, $key );
      }  
    } 
  }   
} 

/**
 * Delete any post meta data that we won't need in the future
 * 
 * This includes post meta data created by:
 * Jetpack Publicize - thereby enabling the posts to be Publicized again.
 * 
 *  3060 _wpas_done_all
 *     9 _wpas_mess
 *  1515 _wpas_skip_3822231
 *  1511 _wpas_skip_3822237
 *     2 _wpas_skip_4737548
 *   744 _wpas_skip_4737700
 *
 *
 * blogger redirect - as these are assumed to be no longer necessary   
 *    
 *   2245	blogger_author
 *   2245	blogger_blog
 *   2245	blogger_permalink
 *   2245	_blogger_self
 *
 * _sexybookmarks_ was just a test.
 */
function oik_batchmove_check_postmeta_key( $key ) {
  bw_trace2();
  $prefixes = array( "_wpas_"
                   , "blogger_"
                   , "_blogger_"
                   // , "_sexybookmarks_" 
                   );
  $deletethis = false;                 
  for ( $index = 0; ( $index < count( $prefixes )) && !$deletethis; $index++ ) {
    $deletethis = strpos( $key, $prefixes[$index] ) === 0;
  } 
  return( $deletethis );                
} 



