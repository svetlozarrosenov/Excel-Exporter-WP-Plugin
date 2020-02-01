<?php
namespace App\Spreadsheet;

class AdminPage {
	private $compatibilityObject;
	private $currentPostType;

	private $pluginDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'excel-spreadsheet' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

	public function __construct( CarbonFieldsCompatibility $CarbonFields ) {
		$this->compatibilityObject = $CarbonFields;
		
		$this->currentPostType = ! empty( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';

		$registeredPostTypes = $this->getPostTypes();

		foreach ( $registeredPostTypes as $post_type ) {
			$page_name = "?post_type={$post_type}";
			if ( $post_type == 'post') {
				$page_name = '';
			}
			
			add_action( 'admin_menu', function() use ( $page_name ) {
				add_submenu_page(
				    "edit.php{$page_name}",
				    __( 'Export', 'crb_xlsx_export' ),
				    __( 'Export', 'crb_xlsx_export' ),
				    'manage_options',
				    'crb_xlsx_export',
				    array( $this, 'ExportPage' )
				);
			} );
		}
	}

	public function getPostTypes() {
		$postTypesForExcluding = [
			'attachment' => 'attachment',
			'revision' => 'revision',
			'nav_menu_item' => 'nav_menu_item',
			'custom_css' => 'custom_css',
			'customize_changeset' => 'customize_changeset',
			'oembed_cache' => 'oembed_cache',
			'user_request' => 'user_request',
			'wp_block' => 'wp_block',
		];

		$registeredPostTypes = \get_post_types();
		foreach ( $registeredPostTypes as $key => $post_type ) {
			if ( in_array( $post_type, $postTypesForExcluding ) ) {
				unset( $registeredPostTypes[ $key ] );
			}
		}

		return $registeredPostTypes;
	}

	public function ExportPage() {
		$this->render('posts', array( 'AdminPageInstance' => $this ) );

		wp_enqueue_script('crb-vue', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js', array(), '1.0.0', true);
		wp_enqueue_script('crb-main', plugins_url('../../resources/js/main.js', __FILE__), array(), '1.0.0', true);
		wp_enqueue_style('crb_xlsx_export_styles', plugins_url('../../resources/css/style.css', __FILE__) );
	}

	public function render( $view, $context = [] ) {
		extract( $context );

		require $this->pluginDir . $view . '.php';
	}

	public function getPostMetaKeys() {
		global $wpdb;

		$ids = [];

		$post_ids = $wpdb->get_results("SELECT ID FROM  $wpdb->posts WHERE post_type = '{$this->currentPostType}';");
		foreach ( $post_ids as $id ) {
			$ids[] = $id->ID;
		}
		$ids = implode( ', ', $ids  );

		$sql = "SELECT DISTINCT pm.meta_key FROM  $wpdb->posts as p INNER JOIN $wpdb->postmeta as pm on p.ID = pm.post_id WHERE post_type = '{$this->currentPostType}' || p.ID IN('$ids') GROUP BY pm.meta_key;";

		$metaKeys = $wpdb->get_results( $sql, OBJECT_K ); 
		
		foreach( $metaKeys as $key => &$val ) {
			if ( $this->compatibilityObject->isComplex( $key, "/(_)(?P<meta_key>[\w]+)((\|[a-z]+\|\d\|\d\|value)|(\|\|\|\d\|value))/" ) ) {
				$new_key = $this->compatibilityObject->getComplexKey( $key, "/(_)(?P<meta_key>[\w]+)((\|[a-z]+\|\d\|\d\|value)|(\|\|\|\d\|value))/" );
				unset( $metaKeys[ $key ] );
				$metaKeys[ $new_key . '--repeater' ] = $new_key;
			}
			if ( $this->compatibilityObject->isEmptyComplex( $key ) ) {
				unset( $metaKeys[ $key ] );
			}
		}

		unset( $metaKeys['_edit_last'] );
		unset( $metaKeys['_edit_lock'] );

		$postKeys = $this->compatibilityObject->getPostCommonExports();

		$metaKeys = array_merge( $postKeys, $metaKeys );

		return $metaKeys;
	}

	public function getCurrentPostType() {
		return $this->currentPostType;
	}
}

