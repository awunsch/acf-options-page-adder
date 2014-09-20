<?php 
	
	// If this file is called directly, abort.
	if (!defined('WPINC')) {die;}
	
	new acfOptionsPagefieldsetDuplicator();
	
	class acfOptionsPagefieldsetDuplicator {
		
		private $post_type = 'acf-opt-grp-dup';
		private $text_domain;
		
		public function __construct() {
			add_action('admin_notices', array($this, 'admin_message'));
			add_filter('manage_edit-'.$this->post_type.'_columns', array($this, 'admin_columns'));
			add_action('manage_'.$this->post_type.'_posts_custom_column', array($this, 'admin_columns_content'), 10, 2);
			
			add_action('acf_options_page/init', array($this, 'init'));
			add_action('acf_options_page/load_text_domain', array($this, 'load_text_domain'));
			
			add_filter('acf/location/rule_values/post_type', array($this, 'acf_location_rules_values_post_type'));
			add_filter('acf/location/rule_match/post_type', array($this, 'acf_location_rules_match_none'), 10, 3);
			add_filter('acf/load_field/name=_acf_field_grp_dup_group', array($this, 'load_acf_field_grp_dup_group'));
			add_filter('acf/load_field/name=_acf_field_grp_dup_page', array($this, 'load_acf_field_grp_dup_page'));
			add_action('acf/include_fields', array($this, 'acf_include_fields'));
		} // end public function __construct
		
		public function init() {
			$this->register_post_type();
		} // end public funtion init
		
		public function acf_include_fields() {
			$text_domain = $this->text_domain;
			$field_group = array(
				'key' => 'group_acf_opt_grp_dup',
				'title' => __('Duplicator Settings', $text_domain),
				'fields' => array(
					array(
						'key' => 'field_acf_field_grp_dup_desc',
						'label' => __('Description', $text_domain),
						'name' => '_acf_field_grp_dup_desc',
						'prefix' => '',
						'type' => 'textarea',
						'instructions' => __('Enter a description for your duplicator. This description will be shown on the admin page to remind you and others why it was created or what it does.', $text_domain),
						'required' => 0,
						'conditional_logic' => 0,
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => '',
						'new_lines' => 'wpautop',
						'readonly' => 0,
						'disabled' => 0,
					),
					array(
						'key' => 'field_acf_field_grp_dup_method',
						'label' => __('What do you want to duplicate?', $text_domain),
						'name' => '_acf_field_grp_dup_method',
						'prefix' => '',
						'type' => 'radio',
						'instructions' => __('<em>Please note that this will not allow you to copy multiple field groups to the same options page multiple times. You will need to create another duplicator to accomplish this.</em>', $text_domain),
						'required' => 1,
						'conditional_logic' => 0,
						'choices' => array(
							'copy' => __('Duplicate a Field Group to Multiple Options Pages', $text_domain),
							'multiply' => __('Duplicate a Field Group to the Same Options Page Multiple Times', $text_domain),
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => 'copy',
						'layout' => 'vertical',
					),
					array(
						'key' => 'field_acf_field_grp_dup_tabs',
						'label' => __('Tabs?', $text_domain),
						'name' => '_acf_field_grp_dup_tabs',
						'prefix' => '',
						'type' => 'radio',
						'instructions' => __('Do you want to put the copies into tabs?<br />Selecting yes will add all of the duplicates to a single field group and each duplicate will be in its own tab.<br /><em>This could have unexpected results if your field groups already contain tab fields.</em>', $text_domain),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_acf_field_grp_dup_method',
									'operator' => '==',
									'value' => 'multiply',
								),
							),
						),
						'choices' => array(
							1 => __('Yes', $text_domain),
							0 => __('No', $text_domain),
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => 0,
						'layout' => 'horizontal',
					),
					array(
						'key' => 'field_acf_field_grp_dup_title',
						'label' => __('Field Group Title', $text_domain),
						'name' => '_acf_field_grp_dup_title',
						'prefix' => '',
						'type' => 'text',
						'instructions' => __('Enter the Title for the new compound tabbed group that will be created. It this is left blank then the title of the duplicated field group will be used.', $text_domain),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_acf_field_grp_dup_method',
									'operator' => '==',
									'value' => 'multiply',
								),
								array(
									'field' => 'field_acf_field_grp_dup_tabs',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array(
						'key' => 'field_acf_field_grp_dup_group',
						'label' => __('Field Group to Duplicate', $text_domain),
						'name' => '_acf_field_grp_dup_group',
						'prefix' => '',
						'type' => 'select',
						'instructions' => __('Select the field group that you want to duplicate.', $text_domain),
						'required' => 0,
						'conditional_logic' => 0,
						'choices' => array(), // dynamically generated
						'default_value' => array(
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
					array(
						'key' => 'field_acf_field_grp_dup_pages',
						'label' => 'Apply to Options Pages',
						'name' => '_acf_field_grp_dup_pages',
						'prefix' => '',
						'type' => 'repeater',
						'instructions' => __('Select the options pages that the duplicated field group should be applied.<br />&nbsp;<br /><strong>New Field Name: </strong>When getting field values you must use the prefix you set here along with the field name set in the field group. For example if your field name is <strong>"my_field"</strong> and your prefix is <strong>"my_prefix_"</strong> then you would use the field name of <strong>"my_prefix_my_field"</strong> when getting the value.<br />&nbsp;<br /><strong>New Field Key: </strong>In order to create unique fields for each field group the ACF "key" value of each field must also be generated. The field key will be the same as the field name described above with the additional prefix of <strong>"field_key_"</strong>. Using the above example the field key for the field would be <strong>"field_key_my_prefix_my_field"</strong>.', $text_domain),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_acf_field_grp_dup_method',
									'operator' => '==',
									'value' => 'copy',
								),
							),
						),
						'min' => 2,
						'max' => '',
						'layout' => 'table',
						'button_label' => __('Add Options Page', $text_domain),
						'sub_fields' => array(
							array(
								'key' => 'field_acf_field_grp_dup_pages_sub_title',
								'label' => __('Field Group Title', $text_domain),
								'name' => '_acf_field_grp_dup_title',
								'prefix' => '',
								'type' => 'text',
								'instructions' => __('Use a different title for the field group on this options page.<br />If you do not specify a title for the field group it will default to the original field group title.', $text_domain),
								'required' => 0,
								'conditional_logic' => 0,
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array(
								'key' => 'field_acf_field_grp_dup_pages_sub_page',
								'label' => __('Options Page', $text_domain),
								'name' => '_acf_field_grp_dup_page',
								'prefix' => '',
								'type' => 'select',
								'instructions' => __('Select the options page to duplicate the field groups to.', $text_domain),
								'required' => 0,
								'conditional_logic' => 0,
								'column_width' => '',
								'choices' => array(), // will be dynamically generated
 								'default_value' => array(
								),
								'allow_null' => 0,
								'multiple' => 0,
								'ui' => 0,
								'ajax' => 0,
								'placeholder' => '',
								'disabled' => 0,
								'readonly' => 0,
							),
							array(
								'key' => 'field_acf_field_grp_dup_pages_sub_prefix',
								'label' => __('Field Name Prefix', $text_domain),
								'name' => '_acf_field_grp_dup_prefix',
								'prefix' => '',
								'type' => 'text',
								'instructions' => __('Enter the prefix to apply to all fields names in the field group. You must supply a unique prefix for each duplication.', $text_domain),
								'required' => 1,
								'conditional_logic' => 0,
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
						),
					),
					array(
						'key' => 'field_acf_field_grp_dup_page',
						'label' => __('Apply to Options Page', $text_domain),
						'name' => '_acf_field_grp_dup_page',
						'prefix' => '',
						'type' => 'select',
						'instructions' => __('Select the options page that this field group will be duplicated to.', $text_domain),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_acf_field_grp_dup_method',
									'operator' => '==',
									'value' => 'multiply',
								),
							),
						),
						'choices' => array(), // will be dynamically generated
						'default_value' => array(
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
					array(
						'key' => 'field_acf_field_grp_dups',
						'label' => __('Duplicates', $text_domain),
						'name' => '_acf_field_grp_dups',
						'prefix' => '',
						'type' => 'repeater',
						'instructions' => __('Set the values to be used for each duplication of the field group on this page.', $text_domain),
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_acf_field_grp_dup_method',
									'operator' => '==',
									'value' => 'multiply',
								),
							),
						),
						'min' => 2,
						'max' => '',
						'layout' => 'table',
						'button_label' => __('Add Duplicate', $text_domain),
						'sub_fields' => array(
							array(
								'key' => 'field_acf_field_grp_dups_sub_title_1',
								'label' => __('Field Group Title', $text_domain),
								'name' => '_acf_field_grp_dup_title',
								'prefix' => '',
								'type' => 'text',
								'instructions' => __('Enter the field group	title to use for this duplicate.<br />If you do not supply a title then the title of the field group will be used.<br />Having the same field group title used multiple times could be confusing to the user.', $text_domain),
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_acf_field_grp_dup_method',
											'operator' => '==',
											'value' => 'multiply',
										),
										array(
											'field' => 'field_acf_field_grp_dup_tabs',
											'operator' => '==',
											'value' => '0',
										),
									),
								),
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array(
								'key' => 'field_acf_field_grp_dups_sub_title_2',
								'label' => __('Tab Label', $text_domain),
								'name' => '_acf_field_grp_dup_title',
								'prefix' => '',
								'type' => 'text',
								'instructions' => __('Enter the tab label to use for this duplicate.<br />If no value is given then the labels<strong>"Tab 1"</strong>, <strong>"Tab 2"</strong>, <strong>"Tab 3"</strong>, etc, will be used.', $text_domain),
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_acf_field_grp_dup_method',
											'operator' => '==',
											'value' => 'multiply',
										),
										array(
											'field' => 'field_acf_field_grp_dup_tabs',
											'operator' => '==',
											'value' => '1',
										),
									),
								),
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array(
								'key' => 'field_acf_field_grp_dups_sub_prefix',
								'label' => __('Field Name Prefix', $text_domain),
								'name' => '_acf_field_grp_dup_prefix',
								'prefix' => '',
								'type' => 'text',
								'instructions' => __('Enter the prefix to apply to all fields names in the field group. You must supply a unique prefix for each duplication.', $text_domain),
								'required' => 1,
								'conditional_logic' => 0,
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'acf-opt-grp-dup',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'acf_after_title',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => array(
					0 => 'permalink',
					1 => 'the_content',
					2 => 'excerpt',
					3 => 'custom_fields',
					4 => 'discussion',
					5 => 'comments',
					6 => 'slug',
					7 => 'author',
					8 => 'format',
					9 => 'page_attributes',
					10 => 'featured_image',
					11 => 'categories',
					12 => 'tags',
					13 => 'send-trackbacks',
				),);
			register_field_group($field_group);
		} // end public function acf_include_fields
		
		public function admin_columns($columns) {
			$new_columns = array();
			foreach ($columns as $index => $column) {
				if ($index == 'title') {
					$new_columns[$index] = $column;
					$new_columns['description'] = __('Description', $this->text_domain);
					$new_columns['method'] = __('Duplication Type', $this->text_domain);
					$new_columns['field_group'] = __('Field Group', $this->text_domain);
					$new_columns['options_pages'] = __('Options Page(s)', $this->text_domain);
					$new_columns['field_prefixes'] = __('Field Prefix(es)', $this->text_domain);
				} else {
					if (strtolower($column) != 'date') {
						$new_columns[$index] = $column;
					}
				}
			}
			return $new_columns;
		} // end public function admin_columns
		
		public function admin_columns_content($column_name, $post_id) {
			switch ($column_name) {
				case 'description':
					echo get_post_meta($post_id, '_acf_field_grp_dup_desc', true);
					break;
				case 'method':
					$method = get_post_meta($post_id, '_acf_field_grp_dup_method', true);
					if ($method == 'copy') {
						_e('Duplicate a Field Group to Multiple Options Pages', $this->text_domain);
					} elseif ($method == 'multiply') {
						_e('Duplicate a Field Group to the Same Options Page Multiple Times', $this->text_domain);
					}
					break;
				case 'field_group':
					$field_group_id = intval(get_post_meta($post_id, '_acf_field_grp_dup_group', true));
					echo get_the_title($field_group_id);
					break;
				case 'options_pages':
					$options_pages = array();
					$method = get_post_meta($post_id, '_acf_field_grp_dup_method', true);
					if ($method == 'copy') {
						$pages = intval(get_post_meta($post_id, '_acf_field_grp_dup_pages', true));
						for ($i=0; $i<$pages; $i++) {
							$key = '_acf_field_grp_dup_pages_'.$i.'__acf_field_grp_dup_page';
							$options_pages[] = get_post_meta($post_id, $key, true);
						}
					} elseif ($method == 'multiply') {
						$options_pages[] = get_post_meta($post_id, '_acf_field_grp_dup_page', true);
					}
					if (count($options_pages)) {
						$list = '';
						global $acf_options_pages;
						foreach ($options_pages as $page) {
							if (isset($acf_options_pages[$page])) {
								if ($list != '') {
									$list .= '<br />';
								}
								$list .= $acf_options_pages[$page]['page_title'];
							}
						}
						echo $list;
					}
					break;
				case 'field_prefixes':
					$method = get_post_meta($post_id, '_acf_field_grp_dup_method', true);
					if ($method == 'copy') {
						$repeater = '_acf_field_grp_dup_pages';
					} elseif ($method == 'multiply') {
						$repeater = '_acf_field_grp_dups';
					}
					$prefixes = intval(get_post_meta($post_id, $repeater, true));
					$list = '';
					for ($i=0; $i<$prefixes; $i++) {
						if ($list != '') {
							$list .= '<br />';
						}
						$key = $repeater.'_'.$i.'__acf_field_grp_dup_prefix';
						$list .= get_post_meta($post_id, $key, true);
					}
					echo $list;
					break;
				default:
					// do nothing
					break;
			} // end switch
		} // end public function admin_columns_content
		
		public function load_acf_field_grp_dup_group($field) {
			// doing query posts so that this only shows field groups
			// created in the ACF editor and not field groups create with code
			$choices = array();
			$args = array('post_type' => 'acf-field-group',
										'status' => 'publish',
										'posts_per_page' => -1);
			$query = new WP_Query($args);
			if (count($query->posts)) {
				foreach ($query->posts as $post) {
					$choices[$post->ID] = $post->post_title;
				}
			}
			$field['choices'] = $choices;
			return $field;
		} // end public function load_acf_field_grp_dup_group
		
		public function load_acf_field_grp_dup_page($field) {
			global $acf_options_pages;
			$choices = array();
			if (count($acf_options_pages)) {
				foreach ($acf_options_pages as $key => $page) {
					if ((!isset($page['redirect']) || $page['redirect']) && !$page['parent_slug']) {
						continue;
					}
					$choices[$key] = $page['page_title'];
				}
			}
			$field['choices'] = $choices;
			return $field;
		} // end public function load_acf_field_grp_dup_page
		
		public function acf_location_rules_match_none($match, $rule, $options) {
			$match = -1;
			return $match;
		} // end public function acf_location_rules_match_none
		
		public function acf_location_rules_values_post_type($choices) {
			if (!isset($choices['none'])) {
				$choices['none'] = 'None (hidden)';
			}
			return $choices;
		} // end public function acf_location_rules_values_user
		
		public function admin_message() {
			// updated below-h2
			$screen = get_current_screen();
			if ($screen->id != 'edit-acf-opt-grp-dup') {
				return;
			}
			?>
				<div class="updated">
					<p>
						<strong>
							<?php _e('Options Page Field Group Duplicators allow the use of the same ACF field group on multiple options pages or to duplicate an ACF field group multiple times to the same options page.<br />The duplication process automatically adds a prefix to all duplicated fields that you specify so that you do not need to duplicate a field group and manually modify each field name.<br />In addition the option &quot;None (hidden)&quot; has been added to the Post Type Location Rules in ACF so that you can create field groups that do not normally appear anywhere.', $this->text_domain); ?>
						</strong>
					</p>
				</div>
			<?php 
		} // end public function admin_message
		
		public function load_text_domain() {
			$this->text_domain = apply_filters('acf_options_page/text_domain', false);
		} // end public function load_text_domain
		
		private function register_post_type() {
			$options_page_post_type = apply_filters('acf_options_page/post_type', false);
			$text_domain = $this->text_domain;
			if ($options_page_post_type === false || $text_domain === false) {
				return;
			}
      $args = array('label' => __('Field Group Duplicators', $text_domain),
										'singular_label' => __('Field Group Duplicator', $text_domain),
                    'description' => '',
                    'public' => false,
										'has_archive' => false,
                    'show_ui' => true,
                    'show_in_menu' => 'edit.php?post_type='.$options_page_post_type,
                    'capability_type' => 'post',
                    'map_meta_cap' => true,
                    'hierarchical' => false,
                    'rewrite' => array('slug' => $this->post_type, 'with_front' => true),
                    'query_var' => $this->post_type,
                    'exclude_from_search' => true,
                    'menu_position' => false,
										//'menu_icon' => 'dashicons-admin-generic',
                    'supports' => array('title','custom-fields','revisions'),
                    'labels' => array('name' => __('Options Page Field Group Duplicators', $text_domain),
                                      'singular_name' => __('Field Group Duplicator', $text_domain),
                                      'menu_name' =>  __('Field Group Duplicators', $text_domain),
                                      'add_new' => __('Add Field Group Duplicator', $text_domain),
                                      'add_new_item' => __('Add New Field Group Duplicator', $text_domain),
                                      'edit' => __('Edit', $text_domain),
                                      'edit_item' => __('Edit Field Group Duplicator', $text_domain),
                                      'new_item' => __('New Field Group Duplicator', $text_domain),
                                      'view' => __('View Field Group Duplicator', $text_domain),
                                      'view_item' => __('View Field Group Duplicator', $text_domain),
                                      'search_items' => __('Search Field Group Duplicators', $text_domain),
                                      'not_found' => __('No Field Group Duplicators Found', $text_domain),
                                      'not_found_in_trash' => __('No Field Group Duplicators Found in Trash', $text_domain),
                                      'parent' => __('Parent Field Group Duplicators', $text_domain)));
			$post_type = register_post_type($this->post_type, $args);
		} // end private function register_post_type
		
	} // end class acfOptionsPagefieldsetDuplicator
	
?>