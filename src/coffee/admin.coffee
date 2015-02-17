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
