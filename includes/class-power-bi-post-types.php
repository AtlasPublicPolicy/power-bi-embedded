<?php
/**
 * Handles displays and hooks for the Power BI custom post type.
 *
 * @package Power_Bi
 */
class Power_Bi_Post_Types {

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'cmb2_init', array( $this, 'power_bi_metaboxs' ) );
		add_filter( 'manage_powerbi_posts_columns', array( $this, 'set_custom_edit_powerbi_columns' ) );
		add_action( 'manage_powerbi_posts_custom_column', array( $this, 'custom_powerbi_column' ), 10, 2 );
	}

	/**
	 * Register a custom post type called "power_bi".
	 *
	 * @see get_post_type_labels() for label keys.
	 */
	public function register_post_types() {
		$labels = array(
			'name'                  => _x( 'Power BI Items', 'Post type general name', 'power-bi' ),
			'singular_name'         => _x( 'Power BI', 'Post type singular name', 'power-bi' ),
			'menu_name'             => _x( 'Power BI', 'Admin Menu text', 'power-bi' ),
			'name_admin_bar'        => _x( 'Power BI', 'Add New on Toolbar', 'power-bi' ),
			'add_new'               => __( 'Add New', 'power-bi' ),
			'add_new_item'          => __( 'Add New Power BI', 'power-bi' ),
			'new_item'              => __( 'New Power BI', 'power-bi' ),
			'edit_item'             => __( 'Edit Power BI', 'power-bi' ),
			'view_item'             => __( 'View Power BI', 'power-bi' ),
			'all_items'             => __( 'All Power BI Items', 'power-bi' ),
			'search_items'          => __( 'Search Power BI Items', 'power-bi' ),
			'parent_item_colon'     => __( 'Parent Power BI Items:', 'power-bi' ),
			'not_found'             => __( 'No Power BI found.', 'power-bi' ),
			'not_found_in_trash'    => __( 'No Power BI found in Trash.', 'power-bi' ),
			'featured_image'        => _x( 'Power BI Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'power-bi' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'power-bi' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'power-bi' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'power-bi' ),
			'archives'              => _x( 'Power BI archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'power-bi' ),
			'insert_into_item'      => _x( 'Insert into report', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'power-bi' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this report', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'power-bi' ),
			'filter_items_list'     => _x( 'Filter reports list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'power-bi' ),
			'items_list_navigation' => _x( 'Power BI Items list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'power-bi' ),
			'items_list'            => _x( 'Power BI Items list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'power-bi' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'powerbi' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
			'menu_icon'          => 'dashicons-chart-line',
			'taxonomies'         => array( 'category' ),
			'supports'           => array( 'title', 'thumbnail', 'excerpt')
		);

		register_post_type( 'powerbi', $args );
	}

	/**
	 * Register metabox
	 */
	public function power_bi_metaboxs() {
		// Start with an underscore to hide fields from custom fields list.
		$prefix = '_power_bi_';
		$languages = $this->get_languages();
		/**
		 * Sample metabox to demonstrate the different conditions you can set.
		 */
		$metabox_details = new_cmb2_box( array(
			'id'            => $prefix . 'details',
			'title'         => 'Embed Details',
			'object_types'  => array( 'powerbi' ), // Post type.
		) );

		$metabox_settings = new_cmb2_box( array(
			'id'            => $prefix . 'settings',
			'title'         => 'Settings',
			'object_types'  => array( 'powerbi' ), // Post type.
		) );

		$metabox_details->add_field( array(
			'name'             => 'Embed Type',
			'desc'             => 'Select the type of content to embed.',
			'id'               => $prefix . 'embed_type',
			'type'             => 'select',
			'default'          => 'report',
			'options'          => array(
				'report' => __( 'Report', 'power-bi' ),
				'visual'   => __( 'Report Visual', 'power-bi' ),
				'qna'   => __( 'Q&A', 'power-bi' ),
				'dashboard'   => __( 'Dashboard', 'power-bi' ),
				'tile'     => __( 'Tile', 'power-bi' ),
			),
		) );

		$metabox_details->add_field( array(
			'name'             => 'Report Mode',
			'desc'             => 'Select how the element should be embedded.',
			'id'               => $prefix . 'report_mode',
			'type'             => 'select',
			'default'          => 'view',
			'options'          => array(
				'view' => __( 'View Mode', 'power-bi' ),
				'edit'   => __( 'Edit Mode', 'power-bi' ),
				'create'   => __( 'Create Mode', 'power-bi' ),
			),
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'report' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'             => 'Q&A Mode',
			'desc'             => 'Select the mode of the Q&A.',
			'id'               => $prefix . 'qna_mode',
			'type'             => 'radio',
			'default'          => 'show_qna',
			'options'          => array(
				'show_qna' => __( 'Show Q&A', 'power-bi' ),
				'show_qna_predefined'   => __( 'Show Q&A with predefined question', 'power-bi' ),
				'show_a_predefined'   => __( 'Show answer only with predefined question', 'power-bi' ),
			),
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'qna' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Q&A Input Question',
			'desc'    => 'Enter input question.',
			'id'      => $prefix . 'input_question',
			'default' => 'This year sales by store type by postal code as map',
			'type'    => 'text',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'qna_mode',
				'data-conditional-value' => wp_json_encode( array( 'show_qna_predefined', 'show_a_predefined' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Dashboard Id',
			'desc'    => 'Enter the unique identifier for the dashboard. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL.',
			'id'      => $prefix . 'dashboard_id',
			'type'    => 'text',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'dashboard', 'tile' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Report Id',
			'desc'    => 'Enter the unique identifier for the report. You can find the identifier by viewing a report in the Power BI Service. The identifier is in the URL.',
			'id'      => $prefix . 'report_id',
			'type'    => 'text',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'report', 'visual' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Group Id',
			'desc'    => 'Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.',
			'id'      => $prefix . 'group_id',
			'type'    => 'text',
		) );

		$metabox_details->add_field( array(
			'name'    => 'Dataset Id',
			'desc'    => 'Enter the unique identifier for the dataset. This is only needed for Create mode. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL.',
			'id'      => $prefix . 'dataset_id',
			'type'    => 'text',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'report', 'qna' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Tile Id',
			'desc'    => 'Enter the unique identifier for the dashboard tile. You can find the identifier by entering the focus mode for a tile when viewing a dashboard in the Power BI Service. The identifier is in the URL.',
			'id'      => $prefix . 'tile_id',
			'type'    => 'text',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'tile' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Page Name',
			'desc'    => 'Enter the unique identifier for the Page. You can find the identifier by viewing the page within a report in the Power BI Service. The identifier is in the URL.',
			'id'      => $prefix . 'page_name',
			'type'    => 'text',
			'default' => 'ReportSection',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'report', 'visual' ) ),
			),
		) );

		$metabox_details->add_field( array(
			'name'    => 'Visual Name',
			'desc'    => 'The Visual Name can be retrieved using the GetVisuals method on the Page object.',
			'id'      => $prefix . 'visual_name',
			'type'    => 'text',
			'default' => 'VisualContainer1',
			'attributes' => array(
				'data-conditional-id'    => $prefix . 'embed_type',
				'data-conditional-value' => wp_json_encode( array( 'visual' ) ),
			),
		) );

		$metabox_settings->add_field( array(
			'name' => 'Filter Pane',
			'desc' => 'Enable the filter pane.',
			'id'   => $prefix . 'filter_pane',
			'default' => false,
			'type' => 'checkbox',
		) );

		$metabox_settings->add_field( array(
			'name' => 'Page Navigation',
			'desc' => 'Enable report page navigation.',
			'id'   => $prefix . 'page_navigation',
			'default' => false,
			'type' => 'checkbox',
		) );

		$metabox_details->add_field( array(
			'name'    => 'Background color',
			'desc'    => 'Optionally enter a background for your embed. For transparent, use models.BackgroundType.Transparent.',
			'id'      => $prefix . 'background',
			'type'    => 'text',
			'default' => '',
		) );

		$metabox_settings->add_field( array(
			'name'             => 'Language',
			'desc'             => 'Select a language.',
			'id'               => $prefix . 'language',
			'type'             => 'select',
			'default'          => 'en',
			'options'          => $languages,
		) );

		$metabox_settings->add_field( array(
			'name'             => 'Format Local',
			'desc'             => 'Select your language.',
			'id'               => $prefix . 'format_local',
			'type'             => 'select',
			'default'          => 'en',
			'options'          => $languages,
		) );

		$metabox_settings->add_field( array(
			'name'    => 'Width',
			'desc'    => 'Enter width in pixels or percent (include %).',
			'id'      => $prefix . 'width',
			'default' => '100%',
			'type'    => 'text',
		) );

		$metabox_settings->add_field( array(
			'name'    => 'Height',
			'desc'    => 'Enter height in pixels or percent (include %).',
			'id'      => $prefix . 'height',
			'default' => '350px',
			'type'    => 'text',
		) );
	}

	public function set_custom_edit_powerbi_columns($columns) {
		$columns['type']      = __( 'Type', 'power-bi' );
		$columns['shortcode'] = __( 'Shortcode', 'power-bi' );

		return $columns;
	}

	public function custom_powerbi_column( $column, $post_id ) {
		switch ( $column ) {

			case 'type':
				$type = get_post_meta( $post_id, '_power_bi_embed_type', true );
				if ( ! empty( $type ) ) {
					echo $type;
				} else {
					_e( 'Unable to get type', 'power-bi' );
				}

				break;

			case 'shortcode':
				$container_width = get_post_meta( $post_id, '_power_bi_width', true );
				$container_height = get_post_meta( $post_id, '_power_bi_height', true );
				$param_width = empty( $container_width ) ? '' : ' width="' . $container_width . '"';
				$param_height = empty( $container_height ) ? '' : ' height="' . $container_height . '"';

				echo '[powerbi id="' . $post_id . '"'. $param_width . $param_height .']';
				break;

		}
	}

	public function get_languages() {
		$languages = [
		    'ab' => 'Abkhazian',
		    'aa' => 'Afar',
		    'af' => 'Afrikaans',
		    'ak' => 'Akan',
		    'sq' => 'Albanian',
		    'am' => 'Amharic',
		    'ar' => 'Arabic',
		    'an' => 'Aragonese',
		    'hy' => 'Armenian',
		    'as' => 'Assamese',
		    'av' => 'Avaric',
		    'ae' => 'Avestan',
		    'ay' => 'Aymara',
		    'az' => 'Azerbaijani',
		    'bm' => 'Bambara',
		    'ba' => 'Bashkir',
		    'eu' => 'Basque',
		    'be' => 'Belarusian',
		    'bn' => 'Bengali',
		    'bh' => 'Bihari languages',
		    'bi' => 'Bislama',
		    'bs' => 'Bosnian',
		    'br' => 'Breton',
		    'bg' => 'Bulgarian',
		    'my' => 'Burmese',
		    'ca' => 'Catalan, Valencian',
		    'km' => 'Central Khmer',
		    'ch' => 'Chamorro',
		    'ce' => 'Chechen',
		    'ny' => 'Chichewa, Chewa, Nyanja',
		    'zh' => 'Chinese',
		    'cu' => 'Church Slavonic, Old Bulgarian, Old Church Slavonic',
		    'cv' => 'Chuvash',
		    'kw' => 'Cornish',
		    'co' => 'Corsican',
		    'cr' => 'Cree',
		    'hr' => 'Croatian',
		    'cs' => 'Czech',
		    'da' => 'Danish',
		    'dv' => 'Divehi, Dhivehi, Maldivian',
		    'nl' => 'Dutch, Flemish',
		    'dz' => 'Dzongkha',
		    'en' => 'English',
		    'eo' => 'Esperanto',
		    'et' => 'Estonian',
		    'ee' => 'Ewe',
		    'fo' => 'Faroese',
		    'fj' => 'Fijian',
		    'fi' => 'Finnish',
		    'fr' => 'French',
		    'ff' => 'Fulah',
		    'gd' => 'Gaelic, Scottish Gaelic',
		    'gl' => 'Galician',
		    'lg' => 'Ganda',
		    'ka' => 'Georgian',
		    'de' => 'German',
		    'ki' => 'Gikuyu, Kikuyu',
		    'el' => 'Greek (Modern)',
		    'kl' => 'Greenlandic, Kalaallisut',
		    'gn' => 'Guarani',
		    'gu' => 'Gujarati',
		    'ht' => 'Haitian, Haitian Creole',
		    'ha' => 'Hausa',
		    'he' => 'Hebrew',
		    'hz' => 'Herero',
		    'hi' => 'Hindi',
		    'ho' => 'Hiri Motu',
		    'hu' => 'Hungarian',
		    'is' => 'Icelandic',
		    'io' => 'Ido',
		    'ig' => 'Igbo',
		    'id' => 'Indonesian',
		    'ia' => 'Interlingua (International Auxiliary Language Association)',
		    'ie' => 'Interlingue',
		    'iu' => 'Inuktitut',
		    'ik' => 'Inupiaq',
		    'ga' => 'Irish',
		    'it' => 'Italian',
		    'ja' => 'Japanese',
		    'jv' => 'Javanese',
		    'kn' => 'Kannada',
		    'kr' => 'Kanuri',
		    'ks' => 'Kashmiri',
		    'kk' => 'Kazakh',
		    'rw' => 'Kinyarwanda',
		    'kv' => 'Komi',
		    'kg' => 'Kongo',
		    'ko' => 'Korean',
		    'kj' => 'Kwanyama, Kuanyama',
		    'ku' => 'Kurdish',
		    'ky' => 'Kyrgyz',
		    'lo' => 'Lao',
		    'la' => 'Latin',
		    'lv' => 'Latvian',
		    'lb' => 'Letzeburgesch, Luxembourgish',
		    'li' => 'Limburgish, Limburgan, Limburger',
		    'ln' => 'Lingala',
		    'lt' => 'Lithuanian',
		    'lu' => 'Luba-Katanga',
		    'mk' => 'Macedonian',
		    'mg' => 'Malagasy',
		    'ms' => 'Malay',
		    'ml' => 'Malayalam',
		    'mt' => 'Maltese',
		    'gv' => 'Manx',
		    'mi' => 'Maori',
		    'mr' => 'Marathi',
		    'mh' => 'Marshallese',
		    'ro' => 'Moldovan, Moldavian, Romanian',
		    'mn' => 'Mongolian',
		    'na' => 'Nauru',
		    'nv' => 'Navajo, Navaho',
		    'nd' => 'Northern Ndebele',
		    'ng' => 'Ndonga',
		    'ne' => 'Nepali',
		    'se' => 'Northern Sami',
		    'no' => 'Norwegian',
		    'nb' => 'Norwegian Bokmål',
		    'nn' => 'Norwegian Nynorsk',
		    'ii' => 'Nuosu, Sichuan Yi',
		    'oc' => 'Occitan (post 1500)',
		    'oj' => 'Ojibwa',
		    'or' => 'Oriya',
		    'om' => 'Oromo',
		    'os' => 'Ossetian, Ossetic',
		    'pi' => 'Pali',
		    'pa' => 'Panjabi, Punjabi',
		    'ps' => 'Pashto, Pushto',
		    'fa' => 'Persian',
		    'pl' => 'Polish',
		    'pt' => 'Portuguese',
		    'qu' => 'Quechua',
		    'rm' => 'Romansh',
		    'rn' => 'Rundi',
		    'ru' => 'Russian',
		    'sm' => 'Samoan',
		    'sg' => 'Sango',
		    'sa' => 'Sanskrit',
		    'sc' => 'Sardinian',
		    'sr' => 'Serbian',
		    'sn' => 'Shona',
		    'sd' => 'Sindhi',
		    'si' => 'Sinhala, Sinhalese',
		    'sk' => 'Slovak',
		    'sl' => 'Slovenian',
		    'so' => 'Somali',
		    'st' => 'Sotho, Southern',
		    'nr' => 'South Ndebele',
		    'es' => 'Spanish, Castilian',
		    'su' => 'Sundanese',
		    'sw' => 'Swahili',
		    'ss' => 'Swati',
		    'sv' => 'Swedish',
		    'tl' => 'Tagalog',
		    'ty' => 'Tahitian',
		    'tg' => 'Tajik',
		    'ta' => 'Tamil',
		    'tt' => 'Tatar',
		    'te' => 'Telugu',
		    'th' => 'Thai',
		    'bo' => 'Tibetan',
		    'ti' => 'Tigrinya',
		    'to' => 'Tonga (Tonga Islands)',
		    'ts' => 'Tsonga',
		    'tn' => 'Tswana',
		    'tr' => 'Turkish',
		    'tk' => 'Turkmen',
		    'tw' => 'Twi',
		    'ug' => 'Uighur, Uyghur',
		    'uk' => 'Ukrainian',
		    'ur' => 'Urdu',
		    'uz' => 'Uzbek',
		    've' => 'Venda',
		    'vi' => 'Vietnamese',
		    'vo' => 'Volap_k',
		    'wa' => 'Walloon',
		    'cy' => 'Welsh',
		    'fy' => 'Western Frisian',
		    'wo' => 'Wolof',
		    'xh' => 'Xhosa',
		    'yi' => 'Yiddish',
		    'yo' => 'Yoruba',
		    'za' => 'Zhuang, Chuang',
		    'zu' => 'Zulu'
		];

		return $languages;
	}
}

Power_Bi_Post_Types::get_instance();
