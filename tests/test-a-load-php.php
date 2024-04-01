<?php

/**
 * @package oik-batchmove
 * @copyright (C) Copyright Bobbing Wide 2023
 *
 * Unit tests to load all the PHP files for PHP 8.2
 */
class Tests_load_php extends BW_UnitTestCase
{

	/**
	 * set up logic
	 *
	 * - ensure any database updates are rolled back
	 * - we need oik-googlemap to load the functions we're testing
	 */
	function setUp(): void 	{
		parent::setUp();
	}

	function test_load_admin_php() {
		oik_require( 'admin/oik-activation.php', 'oik-batchmove');
		oik_require( 'admin/oik-batchmove.php', 'oik-batchmove');
		oik_require( 'admin/oik-batchmove-categories.php', 'oik-batchmove');
		oik_require( 'admin/oik-batchmove-cron.php', 'oik-batchmove');
		oik_require( 'admin/oik-batchmove-tags.php', 'oik-batchmove');
		$this->assertTrue( true );
	}

	function test_load_plugin_php() {
		oik_require( 'oik-batchmove.php', 'oik-batchmove');
		$this->assertTrue( true );
	}
}