jQuery ($) =>
	class @MgzCreateForm
		constructor: ->
			@selectedIds ||= []
			
			$.ajax
				url: 'options-general.php?page=remag&json=y'
				dataType: 'json'
			.done (data) =>
				@articleList = data.posts
				@showPick()
			
		showPick: ->
			MgzModal.displayOrReplace ecoTemplates['pick_articles'](articles: @articleList, selected: @selectedIds)
			$('.mgz-modal-next').click =>
				@selectedIds = $('.mgz-modal input[type=checkbox]:checked').toArray().map (el) -> $(el).data('id')
				if @selectedIds.length == 0
					MgzModal.displayError 'You must select at least one article'
				else
					@_dataFor(@selectedIds).done (data) =>
						@articleData = data
						@showName()
		
		showName: ->
			MgzModal.displayOrReplace ecoTemplates['name_issue'](articles: @articleData)
			$('.mgz-modal-back').click => @showPick()
			$('.mgz-modal form').submit (e) =>
				@title = $('.mgz-modal input#mgz-issue-title').val()
				
				if @title.length == 0
					MgzModal.displayError 'Title can’t be blank'
					return no
				
				MgzModal.clearError()
				
				if issue_id = @submit()
					MgzModal.hide()
					mgz_adminPage.render()
					
					el = $(e.currentTarget)
					el.attr 'method', 'POST'
					el.attr 'action', mgz_admin_url + '/login'
					el.attr 'target', '_blank'
					
					el.find("input[name='login[username]']").val(mgz_settings.user.username)
					el.find("input[name='login[password]']").val(mgz_settings.user.password)
					el.find("input[name=redirect]").val("issues/" + issue_id)
					return yes
				else
					return no
		
		# Creates the issue on the server using @articleData.
		# Returns the server ID of created issue if successful
		# Displays error and returns false if failure 
		
		submit: ->
			result = $.ajax
				url: mgz_api_url + '/magazines/' + mgz_settings.magazine.identifier + '/issues'
				type: 'POST'
				async: no
				dataType: 'json'
				data:
					username: mgz_settings.user.username
					password: mgz_settings.user.password
					issue: JSON.stringify
						title: @title
						articles: @articleData.map (a) ->
							id: a.ID,
							title: a.post_title,
							content: a.post_content,
							metadata: a.meta,
							url: a.permalink
			
			if result.status == 200
				return result.responseJSON.issue_id
			else
				MgzModal.displayError 'An error has occured. Please try again.'
				return false
		
		_dataFor: (ids) ->
			$.ajax
				url: 'options-general.php?page=remag&json=y'
				dataType: 'json'
				async: no
				type: 'POST'
				data: { ids }