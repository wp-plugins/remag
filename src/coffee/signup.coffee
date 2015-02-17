jQuery ($) =>
	class @MgzSignupForm
		constructor: ->
			$.ajax
				url: 'options-general.php?page=remag&json=y'
				dataType: 'json'
			.done (data) =>
				blogName = data.blog_title
				@showForm(blogName)
		
		showForm: (magazineName) ->
			MgzModal.display ecoTemplates['signup']({magazineName}), narrow: yes
			$('.mgz-modal form').submit =>
				MgzModal.clearError()
				
				# validate form
				fields = @getValidatedFields()
				return no if !fields	
				[name, username, password, passwordConfirmation] = fields
				
				# create it!
				result = $.ajax
					url: mgz_api_url + '/magazines'
					type: 'POST'
					async: no
					dataType: 'json'
					data:
						magazine: { name }
						user: { username, password }
						wordpress_url: location.href.match(/^(.*)wp-admin/)[1]
				
				if result.status == 200
					magazineJson = result.responseJSON.magazine
					@saveAndClose(
						magazine: { name, id: magazineJson.id, identifier: magazineJson.identifier },
						user: { username, password })
				else
					MgzModal.displayError result.responseJSON?.error || 'An error has occured. Please try again.'
				
				return no
		
		# Saves settings, hides the modal and returns to homepage
		
		saveAndClose: (settings) ->
			mgz_settings.replace(settings)
			mgz_settings.save()
			MgzModal.hide()
			setTimeout (->
				mgz_adminPage.render(yes) # hide "show all issues"
			), 400
		
		getValidatedFields: ->
			getField = (id) -> $(".mgz-modal input##{id}").val().trim()
			
			name = getField 'mgz-magazine-name'
			username = getField 'mgz-user-username'
			password = getField 'mgz-user-password'
			passwordConfirmation = getField 'mgz-user-password-confirmation'
			
			if name.length == 0
				MgzModal.displayError "Magazine name can’t be blank"
			else if username.length == 0
				MgzModal.displayError "Email address can’t be blank"
			else if password.length == 0
				MgzModal.displayError "Password can’t be blank"
			else if passwordConfirmation.length == 0
				MgzModal.displayError "Password confirmation can’t be blank"
			else if password != passwordConfirmation
				MgzModal.displayError "Passwords don’t match"
			else
				return [name, username, password, passwordConfirmation]
			
			null