jQuery ($) =>
	class @MgzSettings
		magazine: { name: null, identifier: null }
		user: { username: null, password: null }
	
		constructor: ->
			result = $.ajax
				url: 'options-general.php?page=remag&mode=settings'
				async: no
			
			if json = result.responseJSON
				{ @magazine, @user } = json
	
		replace: ({ @magazine, @user }) ->
	
		save: ->
			$.ajax
				url: 'options-general.php?page=remag&mode=settings'
				type: 'POST'
				data:
					data: JSON.stringify { @magazine, @user }