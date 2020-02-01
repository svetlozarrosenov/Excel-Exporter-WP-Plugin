<?php
/**
* @package ExcelSpreadsheet
*/
/*
Plugin Name: ExcelSpreadsheet 
Description: This plugin helps you to create excel spreadsheets easily.
Version 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class ExcelSpreadsheet {
	public function __construct() {
		add_action( 'wp_loaded', function() {
			require 'vendor/autoload.php';

			$container = new \App\Providers\Container( [
				\App\Spreadsheet\CarbonFieldsCompatibility::class => function() {
					return new \App\Spreadsheet\CarbonFieldsCompatibility();
				}
			] );

			$container->instantiateAnObjectThroughtContainer(\App\Spreadsheet\AdminPage::class);

			$XLSX = $container->instantiateAnObjectThroughtContainer(\App\Spreadsheet\XLSX::class);

			$XLSX->generate( $_POST )
				->download();
		} );
	}

	public function activate() {

	}

	public function deactivate() {

	}
}

$excelSpreadsheet = new ExcelSpreadsheet();

register_activation_hook( __FILE__, array( $excelSpreadsheet, 'activate' ) );

register_deactivation_hook( __FILE__, array( $excelSpreadsheet, 'deactivate' ) );

