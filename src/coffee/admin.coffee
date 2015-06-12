jQuery ($) =>
	class @MgzAdminPage
		render: (firstTime) ->
			settings = mgz_settings
			loggedIn = settings.user.username && settings.user.password
			
			$('.mgz-page').html ecoTemplates['homepage']({ settings, loggedIn, assets_url: mgz_assets_url })
			$('.mgz-page').toggleClass('first-time', !!firstTime)
			$('#mgz-create-issue-button').click => new MgzCreateForm
			$('#mgz-signup-button').click => new MgzSignupForm
			
			$('#mgz-see-all-issues-form').submit (e) ->
				el = $(e.currentTarget)
				el.attr 'method', 'POST'
				el.attr 'action', mgz_admin_url + '/login'
				el.attr 'target', '_blank'
				
				el.find("input[name='login[username]']").val(mgz_settings.user.username)
				el.find("input[name='login[password]']").val(mgz_settings.user.password)
				el.find("input[name=redirect]").val("magazines/" + mgz_settings.magazine.id)
				return yes
			
		updateSettings: ->
			if !mgz_settings.user.username || !mgz_settings.user.password
				return
			
			console.log 'Fetching magazine info'
			
			$.ajax
				url: mgz_api_url + '/magazines/' + mgz_settings.magazine.identifier + '.json'
				dataType: 'json'
				data:
					username: mgz_settings.user.username
					password: mgz_settings.user.password
			.done (data) =>
				# console.log 'Old settings:'
				# console.log mgz_settings
				# console.log 'New data:'
				# console.log data
				mgz_settings.magazine.name = data.name
				mgz_settings.magazine.app_store_id = data.app_store_id
				mgz_settings.magazine.google_play_id = data.google_play_id
				
				smartbannerReady = data.app_status == 'published' && data.app_store_id && data.google_play_id
				
				# if smartbannerReady && mgz_settings.smartbanner == null
				# 	console.log 'Enabling smartbanner'
				# 	mgz_settings.smartbanner = true
				
				mgz_settings.save()
				# console.log 'New settings:'
				# console.log mgz_settings
					
			.fail (_, status) =>
				console.log status
	
	debug = no
	if debug
		window.mgz_api_url    = 'http://api.pm.dev'
		window.mgz_admin_url  = 'http://admin.pm.dev'
	else
		window.mgz_api_url    = 'https://api.remag.me'
		window.mgz_admin_url  = 'https://admin.remag.me'
	
	window.mgz_assets_url = location.href.match(/^(.*)wp-admin/)[1] + 'wp-content/plugins/remag/admin/assets'
	window.mgz_adminPage  = new MgzAdminPage
	window.mgz_settings   = new MgzSettings
	mgz_adminPage.render()
	mgz_adminPage.updateSettings()
