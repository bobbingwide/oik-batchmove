<?php // (C) Copyright Bobbing Wide 2017-2020,2024

/**
 * @package oik-batchmove
 *
 * For these tests to work there have to be multiple categories, not just Uncategorised.
 * Post 1 must exist and have more than one category.
 *
 */
class Tests_oik_batchmove extends BW_UnitTestCase {

	function setUp() : void {
		parent::setUp();
		//oik_require( "includes/oik-filters.inc" );
		oik_require( "admin/oik-batchmove.php", "oik-batchmove" );
	}
	
	/**
	 * Create an uncategorized post
	 * Change it to the last category
	 * Change it back again
	 */
	function get_first_category() {
		$cat = $this->get_nth_category( 0 );
		return( $cat );
	}
	
	function get_nth_category( $n ) {
		$terms = get_categories();
		$term = current( $terms );
		for ( $i = 1; $i<= $n; $i++ ) {
			$term = next( $terms);
		}

		return $term->term_id ?? null;
		//print_r( $terms );
		//return $terms[ $n ]->term_id;
	}
	
	/**
	 * Test changing category from 1 (Uncategorized) to the first category
	 *
	 * Do we have to assume that it's already in Uncategorized
	 * Or can we remove it.
	 */
	function test_oik_batchmove_perform_update() {
		$first_cat = $this->get_first_category();
		$second_cat = $this->get_nth_category( 1 );
		//echo $first_cat;
		$_REQUEST['_batchmove_category_select'] = 1;
		$_REQUEST['_batchmove_category_apply'] = $first_cat; 
		oik_batchmove_perform_update( 1 );
    $categories = wp_get_post_categories( 1 );
		//print_r( $categories );
		$actual = current( $categories ); 
		$this->assertEquals( $first_cat, $actual  );
	}

	/**
	 * For this test to work post 1 has to already have more than one category.
	 */
	
	function test_oik_batchmove_perform_delete() {
		$categories = wp_get_post_categories( 1 );
		$deleted = $categories[0];
		oik_batchmove_perform_delete( 1, $deleted );
		$categories = wp_get_post_categories( 1 );
		$this->assertNotContains( $deleted, $categories );
	}
	
	function test_oik_batchmove_perform_add() {
		$categories = wp_get_post_categories( 1 );
		$deleted = $categories[0];
		oik_batchmove_perform_delete( 1, $deleted );
		$categories = wp_get_post_categories( 1 );
		$this->assertNotContains( $deleted, $categories );
		oik_batchmove_perform_add( 1, $deleted );
		$categories = wp_get_post_categories( 1 );
		$this->assertContains( $deleted, $categories );
	}
	

	
}
