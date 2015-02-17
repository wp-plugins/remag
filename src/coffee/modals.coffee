jQuery ($) =>
	class @MgzModal
		modalContainer = '.mgz-page'
		
		@display: (html, options = {}) =>
			return if @isVisible
			$('body').addClass('mgz-modal-lock')
			$(modalContainer).append @_template(html, options)
			$('.mgz-modal-cancel').click @hide
			@isVisible = yes
		
		@replace: (html) =>
			return if !@isVisible
			$('.mgz-modal').html(html)
			$('.mgz-modal-cancel').click @hide
		
		@displayOrReplace: (html, options = {}) =>
			if @isVisible
				@replace(html)
			else
				@display(html, options)
		
		@hide: =>
			return if !@isVisible
			$('.mgz-modal-container').addClass 'animate-close'
			setTimeout ( =>
				$('.mgz-modal-container').remove()
				$('body').removeClass('mgz-modal-lock')
				@isVisible = no
			), 400
			no
		
		@visible: =>
			@isVisible || no
		
		@displayError: (error) =>
			return if !@isVisible
			@clearError()
			$('.mgz-modal').find('h1').after '<div class="alert alert-danger"><p>' + error + '</p></div>'
		
		@clearError: =>
			return if !@isVisible
			$('.mgz-modal').find('div.alert.alert-danger').remove()
	
		@_template: (html, { narrow }) ->
			'<div class="mgz-modal-container animate-open"><div class="mgz-modal ' + (if narrow then 'narrow' else '') + '">' + html + '</div></div>'