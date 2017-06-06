<?php
/*
 * Plugin Name:       Lablab Builder Slideshow Module
 * Plugin URI:        https://github.com/team-ok/lablab-builder
 * Description:       Adds a slideshow module to lablab builder.
 * Version:           1.0.0
 * Author:            Timo Klemm
 * Author URI:        https://github.com/team-ok
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lablab-slideshow
 * Domain Path:       /languages
 */

if ( class_exists( 'Lablab_Module_Builder' ) ):

	class Lablab_Builder_Slideshow_Module extends Lablab_Module_Builder {

		public function __construct(){

			// The module title.
			$this->title = 'Slideshow';

			// The acf field key of the module.
			$this->key = 'field_lablab_slideshow';

			// The acf field name of the module.
			$this->name = 'lablab-slideshow';

			// The current version of the module.
			$this->version = '1.0';

			// The module-specific acf fields of the module.
			$this->fields = plugin_dir_path( __FILE__ ) . 'fields/';

			// The absolute path to a partial template file that both retrieves content data and prints the output.
			$this->template_path = plugin_dir_path( __FILE__ ) . 'template/lablab-slideshow.php';

			// The javascript file(s) to be included only on admin pages.
			$this->admin_js = plugin_dir_url( __FILE__ ) . 'js/lablab-slideshow-admin.js';

			// The css file(s) to be included only on admin pages.
			$this->admin_css = plugin_dir_url( __FILE__ ) . 'css/lablab-slideshow-admin.css';

			// The uikit core components to be included.
			$this->uikit_core = array('overlay', 'cover');

			// The uikit add-on components to be included.
			$this->uikit_addons = array('slideshow');

			// The less fragment(s) to be added to the beans uikit less compiler.
			$this->less_fragments = plugin_dir_path( __FILE__ ) . 'less';

			// The text domain used for translation
			$this->text_domain = $this->name;

			// Relative path to WP_PLUGIN_DIR where the .mo file resides
			$this->domain_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';
		}
		

		// overwrite parent methods

		public function enqueue_admin_scripts(){
			
			// run script loader of parent class first
			parent::enqueue_admin_scripts();

			// localize script
			$nonce = wp_create_nonce( 'lablab_slider_post_data' );
			wp_localize_script( 'lablab-slideshow-admin', 'lablabSlider', 
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce' => $nonce,
					'type' => __('Type:&nbsp;', 'lablab-slideshow'),
					'blank' => __('Untitled', 'lablab-slideshow'),
					'fromPost' => __('From a post', 'lablab-slideshow'),
					'bgColor' => __('Background Color', 'lablab-slideshow')
				)
			);
		}
		
		
		public function admin_ajax_hooks(){

			add_action('wp_ajax_lablab_slider_post_data', array( $this, 'lablab_slider_post_data' ) );

		}
		
		public function lablab_slider_post_data(){

			check_ajax_referer( 'lablab_slider_post_data', 'nonce');

			$post_object = get_post( $_POST['postid'] );
			$post_data = array();
			$post_data['title'] = $post_object->post_title;
			$excerpt_args = array( 
				'post' => $post_object,
				'limit' => 200,
				'readmore' => false,
				'source' => 'excerpt',
				'suffix' => '...',
				'wrap_p' => false,
			);
			$excerpt_args = apply_filters( 'lablab_slider_excerpt_args', $excerpt_args );
			$post_data['excerpt'] = LabLab_Builder_Utilities::get_excerpt( $excerpt_args );
			$post_data['imageID'] = get_post_thumbnail_id( $post_object );
			$post_data['imageURL'] = wp_get_attachment_url( $post_data['imageID'], 'thumbnail');
			$post_data['url'] = get_permalink( $post_object );

			echo json_encode($post_data);

			die();

		}
	}

endif;

// register this module with lablab builder
add_filter( 'lablab_builder_modules', array( 'Lablab_Builder_Slideshow_Module', 'register' ) );