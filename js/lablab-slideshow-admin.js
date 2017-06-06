(function($){
	$(document).ready(function(){

		var $lablabBuilder = $('.lablab-content-area');
		var $slidesContainer = $lablabBuilder.find('.layout[data-layout="lablab-slideshow"]:not(.acf-clone)');
		var $slides = $slidesContainer.find('.acf-row:not(.acf-clone) > .acf-fields');
		var $contentSourceRadio = $('.lablab-slide-content-source input[type="radio"]');

		// refresh slides when a new one is added
		acf.add_action('append', function( $el ){
			$slidesContainer = $lablabBuilder.find('.layout[data-layout="lablab-slideshow"]:not(.acf-clone)');
			$slides = $slidesContainer.find('.acf-row').not('.acf-clone').find('.acf-fields');
			$el.find('.acf-field-message')
				.find('label')
					.html( 
						'<span class="lablab-slide-label-title">'
							+ lablabSlider.blank +
						'</span>' +
						'<br>' + 
						'<span class="lablab-slide-label-type">' +
							'(' + lablabSlider.type + lablabSlider.bgColor + ')' +
						'</span>'
					);
		});

		// when content source is selected
		$lablabBuilder.on('change', '.lablab-slide-content-source input[type="radio"]', function(){
			var $slide = $(this).closest('td');
			var maybeTrueFalse = ( $(this).val() === 'from_post' ? true : false );
			var maybeHidden = ( maybeTrueFalse ? 'none' : 'block');
			var type = ( maybeTrueFalse ? lablabSlider.fromPost : $slide.find('.lablab-slide-type input[type="radio"]:checked').parent().text() );
			
			$slide
				.find('.lablab-slide-overlay-title input')
					.prop('disabled', maybeTrueFalse)
				.end()
				.find('.lablab-slide-overlay-text textarea')
					.prop('disabled', maybeTrueFalse)
				.end()
				.find('.acf-field-message label .lablab-slide-label-type')
					.text( '(' + lablabSlider.type + type +')' )
				.end()
				.find('.lablab-slide-image .acf-image-uploader')
					.find('ul')
						.css('display', maybeHidden)
					.end()
					.find('a.button')
						.css('display', maybeHidden);
		});

		// fetch content from a post
		$lablabBuilder.on('change', '.lablab-slide-post-object select', function(){

			var postID = $(this).val();
			if (!postID){
				return false;
			}
			var $slide = $(this).closest($slides);
			var $imageUploader = $slide.find('.lablab-slide-image .acf-image-uploader');
			
			$.ajax({
				url: lablabSlider.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'lablab_slider_post_data',
					postid: postID,
					nonce: lablabSlider.nonce
				},
				success: function( postData ){
					$slide.find('.lablab-slide-overlay-title input').prop('disabled', true).val(postData.title);
					$slide.find('.lablab-slide-overlay-text textarea').prop('disabled', true).val(postData.excerpt);
					$slide.find('.lablab-slide-type input[value="image"]').prop('checked', true);
					$slide.find('.acf-field-message label .lablab-slide-label-title').text(postData.title);
					if ( postData.imageID != false){
						$imageUploader.addClass('has-value')
							.find('input')
								.val(postData.imageID)
							.end()
							.find('img')
								.attr('src', postData.imageURL);
					} else {
						$imageUploader.removeClass('has-value');
					}
				}
			});
			
		});

		// add title on page load
		$slides.each( function(){
			var title = $(this).find('.lablab-slide-overlay-title input').val();
			var type = $(this).find('.lablab-slide-type label.selected').text();
			var contentType = $(this).find('.lablab-slide-content-source input[type="radio"]:checked').val();
			if (contentType === 'from_post'){
				type = lablabSlider.fromPost;
				title = $(this).find('.lablab-slide-post-object span.select2-chosen').text();
			} 
			if (!title){
				title = lablabSlider.blank;
			}
			$(this).find('.acf-field-message label').html( '<span class="lablab-slide-label-title">' + title + '</span><br><span class="lablab-slide-label-type">(' + lablabSlider.type + type + ')</span>' );
		});
		$contentSourceRadio.filter('input[value="from_post"]:checked').each(function(){
			$(this).trigger('change');
			$(this).closest('td').find('.lablab-slide-post-object input').trigger('change');
		});

		// refresh title when overlay-title is changed
		$lablabBuilder.on('change', '.lablab-slide-overlay-title input', function(){
			var title = $(this).val();
			if (!title){
				title = lablabSlider.blank;
			}
			$(this).closest('td').find('.acf-field-message label .lablab-slide-label-title').text(title);
		});
		
		// refresh title when slide-type is changed
		$lablabBuilder.on('change', '.lablab-slide-type input[type="radio"]', function(){
			var type = $(this).closest('ul').find('label.selected').text();
			$(this).closest('td').find('.acf-field-message label .lablab-slide-label-type').text( '(' + lablabSlider.type + type + ')' );
		});

	});

})(jQuery);