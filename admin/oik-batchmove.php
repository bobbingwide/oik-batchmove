<?php // (C) Copyright Bobbing Wide 2013,2014

/**
 * Implement "oik_admin_menu" for oik-batchmove 
 *
 * Note: User must have "manage_options" or "manage_categories" capability to be able to use this function.
 */
function oik_batchmove_lazy_admin_menu() {
  add_submenu_page( 'oik_menu', 'oik batchmove', 'Batch move', 'manage_options', 'oik_batchmove', 'oik_batchmove_do_page' );
  add_posts_page( "Batch move", "Batch move", 'manage_categories', "oik_batchmove", "oik_batchmove_do_page");
  
  register_setting( 'bw_scheduled', 'bw_scheduled', 'oik_plugins_validate' ); // No validation for oik-batchmove ?
  add_submenu_page( 'oik_menu', 'oik scheduled republish', 'Scheduled republish', 'manage_options', 'oik_batchmove_scheduled', 'oik_batchmove_scheduled_page' );
}

/**
 * Implement batchmove main page 
 */
function oik_batchmove_do_page() {
  oik_require( "includes/bw_posts.inc" );
  oik_menu_header( "batch change post categories or published date", "w90pc" );
  oik_box( null, null, "Results", "oik_batchmove_results" );
  oik_box( null, null, "Selection criteria. Choose the posts to alter", "oik_batchmove_selection" );
  oik_box( null, null, "Selected posts", "oik_batchmove_selected" );
  oik_box( null, null, "Usage notes", "oik_batchmove_usage_notes" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Implement scheduled batchmove page 
 */
function oik_batchmove_scheduled_page() {
  oik_require( "admin/oik-batchmove-cron.php", "oik-batchmove" );
  oik_batchmove_lazy_scheduled_page();
}

/**
 * Display the order_by select list 
 */
function oik_batchmove_order_by() {
  $orderby = array( "date" => __("Date Posted" )
                  , "modified" => __("Date modified" )
                  , "author" => __( "Author" )
                  , "title" => __( "Title" )
                  , "status" => __( "Status" )
                  );
  bw_select( "_batchmove_order_by", "Order by", null, array( "#options" => $orderby ) );
}

/**
 * Display the sort sequence select list 
 */
function oik_batchmove_order() {
  $order = array( "asc" => "Ascending" 
                , "desc" => "Descending" 
                );
  bw_select( "_batchmove_order", "Sequence", null, array( "#options" => $order ) );
}

/**
 * Display a category selection drop down list 
 * 
 * Include: empty categories and show the count of each category
 * @link http://codex.wordpress.org/Function_Reference/wp_dropdown_categories
 *
 * @param string $name - name for the select field
 * @param string $all - Text for "show_option_all" 
 */
function oik_batchmove_category_select( $name, $all="All" ) {
  /* Do we have to worry about selected? It would be pretty obvious from the selected list **?** */
  /* Answer - Yes, since we need to redisplay after each action */
  $args = array( "show_count" => 1
               , "hide_empty" => 0  
               , "echo" => 0
               , "hierarchical" => 1
               , "name" => $name //  "" 
               , "show_option_all" => $all
               );
  $selected = bw_array_get( $_REQUEST, $name, null );
  if ( $selected ) {
    $args['selected'] = $selected;
  }
  return( wp_dropdown_categories( $args ) );
} 

/**
 * Display the post category selection form 
 */
function oik_batchmove_selection() {
  // p( "Choose the posts to alter" );
  bw_form();
  stag( "table", "widefat" );
  bw_tablerow( array( "Category", oik_batchmove_category_select( "_batchmove_category_select" ) ) ); 
  //bw_textfield( "_batchmove_rows", 15, "Rows per page", null );
  oik_batchmove_order_by();
  oik_batchmove_order();
  
  //bw_textfield( "_batchmove_keywords", 30, "Keywords", null );
  //bw_textfield( "_batchmove_tags", 30, "Tags", null );
  
  oik_require( "bw_metadata.inc" ); 
  
  //$from_date = bw_get_option( "yearFrom" );
  
  bw_form_field_date( "_batchmove_from_date", "date", "From date", null, null );
  bw_form_field_date( "_batchmove_to_date", "date", "To date", null, null );
  
  // bw_textfield( "_batchmove_to_date", 10,  "Date to", null, null  );
  etag( "table" );
  p( isubmit( "_oik_batchmove_filter", "Filter", null, "button-primary" ) );
  etag( "form" );
}
  
/**
 * Offer different actions to perform
 *
 */
function oik_batchmove_action() {
  $actions = array( "update" => "Update to selected category" 
                  , "add" => "Add selected category"
                  , "delete" => "Delete selected category" 
                  , "republish" => "Republish post" 
                  );
  bw_select( "_batchmove_action", "Action", null, array( "#options" => $actions ) );
}

/**
 * Add a hidden field to the form if the field value is set
 * @param string $name - name of the field 
 */
function oik_batchmove_hidden_field( $name ) {
  $value = bw_array_get( $_REQUEST, $name, null );
  if ( $value ) {
    e( ihidden( $name, $value ) );
  }
  bw_trace2( $value, "hidden field" ); 
}

/**
 * Add hidden fields to the form
 */
function oik_batchmove_hidden_fields() {
  oik_batchmove_hidden_field( "_batchmove_category_select" );
  oik_batchmove_hidden_field( "_batchmove_order_by" );
  oik_batchmove_hidden_field( "_batchmove_order" );
  oik_batchmove_hidden_field( "_batchmove_from_date" );
  oik_batchmove_hidden_field( "_batchmove_to_date" );
}  

/**
 * Display the selected posts in a form, allowing them to be selected, and the actions that can be performed
 *
 */
function oik_batchmove_selected() {
  bw_form();
  oik_batchmove_hidden_fields();
  oik_batchmove_display_posts();
  
  stag( "table", "widefat" );
  bw_tablerow( array( "Target Category", oik_batchmove_category_select( "_batchmove_category_apply", "" ) ) ); 
  bw_form_field_( "_batchmove_date_adjustment", "text", "Date adjustment e.g. +1 year", null, null );
  oik_batchmove_action();
  etag( "table" );
  p( isubmit( "_oik_batchmove_apply", "Apply changes", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Display the results of performing the actions 
 */
function oik_batchmove_results() {
  // p( "This is what happened ");
  $action = bw_array_get( $_REQUEST, "_oik_batchmove_apply", null );
  if ( $action ) {
    $new_cat = bw_array_get( $_REQUEST, "_batchmove_category_apply", null );
    $action = bw_array_get( $_REQUEST, "_batchmove_action", null ); 
    if ( $action ) {
      oik_batchmove_perform( $action, $new_cat );
    }
  }
}

/**
 * Perform the selected action against the selected post 
 * 
 * @param string $action - the action to perform - add, update, delete, republish, etc
 * @param ID $id - the ID of the post to update
 * 
 * We retrieve the post to be updated but don't need to pass it to the $action_func
 * since that works with the $id alone. 
 */
function oik_batchmove_perform_action( $action, $id ) {
  $post = bw_get_post( $id, "post" );
  bw_trace2( $post );
  p( "Performing $action for post $id " . $post->post_title );
  $action_func = "oik_batchmove_perform_$action"; 
  if ( is_callable( $action_func ) ) {
    $action_func( $id ); 
  } else {
    p( " $action_func not defined " );
  }
  do_action( "oik_batchmove_perform_$action", $post ); 
}

/**
 * Update a post by adding then new category then removing the original
 * 
 * This used to be the other way around but I noticed a bug when the post was Uncategorized.
 * You can't remove all categories so the delete didn't work until the new category was added.
 *  
 * @param post $id - the post to be updated
 *  
 */
function oik_batchmove_perform_update( $id ) {
  $from_category = bw_array_get( $_REQUEST, "_batchmove_category_select", null );
  $to_category = bw_array_get( $_REQUEST, "_batchmove_category_apply", null ); 
  if ( $from_category && $to_category ) {
    oik_batchmove_perform_add( $id, $to_category );  
    oik_batchmove_perform_delete( $id, $from_category ); 
  }
}

/**
 * Add a category to a post 
 * @param integer $id - the post id to be updated
 * @param integer $to_category - the ID if the category to be added
*/
function oik_batchmove_perform_add( $id, $to_category=null ) {
  if ( !$to_category ) {
    $to_category = bw_array_get( $_REQUEST, "_batchmove_category_apply", null ); 
  }
  if ( $to_category ) {
    $categories = wp_get_post_categories( $id ); 
    bw_trace2( $categories, "categories" );
    $categories[] =  $to_category;
    
    bw_trace2( $categories, "categories after", false );
    wp_set_post_categories( $id, $categories );
  }
}

/**
 * Delete a category from a post
 * @param integer $id - the post id to be updated
 * @param integer $to_category - the ID if the category to be deleted
 */
function oik_batchmove_perform_delete( $id, $from_category=null ) {
  if ( !$from_category ) {
    $from_category = bw_array_get( $_REQUEST, "_batchmove_category_select", null ); 
  }
  if ( $from_category ) {
    $categories = wp_get_post_categories( $id ); 
    bw_trace2( $categories, "categories" );
    $categories = bw_array_unset_value( $categories, $from_category );
    // $categories[] =  $to_category;
    
    wp_set_post_categories( $id, $categories );
  }
}

function bw_array_unset_value( $array, $value ) {
  $flip = array_flip( $array );
  unset( $flip[ $value ] );
  $array = array_flip( $flip );
  return( $array );
}

/**
 * Perform republish for a selected post
 * @param integer $id - ID of the post to be republished
 * 
 * Note that this function pays no attention to comments. It's a quick and dirty republish.
 * Q: Do we have to alter post_date_gmt ? 
 * A: Yes, as we have now discovered with scheduled batchmove
 * 
 */
function oik_batchmove_perform_republish( $id ) {
  $post = array(); 
  $post['ID'] = $id; 
  $date_adjustment = bw_array_get( $_REQUEST, "_batchmove_date_adjustment", null );
  if ( $date_adjustment ) {
    $post = get_post( $id, ARRAY_A ); 
    $post['post_date'] = bw_date_adjust( $date_adjustment, $post['post_date'], "Y-m-d H:i:s" ); 
    e( "New post date: " . $post['post_date'] ); 
  } else {
    $post['post_date'] = bw_format_date( null, "Y-m-d H:i:s" ); 
    
  }
  $post['post_date_gmt'] = $post['post_date'];
  wp_update_post( $post );
}

/**
 * Perform the selected action for each of the selected posts
 */
function oik_batchmove_perform( $action, $new_cat ) {
  p( "Applying $action for $new_cat " );
  
  $ids = bw_array_get( $_REQUEST, "_batchmove_ids", null );
  if ( $ids && count( $ids ) ) {
    // p( "something to do: ". count( $ids ) );
    foreach ( $ids as $id ) {
      oik_batchmove_perform_action( $action, $id );
    }
  } else {
    p( "No posts selected." );
  }
}
  

function oik_batchmove_usage_notes() {
  p( "Use the Selection criteria to list the posts you may want to alter and click on Filter." );
  p( "Select the posts to change." );
  p( "Select the target category, choose the Action to perform, click on Apply changes." );
}


/**
 * Add a "posts_where" filter field
 */
function oik_batchmove_add_filter_field( $filter ) {
  global $bw_filter; 
  if ( !isset( $bw_filter ) ) {
    add_filter( "posts_where", "oik_batchmove_filter_where" );
    $bw_filter = null ;
  } 
  $bw_filter .= " ";
  $bw_filter .= $filter;
  bw_trace2();
  p( "Added: $filter"  );
  p( "Filter is now: $bw_filter" );
}

/**
 * Add filters for from and to dates
 */
function oik_batchmove_add_filter_fields() {
  $from_date = bw_array_get( $_REQUEST, "_batchmove_from_date", null ) ;
  if ( $from_date ) {
    oik_batchmove_add_filter_field( "AND post_date >= '$from_date 00:00:00'" );
    
  }
  $to_date = bw_array_get( $_REQUEST, "_batchmove_to_date", null );
  if ( $to_date ) {
    oik_batchmove_add_filter_field( "AND post_date <= '$to_date 23:59:59'" );
  }
}

/**
 * Implement "filter_where" filter for bw_get_posts()
 * 
 * Create a new filtering function that will add our where clause to the query
 * Note: This filter can only be applied once. It automatically clears itself. Is this OK **?**
 * If not, we'll have to remove the filter after the get_posts - which is tiresome.
 *
 */
function oik_batchmove_filter_where( $where = '' ) {
  global $bw_filter;
  if ( isset( $bw_filter ) ) {
    $where .= $bw_filter;
    unset( $bw_filter );
  }  
  bw_trace2();
  return( $where );
}

/**
 * Display the selected posts based on the selection criteria 
 */
function oik_batchmove_display_posts() {
  $category_select = bw_array_get( $_REQUEST, "_batchmove_category_select", null );
  oik_require( "includes/bw_posts.inc" );
  $args = array( "post_type" => "post" 
                 , "orderby" => bw_array_get( $_REQUEST, "_batchmove_order_by", null )
                 , "order" => bw_array_get( $_REQUEST, "_batchmove_order", null )
                 );
  //p( "Category selected: $category_select" );
  if ( $category_select !== null ) { 
    if ( $category_select ) {
      $args['category'] = $category_select;
    } elseif ( $category_select === "0" ) {
      p( "Listing ALL categories. Perhaps you should filter on the date. " );
    } else {
      // null;
      p( "eh?" );
    }
    oik_batchmove_add_filter_fields();                 
    $posts = bw_get_posts( $args );
    p( "Total posts selected: " . count( $posts ) );
    oik_batchmove_display_selection( $posts );
  }  
}

/**
 * Display the table header for the batch move selection table
 *
 * Note: We manually create the table heading since we need to set the class of the table heading checkbox to "check-column"
 * in order for the magic in common.js to take effect - selecting or deselecting all the other check boxes.
 */
function oik_batchmove_display_header() {
  stag( "table", "widefat" );
  stag( "thead");
  stag( "tr" );
  th( _oikbm_icheckselectall(), "check-column" );
  th( "ID" );
  th( "Pub. Date" );
  th( "Title" );
  th( "Comments" );
  etag( "tr" );
  etag( "thead");
}

/** 
 * Display the table of selected posts
 * @param array $posts - array of post objects
 */
function oik_batchmove_display_selection( $posts ) {
  if ( $posts && count( $posts ) ) {
    oik_batchmove_display_header();
    foreach ( $posts as $post ) {
      oik_batchmove_display_post( $post );
    } 
    etag( "table" );
  }
}

/**
 * Display the values of the current post 
 *
 * The class of the checkbox is "check-column" - enabling it to be selected / de-selected by the checkbox in the header.
 * @see oik_batchmove_display_header() 
 *
 * @param post $post - the post object
 * 
 */
function oik_batchmove_display_post( $post ) {
  $checkbox = _oikbm_icheck( "_batchmove_ids[]", $post->ID );
  stag( "tr" );
  th( $checkbox, "check-column" );
  $link = retlink( null, get_permalink( $post->ID ), $post->ID );
  td( $link );
  td( $post->post_date );
  td( $post->post_title );
  td( $post->comment_count );
  etag( "tr" );
} 

/**
 * Implement a select all check box 
 *
 * This code reproduces that found in other wp-admin pages
 * There is no need to enqueue common.js 
 */
function _oikbm_icheckselectall( ) {
  static $cb_counter = 0;
  $cb_counter++;
  return( '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />' );
}

/** 
 * Display an input field checkbox which doesn't have a hidden field 
 * This means that only those which are selected are returned.
 * @param string $name - the name of the field e.g. batchmove[]
 * @param string $value - the value for the field e.g. 149
 */
function _oikbm_icheck( $name, $value ) {
  $it = "<input ";
  $it .= kv( "type", "checkbox" );
  $it .= kv( "name", $name );
  $it .= kv( "value", $value );
  $it .= "/>";
  return( $it );
}

