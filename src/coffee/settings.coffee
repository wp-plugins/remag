jQuery ($) =>
	class @MgzSettings
		magazine: { name: null, identifier: null, app_store_id: null, google_play_id: null }
		user: { username: null, password: null }
		smartbanner: null
	
		constructor: ->
			result = $.ajax
				url: 'options-general.php?page=remag&mode=settings'
				async: no
			
			if json = result.responseJSON
				{ @magazine, @user, @smartbanner } = json
	
		replace: ({ @magazine, @user }) ->
	
		save: ->
			$.ajax
				url: 'options-general.php?page=remag&mode=settings'
				type: 'POST'
				data:
					data: JSON.stringify { @magazine, @user, @smartbanner }