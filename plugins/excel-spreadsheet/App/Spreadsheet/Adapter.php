<?php
namespace App\Spreadsheet;

interface Adapter {
	public function prepareForXLSXImport( array $complex );

	public function getSelectedPostCommonExports( $request, $postIDs );

	public function getSelectedPostMetaExports( $request, $postIDs );
}
