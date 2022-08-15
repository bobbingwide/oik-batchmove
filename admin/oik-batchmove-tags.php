<?php // (C) Copyright Bobbing Wide 2014

/**  Changes from oik_x2t to oik_batchmove_categories ( bmc )
    
     x2t => bmc
     "Taxonomies to types" => "Category reschedule" 
     "bw_bmcs" => "bw_batchmove_categories" ?
     page=oik_x2t => page=oik_batchmove_scheduled
     
     Changes from tag to tags ( bmc => bmt )
     
     Time to attempt to create a generic solution?
     
     
     
     
*/    

/**
 * Reschedule oldest in tag to time page
 *
 * Processing depends on the button that was pressed. There should only be one!
 * 
 * Selection                       Validate? Perform action        Display preview Display add  Display edit Display select list
 * ------------------------------- --------  -------------------   --------------- ------------ ------------ -------------------
 * preview_bmt                    No        n/a                   Yes             -            -            -
 * delete_bmt                     No        delete selected bmt  -               -            -            Yes
 * edit_bmt                       No        n/a                   -               -            Yes          Yes
 *
 * _oik_bmt_edit_bmt         Yes       update selected bmt  -               -            Yes          Yes
 * _oik_bmt_add_bmt
 * _oik_bmt_add_oik_bmt
 * 
 * 
*/
function oikbmt_lazy_do_page() {
  $validated = false;
  
  $preview_bmt = bw_array_get( $_REQUEST, "preview_bmt", null );
  $delete_bmt = bw_array_get( $_REQUEST, "delete_bmt", null );
  $edit_bmt = bw_array_get( $_REQUEST, "edit_bmt", null );
  
  /** These codes override the ones from the list... but why do we need to do it? 
   * Do we have to receive the others in the $_REQUEST **?**
   *
  */
  $oik_bmt_edit_bmt = bw_array_get( $_REQUEST, "_oik_bmt_edit_bmt", null );
  $oik_bmt_add_oik_bmt = bw_array_get( $_REQUEST, "_oik_bmt_add_oik_bmt", null );
  $oik_bmt_add_bmt = bw_array_get( $_REQUEST, "_oik_bmt_add_bmt", null );
  if ( $oik_bmt_add_bmt || $oik_bmt_add_oik_bmt ) {
    $preview_bmt = null;
    $delete_bmt = null;
    $edit_bmt = null; 
  }  
  
  
  if ( $preview_bmt ) {
    oik_box( NULL, NULL, "Preview", "oik_bmt_preview" );
  } 
  
  if ( $delete_bmt ) { 
    _oik_bmt_delete_bmt( $delete_bmt );
  }  

  if ( $edit_bmt ) {
    global $bw_bmt;
    $bw_bmts = get_option( "bw_bmts" );
    $bw_bmt = bw_array_get( $bw_bmts, $edit_bmt, null );
    $bw_bmt['args']['bmt'] = $edit_bmt; 
    bw_trace2( $bw_bmt );
  }
  if ( $oik_bmt_edit_bmt ) {  
    $validated = _oik_bmt_bmt_validate( false );
  }  
  
  if ( $oik_bmt_add_oik_bmt ) {
    $validated = _oik_bmt_bmt_validate( true );
  }
  
  if ( $oik_bmt_add_bmt || ( $oik_bmt_add_oik_bmt && !$validated )  ) {
    oik_box( NULL, NULL, "Add new", "oik_bmt_add_oik_bmt" );
  }
  
  if ( $edit_bmt || $oik_bmt_edit_bmt || $validated ) {
    // oik_box( null, null, "Edit relationship", "oik_bmt_edit_bmt" );
  }
  oik_box( NULL, NULL, "Tags", "oik_bmt_bmts" );
  //oik_menu_footer();
  bw_flush();
}

/** 
 * Display a current bmt mapping
 */
function _oik_bmt_bmt_row( $bmt, $data ) {
  bw_trace2();
  $row = array();
  $args = $data['args'];
  
	$tag = get_term(  $args['tag'], 'post_tag' );
  if ( is_wp_error( $tag ) || is_null( $tag)  ) {
    $row[] = "Invalid tag - please Delete ";
  } else {
    $row[] = $tag->name . "&nbsp"; 
  }
  $row[] = esc_html( stripslashes( $args['time'] ) ) . "&nbsp";
  $links = null;
  //$links = retlink( null, admin_url("admin.php?page=oik_batchmove_scheduled&amp;preview_bmt=$bmt"), "Preview" );
  //$links .= "&nbsp;";
  $links .= retlink( null, admin_url("admin.php?page=oik_batchmove_scheduled&amp;delete_bmt=$bmt"), "Delete" ); 
  $links .= "&nbsp;";
  // $links .= retlink( null, admin_url("admin.php?page=oik_batchmove_scheduled&amp;edit_bmt=$bmt"), "Edit" );   
  $row[] = $links;
  bw_tablerow( $row );
}

/**
 * Display the table of Tag rescheduling
 * 
 */
function _oik_bmt_bmt_table() {
  $bw_bmts = get_option( "bw_bmts" );
  if ( is_array( $bw_bmts) && count( $bw_bmts )) {
    foreach ( $bw_bmts as $bmt => $data ) {
      //$bmt = bw_array_get( $bw_bmt, "bmt", null );
      _oik_bmt_bmt_row( $bmt, $data );
    }
  }  
}

/** 
 * @TODO - implement logic to prevent the relationship being added multiple times
 * 
 */
function oik_bmt_check_relationship_exists( $bmt )  {
  $bmt_exists = bw_get_option( $bmt, "bw_bmts" );
  return( $bmt_exists );
}  

/**
 * Check if the reschedule Tag already exists
 *
 * If not then add to the options using bw_update_option() 
 * then empty out the bmt field for the next one
 *
 */
function _oik_bmt_add_oik_bmt( $bw_bmt ) {
  $bmt = bw_array_get( $bw_bmt['args'], "bmt", null );
  $bmt_exists = oik_bmt_check_relationship_exists( $bmt ); 
  if ( $bmt_exists ) {
    p( "Reschedule already defined, try another Tag." );   
    $ok = false;

  } else {
    unset( $bw_bmt['args']['bmt'] );
    bw_update_option( $bmt, $bw_bmt, "bw_bmts" );
    // We don't need to add the bmt now! 
    $bw_bmt['args']['bmt'] = "";
    $ok = true;
  }
  return( $ok ); 
}

/**
 * Update the tag to time relationship
 */
function _oik_bmt_update_bmt( $bw_bmt ) {
  $bmt = bw_array_get( $bw_bmt['args'], "bmt", null );
  if ( $bmt ) { 
    unset( $bw_bmt['args']['bmt'] );
    bw_update_option( $bmt, $bw_bmt, "bw_bmts" );
  } else {
    bw_trace2( $bmt, "Logic error?" );
  }  
}

/**
 * Delete the tag to time relationship
 */
function _oik_bmt_delete_bmt( $bw_bmt ) {
  bw_delete_option( $bw_bmt, "bw_bmts" );
}  


/**
 * bmt must not be blank
 */
function oik_diy_validate_bmt( $bmt ) {
  $valid = isset( $bmt );
  if ( $valid ) { 
    $bmt = trim( $bmt );
    $valid = strlen( $bmt ) > 0;
  } 
  if ( !$valid ) { 
    p( "bmt must not be blank" );   
  }  
  return $valid;
}
    
/**
 * Validate the Tag reschedule
 *
 * We only allow one entry per tag, as it doesn't make sense to define multiple target times.
 *
 */
function _oik_bmt_bmt_validate( $add_bmt=true ) {

  global $bw_bmt;
  $bw_bmt['args']['time'] = bw_array_get( $_REQUEST, "time", null );
  $bw_bmt['args']['tag'] = bw_array_get( $_REQUEST, "tag", null );
  
  $bw_bmt['args']['bmt'] = $bw_bmt['args']['tag'];   
  // $bw_bmt['args']['hierarchical'] = bw_array_get( $_REQUEST, "hierarchical", null );
  // $bw_bmt['args']['title'] = bw_array_get( $_REQUEST, "title", null );
  
  bw_trace2( $bw_bmt, "bw_bmt" );
  
  $ok = oik_diy_validate_bmt( $bw_bmt['args']['bmt'] );
  
  // validate the fields and add the bmt IF it's OK to add
  // $add_bmt = bw_array_get( $_REQUEST, "_oik_bmt_add_oik_bmt", false );
  if ( $ok ) {
    if ( $add_bmt ) {
      $ok = _oik_bmt_add_oik_bmt( $bw_bmt );  
    } else {
      $ok = _oik_bmt_update_bmt( $bw_bmt );
    }
  }  
  return( $ok );
}


/**
 * Display the table of existing Tag reschedules, with optional time
 * 
 * This may be extended to include custom taxonomies and categories as well
 * - which will require the tag or tag to be a custom field name **?**
 *
 */
function oik_bmt_bmts() {
  p( "" );
  bw_form();
  stag( "table", "widefat" );
  stag( "thead");
  bw_tablerow( array( "Tag", "Time", "Actions" ));
  etag( "thead");
  _oik_bmt_bmt_table();
  etag( "table" );
  p( isubmit( "_oik_bmt_add_bmt", "Add tag reschedule", null, "button-primary" ) );
  etag( "form" );
} 

/**
 * Return a list of tags
function bw_list_tags() {
  return( $tags );
}
 */


/**
 * Display a tag selection drop down list
 *
 * We use the default parameters to get_terms() - so that unused tags are not listed.
 
 * 
     [0] => stdClass Object
        (
            [term_id] => 94
            [name] => Artisteer
            [slug] => artisteer
            [term_group] => 0
            [term_taxonomy_id] => 94
            [taxonomy] => post_tag
            [description] => 
            [parent] => 0
            [count] => 1
        )
 
 * 
 */
function oik_batchmove_tag_select() {
  $tags = get_terms( 'post_tag' );
  $term_array = bw_term_array( $tags );
  bw_select( "tag", "Tags", null, array( "#options" => $term_array ) );
  return( $tags );
} 

function oik_bmt_add_oik_bmt( ) {
  global $bw_bmt;
  $bw_bmt['args']['time'] = bw_array_get( $_REQUEST, "time", null );
  bw_form();
  stag( "table", "wide-fat" );
  //$types = bw_list_registered_post_types();
  //bw_select( "type", "Time", null, array( "#options" => $types ) ); 
  //$taxonomies = bw_list_tags();
  // bw_select( "tag", "Tag", null, array( "#options" => $taxonomies )) ; 
  
  //bw_tablerow( array( "Tag", oik_batchmove_tag_select( "tag", "" ) ) ); 
  oik_batchmove_tag_select();
  bw_textfield( "time", 5, "time", stripslashes( $bw_bmt['args']['time'] ) );  
  etag( "table" );
  p( isubmit( "_oik_bmt_add_oik_bmt", "Add new tag to time", null, "button-primary" ) );
  etag( "form" );
}

/**
 * Edit the tag to time relationship
 */
function oik_bmt_edit_bmt( ) {
  global $bw_bmt;
  bw_form();
  stag( "table", "wide-fat" );
  
  bw_tablerow( array( "Relationship", $bw_bmt['args']['bmt'] . ihidden( 'bmt', $bw_bmt['args']['bmt']) ) );
  //bw_textfield( "bmt", 20, "Post bmt", $bw_bmt['args']['bmt'] );
  //bw_textfield( "type", 30, "Time", stripslashes( $bw_bmt['args']['type'] ) );
  //$field = esc_textarea( $bw_bmt['args']['field'] );
  //bw_trace2( $field, "esc_textarea field", false );
  bw_textfield( "tag", 100, "tag", stripslashes( $bw_bmt['args']['tag'] ) );
  //bw_checkbox( "hierarchical", "Hierarchical bmt?", $bw_bmt['args']["hierarchical"] );
  etag( "table" );
  p( isubmit( "_oik_bmt_edit_bmt", "Change relationship", null, "button-primary" ) );
  etag( "form" );
}

/**
 * View the tag to time relationship
 */
function oik_bmt_preview() {
  oik_require( "includes/oik-sc-help.inc" );
  $preview_bmt = bw_array_get( $_REQUEST, "preview_bmt", null );
  if ( $preview_bmt ) {
    sdiv( "oik_preview");
    //bw_invoke_bmt( $preview_bmt, null, "Preview of the $preview_bmt bmt" );
    p( "Preview not yet implemented" );
    ediv( "oik_preview");
  }
}



