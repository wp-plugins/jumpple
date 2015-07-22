//if ( !jQuery.curCSS ) { jQuery.curCSS = jQuery.css; }

;(function($){

	if ($) {

		$(document).ready(function(){
			$('#swtcptcf_additions_options').change( function() {
				if($(this).is(':checked') )
					$('.swtcptcf_additions_block').removeClass('swtcptcf_hidden');
				else
					$('.swtcptcf_additions_block').addClass('swtcptcf_hidden');
			});
			$('#swtcptcf_change_label').change( function() {
				if($(this).is(':checked') )
					$('.swtcptcf_change_label_block').removeClass('swtcptcf_hidden');
				else
					$('.swtcptcf_change_label_block').addClass('swtcptcf_hidden');
			});
			$('#swtcptcf_display_add_info').change( function() {
				if($(this).is(':checked') )
					$('.swtcptcf_display_add_info_block').removeClass('swtcptcf_hidden');
				else
					$('.swtcptcf_display_add_info_block').addClass('swtcptcf_hidden');
			});
		});

	}

})(jQuery);

