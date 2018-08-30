<?php
/**
 * Elementor Repeater-Gallery Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_Repeater_Gallery_Widget extends \Elementor\Widget_Image_Gallery {

	/**
	 * Get widget name.
	 *
	 * Retrieve Repeater-Gallery widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'repeater_gallery';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Repeater-Gallery widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Repeater-Gallery', 'elementor-repeater-gallery-extension' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Repeater-Gallery widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Repeater-Gallery widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'jet-listing-elements' ];
	}

	/**
	 * Register Repeater-Gallery widget controls.
	 *
	 * Adds different fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		
		parent::_register_controls();
		
		$this->remove_control('wp_gallery');
		
		$this->start_controls_section(
			'section_general',
			array(
				'label' => __( 'Repeater Gallery Source', 'elementor-repeater-gallery-extension' ),
			)
		);

		$this->add_control(
			'repeater_notice',
			array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => __( '<b>Note</b> This widget can process only "media" type sub-fields within repeater meta fields created with JetThemeCore or ACF plugins', 'elementor-repeater-gallery-extension' ),
			)
		);

		$repeater_fields = $this->get_image_repeater_fields();

		$this->add_control(
			'dynamic_field_source',
			array(
				'label'   => __( 'Source', 'elementor-repeater-gallery-extension' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $repeater_fields,
			)
		);
		
		$this->end_controls_section();

	}

	/**
	 * Render Repeater-Gallery widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		if( function_exists( 'jet_engine' ) ) :

			$settings = $this->get_settings_for_display();
			$source = isset( $settings['dynamic_field_source'] ) ? $settings['dynamic_field_source'] : false;
			$source_arr = explode(" -- ", $source, 2);
			$source_field = isset( $source_arr[0] ) ?  $source_arr[0] : '';
			$source_key = isset( $source_arr[1] ) ?  $source_arr[1] : '';

			if( !empty( $source ) && !empty( $source_field ) && !empty( $source_key ) ) :

				$ids = jet_engine()->listings->data->get_meta( $source_field);
				$ids = wp_list_pluck($ids,$source_key);
			
				if( !empty( $ids ) ) :

					$this->add_render_attribute( 'shortcode', 'ids', implode( ',', $ids ) );
					$this->add_render_attribute( 'shortcode', 'size', $settings['thumbnail_size'] );

					if ( $settings['gallery_columns'] ) {
						$this->add_render_attribute( 'shortcode', 'columns', $settings['gallery_columns'] );
					}

					if ( $settings['gallery_link'] ) {
						$this->add_render_attribute( 'shortcode', 'link', $settings['gallery_link'] );
					}

					if ( ! empty( $settings['gallery_rand'] ) ) {
						$this->add_render_attribute( 'shortcode', 'orderby', $settings['gallery_rand'] );
					}
					?>
					<div class="elementor-image-gallery">
						<?php
						$this->add_render_attribute( 'link', [
							'data-elementor-open-lightbox' => $settings['open_lightbox'],
							'data-elementor-lightbox-slideshow' => $this->get_id(),
						] );

						if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
							$this->add_render_attribute( 'link', [
								'class' => 'elementor-clickable',
							] );
						}

						add_filter( 'wp_get_attachment_link', [ $this, 'add_lightbox_data_to_image_link' ] );

						echo do_shortcode( '[gallery ' . $this->get_render_attribute_string( 'shortcode' ) . ']' );

						remove_filter( 'wp_get_attachment_link', [ $this, 'add_lightbox_data_to_image_link' ] );
						?>
					</div>
			
				<?php endif;

			endif;
		
		endif;

	}

	/**
	 * Get repeater meta fields for post type
	 *
	 * @return array
	 */	
	public function get_image_repeater_fields() {

		$result      = array();

		if( function_exists('jet_engine') ) {
			$meta_fields = jet_engine()->listings->data->get_listing_meta_fields();

			if ( ! empty( $meta_fields ) ) {
				foreach ( $meta_fields as $field ) {
					if ( 'repeater' === $field['type'] ) {
						foreach ( $field['repeater-fields'] as $subfield ) {
							if ( 'media' === $subfield['type'] ) {
								$result[ $field['name'] . ' -- ' . $subfield['name'] ] = $field['title'] . ' -- ' . $subfield['title'];
							}
						}
					}
				}
			}
		}

		return apply_filters( 'jet-engine/listings/dynamic-gallery-repeater/fields', $result );

	}

}