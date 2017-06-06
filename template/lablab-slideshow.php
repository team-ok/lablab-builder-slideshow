<?php
/**
 *
 * Lablab Slideshow Module Template
 *
 * @author Timo Klemm
 * @var    array	$column_width	An array holding the width of the grid column that is currently rendered in lablab builder's main loop.
 * @see    Lablab_Builder_Loop::run_loop
 * @param  int		$this->content_width	The maximum width of the theme's main content wrapper.
 * @see    Lablab_Builder_Loop
 */


global $post;

// general settings
$height = (int) get_sub_field( 'lablab_slider_height' );
$height = ( $height ? $height : 400 );

$width = (int) get_sub_field( 'lablab_slider_width' );
$width = ( $width ? $width : 'full' );

// array representing current column's width as fraction (array[0] = numerator, array[1] = denominator)
$column_width_fraction = explode( '-', $column_width['value'] );

if ( $width === 'full' ){
	$crop_width = round( $this->content_width * $column_width_fraction[0] / $column_width_fraction[1] );
} else {
	$crop_width = $width;
}


$autoplay = (bool) get_sub_field( 'lablab_slider_autoplay' );
$autoplay_interval = (int) get_sub_field( 'lablab_slider_autoplay_interval') * 1000;

if ( $autoplay && $autoplay_interval ){
	$autoplay = 'autoplay: true, autoplayInterval: ' . $autoplay_interval . ', ';
} else {
	$autoplay = '';
}

$animation = get_sub_field( 'lablab_slider_animation' );
$animation_duration = ( get_sub_field( 'lablab_slider_animation_duration' ) ? (int) get_sub_field( 'lablab_slider_animation_duration' ) : 500 );
$kenburns_duration = ( get_sub_field( 'lablab_slider_kenburns_duration' ) ? (int) get_sub_field( 'lablab_slider_kenburns_duration' ) : 15 );
$kenburns = ( get_sub_field( 'lablab_slider_kenburns' ) ? '\''.$kenburns_duration.'s\'' : 'false' );

// slider markup
if ( $width != 'full' ){
	$maybe_centered = ( get_sub_field( 'lablab_slider_center' ) ? ' uk-container-center' : '' );
	echo '<div style="max-width: ' . $width . 'px;" class="lablab-slider-wrapper uk-container' . $maybe_centered .'">';
}
		echo '<div style="height: ' . $height . 'px;" class="lablab-slider uk-slidenav-position" data-uk-slideshow="{height: '.$height.', '.$autoplay.'animation: \''.$animation.'\', duration: '.$animation_duration.', kenburns: '.$kenburns.'}">';
			echo '<ul class="uk-slideshow uk-overlay-active">';

			while ( have_rows('lablab_slides') ) : the_row();

				// subfields
				$slide_type = get_sub_field( 'lablab_slide_type' );
				$content_source = get_sub_field('lablab_slide_content_source');
				$position = get_sub_field( 'lablab_slide_overlay_position' );
				$no_kenburns = get_sub_field( 'lablab-slide-no-kenburns' );

				// get content of selected post
				if ( $content_source === 'from_post' ){

					$post_object = get_sub_field('lablab_slide_post_object');

					// skip slide if no post was selected
					if (! $post_object){
						continue;
					}
					$slide_type = 'image';

					// setup postdata of selected post
					$post = $post_object;

					setup_postdata( $post );
					
					$title = get_the_title();

					$excerpt_args = array(
						'limit' => 200,
						'readmore' => true,
						'source' => 'excerpt',
						'readmore_string' => __('Read More', 'lablab-slideshow'),
						'class' => 'lablab-slider-excerpt',
						'suffix' => '...',
						'wrap_p' => true,
					);
					// allow filtering of $excerpt_args
					$excerpt_args = apply_filters( 'lablab_slider_excerpt_args', $excerpt_args );

					// get excerpt of selected post or generate one if none exists
					$text = Lablab_Builder_Utilities::get_excerpt( $excerpt_args );
		
					$link = get_permalink();

					// restore global $post etc.
					wp_reset_postdata();

				// get manually entered content
				} else {
					
					$title = get_sub_field( 'lablab_slide_overlay_title');
					$text = get_sub_field( 'lablab_slide_overlay_text');
					$link = get_sub_field( 'lablab_slide_link' );

					if ($link === 'internal'){

						$link = get_sub_field( 'lablab_slide_link_internal');

					} elseif ($link === 'external'){

						$link = esc_url( get_sub_field( 'lablab_slide_link_external') );

					}
				}

				echo '<li' . ( $no_kenburns ? ' class="no-kenburns"' : '' ) . '>';
					switch ($slide_type) {
						case 'image':
							if ($content_source === 'from_post'){
								$image = wp_get_attachment_url( get_post_thumbnail_id($post_object->ID), 'full');
							} else {
								$image = get_sub_field( 'lablab_slide_image' );
							}
							if ( $image ){
								$image_args = array( 
									'resize' => array( $crop_width, $height, array( 'center', 'center' ) ),
								);
								$image = beans_edit_image( $image, $image_args, 'OBJECT' );
								echo '<img src="'.$image->src.'" width="'.$image->width.'" height="'.$image->height.'">';
							}
							break;
						case 'video':
							$video = get_sub_field( 'lablab_slide_video' );
							if ($video && is_array($video) ){
								echo '<video height="'.$video['height'].'" width="'.$video['width'].'" loop>';
									echo '<source src="'.$video['url'].'" type="'.$video['mime_type'].'">';
								echo '</video>';
							}
							break;
						case 'iframe':
							$iframe = get_sub_field( 'lablab_slide_iframe' );
							if ($iframe){
								echo $iframe;
							}
							break;
						
						default:
							$color = get_sub_field ( 'lablab_slide_color' );
							if ($color){
								echo '<div class="uk-cover-object" style="background-color: '.$color.';"></div>';
							}
							break;
					}
					if ($text || $title){
						$contrast = ( get_sub_field( 'lablab_slide_overlay_background' ) ? 'uk-overlay-background' : '');
						echo '<div class="uk-overlay-panel uk-overlay-fade '.$contrast.' uk-overlay-'.$position.'">';
							echo '<div>';
								if ($title){
									echo '<span>'.$title.'</span>';
								}
								if ($text){
									// maybe wrap text in <p>-tags
									echo ( strpos($text, '<p') !== 0 ? '<p>'.$text.'</p>' : $text );
								}
								if ($link){
									echo '<a class="uk-position-cover" href="'.$link.'"></a>';
								}
							echo '</div>';
						echo '</div>';
					}
				echo '</li>';

			endwhile;

			echo '</ul>';
			// slider navigation
			echo '<a href="" class="uk-slidenav uk-slidenav-contrast uk-slidenav-previous" data-uk-slideshow-item="previous"></a>';
			echo '<a href="" class="uk-slidenav uk-slidenav-contrast uk-slidenav-next" data-uk-slideshow-item="next"></a>';
		echo '</div>';
		
if ( $width != 'full' ){
	echo '</div>';
}