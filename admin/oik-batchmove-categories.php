<?php // (C) Copyright Bobbing Wide 2014

/**  Changes from oik_x2t to oik_batchmove_categories ( bmc )
    
     x2t => bmc
     "Taxonomies to types" => "Category reschedule" 
     "bw_bmcs" => "bw_batchmove_categories" ?
     page=oik_x2t => page=oik_batchmove_scheduled
     
*/    

/**
 * Reschedule oldest in category to time page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_bmc                    No        n/a                   Yes             -            -            -
 * delete_bmc                     No        delete selected bmc  -               -            -            Yes
 * edit_bmc                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_bmc_edit_bmc         Yes       update selected bmc  -               -            Yes          Yes
 * _oik_bmc_add_bmc
 * _oik_bmc_add_oik_bmc
 * 
 * 
*/
function oikbmc_lazy_do_page() {
  // oik_menu_header( "Reschedule oldest in category", "w100pc" );
  $validated = false;
  
  $preview_bmc = bw_array_get( $_REQUEST, "preview_bmc", null );
  $delete_bmc = bw_array_get( $_REQUEST, "delete_bmc", null );
  $edit_bmc = bw_array_get( $_REQUEST, "edit_bmc", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_bmc_edit_bmc = bw_array_get( $_REQUEST, "_oik_bmc_edit_bmc", null );
  $oik_bmc_add_oik_bmc = bw_array_get( $_REQUEST, "_oik_bmc_add_oik_bmc", null );
  $oik_bmc_add_bmc = bw_array_get( $_REQUEST, "_oik_bmc_add_bmc", null );
  if ( $oik_bmc_add_bmc || $oik_bmc_add_oik_bmc ) {
    $preview_bmc = null;
    $delete_bmc = null;
    $edit_bmc = null; 
  }  
  
  
  if ( $preview_bmc ) {
    oik_box( NULL, NULL, "Preview", "oik_bmc_preview" );
  } 
  
  if ( $delete_bmc ) { 
    _oik_bmc_delete_bmc( $delete_bmc );
  }  

  if ( $edit_bmc ) {
    global $bw_bmc;
    $bw_bmcs = get_option( "bw_bmcs" );
    $bw_bmc = bw_array_get( $bw_bmcs, $edit_bmc, null );
    $bw_bmc['args']['bmc'] = $edit_bmc; 
    bw_trace2( $bw_bmc );
  }
  if ( $oik_bmc_edit_bmc ) {  
    $validated = _oik_bmc_bmc_validate( false );
  }  
  
  if ( $oik_bmc_add_oik_bmc ) {
    $validated = _oik_bmc_bmc_validate( true );
  }
  
  if ( $oik_bmc_add_bmc || ( $oik_bmc_add_oik_bmc && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_bmc_add_oik_bmc" );
  }
  
  if ( $edit_bmc || $oik_bmc_edit_bmc || $validated ) {
    // oik_box( null, null, "Edit relationship", "oik_bmc_edit_bmc" );
  }
  oik_box( NULL, NULL, "Categories", "oik_bmc_bmcs" );
  //oik_menu_footer();
  bw_flush();
}

/** 
 * Display a current bmc mapping
 */
function _oik_bmc_bmc_row( $bmc, $data ) {
  bw_trace2();
  $row = array();
  $args = $data['args'];
  
	$category = get_term(  $args['category'], 'category' );
  if ( is_wp_error( $category ) || is_null( $category)  ) {
    $row[] = "Invalid category - please Delete ";
  } else {
    $row[] = $category->name . "&nbsp"; 
  }
  $row[] = esc_html( stripslashes( $args['time'] ) ) . "&nbsp";
  $links = null;
  //$links = retlink( null, admin_url("admin.php?page=oik_batchmove_scheduled&amp;preview_bmc=$bmc"), "Preview" );
  //$links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_batchmove_scheduled&amp;delete_bmc=$bmc"), "Delete" ); 
  $links .= "&nbsp;";
  // $links .= retlink( null, admin_url("admin.php?page=oik_batchmove_scheduled&amp;edit_bmc=$bmc"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of Category rescheduling
 * 
 */
function _oik_bmc_bmc_table() {
  $bw_bmcs = get_option( "bw_bmcs" );
  if ( is_array( $bw_bmcs) && count( $bw_bmcs )) {
    foreach ( $bw_bmcs as $bmc => $data ) {
      //$bmc = bw_array_get( $bw_bmc, "bmc", null );
      _oik_bmc_bmc_row( $bmc, $data );
    }
  }  
}

/** 
 * @TODO - implement logic to prevent the relationship being added multiple times
 * 
 */
function oik_bmc_check_relationship_exists( $bmc )  {
  $bmc_exists = bw_get_option( $bmc, "bw_bmcs" );
  return( $bmc_exists );
}  

/**
 * Check if the reschedule Category already exists
 *
 * If not then add to the options using bw_update_option() 
 * then empty out the bmc field for the next one
 *
 */
function _oik_bmc_add_oik_bmc( $bw_bmc ) {
  $bmc = bw_array_get( $bw_bmc['args'], "bmc", null );
  $bmc_exists = oik_bmc_check_relationship_exists( $bmc ); 
  if ( $bmc_exists ) {
    p( "Reschedule already defined, try another Category." );   
    $ok = false;

  } else {
    unset( $bw_bmc['args']['bmc'] );
    bw_update_option( $bmc, $bw_bmc, "bw_bmcs" );
    // We don't need to add the bmc now! 
    $bw_bmc['args']['bmc'] = "";
    $ok = true;
  }
  return( $ok ); 
}

/**
 * Update the category to time relationship
 */
function _oik_bmc_update_bmc( $bw_bmc ) {
  $bmc = bw_array_get( $bw_bmc['args'], "bmc", null );
  if ( $bmc ) { 
    unset( $bw_bmc['args']['bmc'] );
    bw_update_option( $bmc, $bw_bmc, "bw_bmcs" );
  } else {
    bw_trace2( $bmc, "Logic error?" );
  }  
}

/**
 * Delete the category to time relationship
 */
function _oik_bmc_delete_bmc( $bw_bmc ) {
  bw_delete_option( $bw_bmc, "bw_bmcs" );
}  


/**
 * bmc must not be blank
 */
function oik_diy_validate_bmc( $bmc ) {
  $valid = isset( $bmc );
  if ( $valid ) { 
    $bmc = trim( $bmc );
    $valid = strlen( $bmc ) > 0;
  } 
  if ( !$valid ) { 
    p( "bmc must not be blank" );   
  }  
  return $valid;
}
    
/**
 * Validate the Category reschedule
 *
 * We only allow one entry per category, as it doesn't make sense to define multiple target times.
 *
 */
function _oik_bmc_bmc_validate( $add_bmc=true ) {

  global $bw_bmc;
  $bw_bmc['args']['time'] = bw_array_get( $_REQUEST, "time", null );
  $bw_bmc['args']['category'] = bw_array_get( $_REQUEST, "category", null );
  
  $bw_bmc['args']['bmc'] = $bw_bmc['args']['category'];   
  // $bw_bmc['args']['hierarchical'] = bw_array_get( $_REQUEST, "hierarchical", null );
  // $bw_bmc['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  
  bw_trace2( $bw_bmc, "bw_bmc" );
  
  $ok = oik_diy_validate_bmc( $bw_bmc['args']['bmc'] );
  
  // validate the fields and add the bmc IF it's OK to add
  // $add_bmc = bw_array_get( $_REQUEST, "_oik_bmc_add_oik_bmc", false );
  if ( $ok ) {
    if ( $add_bmc ) {
      $ok = _oik_bmc_add_oik_bmc( $bw_bmc );  
    } else {
      $ok = _oik_bmc_update_bmc( $bw_bmc );
    }
  }  
  return( $ok );
}


/**
 * Display the table of existing Category reschedules, with optional time
 * 
 * This may be extended to include custom taxonomies and categories as well
 * - which will require the tag or category to be a custom field name **?**
 *
 */
function oik_bmc_bmcs() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Category", "Time", "Actions" ));
  etag( "thead");
  _oik_bmc_bmc_table();
  etag( "table" );
  p( isubmit( "_oik_bmc_add_bmc", "Add category reschedule", null, "button-primary" ) );
  etag( "form" );
} 

/**
 * Return a list of categories
 */
function bw_list_categories() {
  $categories = get_categories( null ); 
  return( $categories );
}

function oik_bmc_add_oik_bmc( ) {
  global $bw_bmc;
	$bw_bmc['args']['time'] = bw_array_get( $_REQUEST, "time", null );
  bw_trace2( $bw_bmc, "bw_bmc", false);
  bw_form();
  stag( "table", "wide-fat" );
  //$types = bw_list_registered_post_types();
  //bw_select( "type", "Time", null, array( "#options" => $types ) ); 
  //$taxonomies = bw_list_categories();
  // bw_select( "category", "Category", null, array( "#options" => $taxonomies )) ; 
  
  bw_tablerow( array( "Category", oik_batchmove_category_select( "category", "" ) ) ); 
  bw_textfield( "time", 5, "time", stripslashes( $bw_bmc['args']['time'] ) );  
  etag( "table" );
  p( isubmit( "_oik_bmc_add_oik_bmc", "Add new category to time", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Edit the category to time relationship
 */
function oik_bmc_edit_bmc( ) {
  global $bw_bmc;
  bw_form();
  stag( "table", "wide-fat" );
  
  bw_tablerow( array( "Relationship", $bw_bmc['args']['bmc'] . ihidden( 'bmc', $bw_bmc['args']['bmc']) ) );
  //bw_textfield( "bmc", 20, "Post bmc", $bw_bmc['args']['bmc'] );
  //bw_textfield( "type", 30, "Time", stripslashes( $bw_bmc['args']['type'] ) );
  //$field = esc_textarea( $bw_bmc['args']['field'] );
  //bw_trace2( $field, "esc_textarea field", false );
  bw_textfield( "category", 100, "category", stripslashes( $bw_bmc['args']['category'] ) );
  //bw_checkbox( "hierarchical", "Hierarchical bmc?", $bw_bmc['args']["hierarchical"] );
  etag( "table" );
  p( isubmit( "_oik_bmc_edit_bmc", "Change relationship", null, "button-primary" ) );
  etag( "form" );
}

/**
 * View the category to time relationship
 */
function oik_bmc_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_bmc = bw_array_get( $_REQUEST, "preview_bmc", null );
  if ( $preview_bmc ) {
    sdiv( "oik_preview");
    //bw_invoke_bmc( $preview_bmc, null, "Preview of the $preview_bmc bmc" );
    p( "Preview not yet implemented" );
    ediv( "oik_preview");
  }
}



