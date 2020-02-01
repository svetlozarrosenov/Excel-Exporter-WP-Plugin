<?php
namespace App\Spreadsheet;

class CarbonFieldsCompatibility implements Compatibility {
	public function getSelectedPostCommonExports( $request, $postIDs ) {
    	foreach ( $postIDs as $postID ) {
    		$args = [];
    		foreach ( $request as $key => $export ) {
    			if ( $key == 'ID' ) {
    				$args[$key] = [
						'name' => 'Post ID',
						'value' => $postID
					];
    			}

    			if ( $key == 'post_title' ) {
    				$args[$key] = [
						'name' => 'Post Title',
						'value' => get_the_title( $postID )
					];
    			}
    			
    			if ( $key == 'post_author' ) {
    				$args[$key] = [
						'name' => 'Post Author',
						'value' => $this->getAuthorMeta( 'display_name', $postID )
					];
    			}

    			if ( $key == 'post_date' ) {
    				$args[$key] = [
						'name' => 'Post Date',
						'value' => get_the_date( 'Y-m-d', $postID )
					];
    			}

    			if ( $key == 'post_content' ) {
    				$args[$key] = [
						'name' => 'Post Content',
						'value' => get_the_content( null, false, $postID )
					];
    			}

    			if ( $key == 'post_excerpt' ) {
    				$args[$key] = [
						'name' => 'Post Excerpt',
						'value' => get_the_excerpt( $postID )
					];
    			}

    			if ( $key == 'post_status' ) {
    				$args[$key] = [
						'name' => 'Post Status',
						'value' => get_post_status( $postID )
					];
    			}

    		}
    		
    		$postCommonTypes[ $postID ] = $args;
    	}
    	
		return $postCommonTypes;
	}
	public function getSelectedPostMetaExports( $request, $postIDs ) {
		$commonExports = $this->getPostCommonExports();
		$postMetaExports = [];
		foreach ( $postIDs as $postID ) {
			foreach ( $request as $key => $val ) {
				if ( array_key_exists( $key, $commonExports ) ) {
					continue;
				}
				if ( $this->isComplex( $key ) ) {
					$complexKey = $this->getComplexKey( $key );

					$value = carbon_get_post_meta( $postID, $complexKey );
					if ( ! empty( $value ) ) {
						$postMetaExports[ $postID ][ $complexKey ] = [
							'name' => $key,
							'value' => $value,						
						];
						
						$postMetaExports[ $postID ][ $complexKey ] = $this->prepareForXLSXImport( $postMetaExports[ $postID ][ $complexKey ] );

						if ( array_key_exists( $complexKey, $postMetaExports[ $postID ] ) ) {
							continue;
						}
					}
				}

				$value = get_post_meta( $postID, $key, true );

				if ( ! empty( $value ) ) {
					$postMetaExports[ $postID ][ $key ] = [
						'name' => $key,
						'value' => get_post_meta( $postID, $key, true ),
					
					];
				}
			}
		}
		return $postMetaExports;
	}

	public function prepareForXLSXImport( array $complex ) {
		$titles = [];
		$subtitles = [];
		foreach ( $complex['value'] as $index => &$array ) {
			unset( $complex['value'][ $index ]['_type'] );
			if ( $index == 0 ) {
				foreach ( $array as $key => $val ) {
					$subtitles[ $key ] = $key;
					$titles[] = $complex['name'];
				}
			}
		}

		array_unshift( $complex['value'], $subtitles );
		array_unshift( $complex['value'], $titles );

		return $complex;
	}

	public function isComplex( $value, $regex = "/(?P<meta_key>[\w]+)(--repeater)/" ) {
		return preg_match( $regex, $value, $match );
	}

	public function getComplexKey( $value, $regex="/(?P<meta_key>[\w]+)(--repeater)/" ) {
		preg_match( $regex, $value, $matches );
		if ( isset( $matches[ 'meta_key' ] ) ) {
			return $matches[ 'meta_key' ];
		} 
		return false;
	}

	public function isEmptyComplex( $value, $regex="/(_)([\w]+)(\|\|\|\d\|_empty)/" ) {
		return preg_match( $regex, $value, $matches );
	}

	public function getPostCommonExports() {
		return [
			'ID' => [
				'name' => 'Post ID',
			],
			'post_title' => [
				'name' => 'Post Title',
			],
			'post_author' => [
				'name' => 'Post Author',
			],
			'post_date' => [
				'name' => 'Post Date',
			],
			'post_content' => [
				'name' => 'Post Content',
			],
			'post_excerpt' => [
				'name' => 'Post Excerpt',
			],
			'post_status' => [
				'name' => 'Post Status',
			],
		];
	}

	public function getAuthorMeta( $field, $postID ) {
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM  $wpdb->posts as p INNER JOIN $wpdb->users as u on p.post_author = u.ID WHERE p.ID = '$postID'");
		if ( isset( $results[0] ) ) {
			return $results[0]->$field;
		}
		return false;
	}
}
