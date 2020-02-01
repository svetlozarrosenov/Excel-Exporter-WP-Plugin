<?php
namespace App\Spreadsheet;

use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class XLSX {
	private $compatibilityObject;
	private $currentPostType;

	public function __construct( CarbonFieldsCompatibility $CarbonFields ) {
		$this->compatibilityObject = $CarbonFields;

		$this->currentPostType = $this->getCurrentPostType();
	}

	private function getCurrentPostType() {
		return ! empty( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';
	}

	public function generate( $request ) {

		if ( empty( $request ) || ! isset( $request['export'] ) ) {
			return $this;
		}

		unset( $request['export'] );
		unset( $request['crb_selected_posts'] );

		$fileName =  dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'data.xlsx';

		$posts_exports = $this->getSelectedExports( $request );

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

		$row = 1;
		$tableHead = [];
		foreach ( $posts_exports as $id => $post ) {
			$column = 1;
			$rowINcrementationBecauseOfArray = 0;
			foreach ( $post as $key => $export ) {
				$columnINcrementationBecauseOfArray = 0;

				if ( ! in_array( $export['name'], $tableHead ) ) {
					$tableHead[] = $export['name'];
					$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow( $column, $row, $export['name'] );			
				}

				if ( is_array( $export['value'] ) ) {
					$rowINcrementationBecauseOfArray = $this->getRowIncrementationVal( $rowINcrementationBecauseOfArray, $export['value'] );

					$spreadsheet->getActiveSheet()->fromArray( $export['value'], NULL, Coordinate::stringFromColumnIndex($column) . ($row+1) );
					$column++;
				} else {
					$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow( $column, ($row+1), $export['value'] );
				}

				$column++;
			}

			$row++;
			$row += $rowINcrementationBecauseOfArray;
		}

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

		$writer->save( $fileName );

		$this->file = $fileName;

		return $this;
	}

	public function getRowIncrementationVal( $val, $array ) {
		if ( $val < count( $array ) ) {
			$val = count( $array );
		}
		return $val;
	}

	public function getSelectedExports( $request ) {
		global $wpdb;
		$ids = [];
		$postIDs = $wpdb->get_results("SELECT ID FROM  $wpdb->posts WHERE post_type = '{$this->currentPostType}';");
		foreach ( $postIDs as $id ) {
			$ids[] = $id->ID;
		}

		if ( ! isset( $ids ) ) {
			return;
		}

		$postCommonSelectedExports = $this->compatibilityObject->getSelectedPostCommonExports( $request, $ids );

		$postMetaSelectedExports = $this->compatibilityObject->getSelectedPostMetaExports( $request, $ids );

		if ( empty( $postMetaSelectedExports ) ) {
			return $postCommonSelectedExports;
		}

		$exports = [];
		foreach ( $postCommonSelectedExports as $key => $export ) {
			if ( isset( $postCommonSelectedExports[ $key ] ) && isset( $postMetaSelectedExports[ $key ] ) ) {
				$exports[ $key ] = array_merge( $postCommonSelectedExports[ $key ], $postMetaSelectedExports[ $key ] );
			}
		}

		foreach ( $postCommonSelectedExports as $key => $export ) {
			if ( ! isset( $exports[ $key ] ) ) {
				$exports[ $key ] = $export;
			}
		}

		arsort( $exports );

		return $exports;
	}

	public function download() {
		if ( empty( $this->file ) ) {
			return false;
		}

		if ( file_exists( $this->file ) ) {
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="'. basename( $this->file ).'"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize( $this->file ));
		    readfile( $this->file );
		    exit;
		}
	}
}