jQuery(document).ready( function($) {
	
	'use strict';

	$( window ).load( function() {

		if ( typeof task_breakerAjaxUrl === 'undefined' ) {
			return;
		}

		var ajaxurl = task_breakerAjaxUrl;

		/**
		 * Delete Event
		 */
		$('body').on('click', '.task_breaker-delete-ticket-btn', function(e){

			e.preventDefault();

			var element = $(this);

				element.text('Removing Ticket ...');
				element.parent().parent().parent().parent().remove();

				$.ajax({
					url: ajaxurl,
					method: 'post',
					data: {
						id: element.attr('data-ticket-id'),
						action: 'task_breaker_transactions_request',
						method: 'task_breaker_transaction_delete_ticket',
						nonce: task_breakerProjectSettings.nonce
					},
					success: function(response) {
						
					},
					error: function(error_response, error_message) {
						console.log('Error:' + error_message);
					}
				});

			return;	
		});

		/**
		 * Delete Event
		 */
		$('#task_breaker-delete-btn').click(function(e){
			
			e.preventDefault();

			var verify_delete = confirm('Are you sure you want to delete this task?');

			if ( ! verify_delete ) {
				return;
			}

			var element = $(this);
			var task_id = $('#task_breakerTaskId').val();
			var http_params = {
				id: task_id,
				action: 'task_breaker_transactions_request',
				method: 'task_breaker_transaction_delete_ticket',
				nonce: task_breakerProjectSettings.nonce
			};

			element.attr('disabled', true).text('Deleting Task...');

			$.ajax({
				url: ajaxurl,
				method: 'post',
				data: http_params,
				success: function( response ) {
					element.attr('false', true).text('Delete');
					location.href = '#tasks';
				},
				error: function(error_response, error_message) {
					element.attr('false', true).text('Delete');
					location.href = '#tasks';
				}
			});

			return;
		});

		/**
		 * Edit Event
		 */
		$('#task_breaker-edit-btn').click(function(e){

			e.preventDefault();

			var element = $(this);
				element.attr('disabled', true);
				element.text('Loading ...');

			var taskDescription = "";

			var MCEtask_breakerTaskDescription = tinymce.get( 'task_breakerTaskEditDescription' );

			if ( MCEtask_breakerTaskDescription ) {

				taskDescription = MCEtask_breakerTaskDescription.getContent();

			} else {

				taskDescription = $('#task_breakerTaskEditDescription').val();

			}

			$.ajax({
				
				url: ajaxurl,
				data: {
					action: 'task_breaker_transactions_request',
					method: 'task_breaker_transaction_edit_ticket',
					title: $('#task_breakerTaskEditTitle').val(),
					description: taskDescription,
					milestone_id: $('#task_breakerTaskMilestone').val(),
					id: $('#task_breakerTaskId').val(),
					project_id: task_breakerTaskConfig.currentProjectId,
					user_id: task_breakerTaskConfig.currentUserId,
					priority: $('#task_breaker-task-edit-select-id').val(),
					nonce: task_breakerProjectSettings.nonce,
				},
				
				method: 'post',

				success: function( __response ) {
					
					var response = JSON.parse( __response );

					if ( "fail" === response.message && "no_changes" !== response.type ) {

						$('#task_breaker-edit-task-message').addClass('error').text('There was an error updating the task').show();

					} else {

						$('#task_breaker-edit-task-message').removeClass('error').text('Task successfully updated').show();

					}

					setTimeout( function() {
						$('#task_breaker-edit-task-message').removeClass('error').text('').hide();
					}, 5000);
					
					element.attr('disabled', false).text('Update Task');

					return;

				},
				error: function() {

					$('#task_breaker-edit-task-message').addClass('error').text('Ops! Network Error. Please try again later').show();

					setTimeout( function() {
						$('#task_breaker-edit-task-message').removeClass('error').text('').hide();
					}, 5000);

					element.attr('disabled', false).text('Update Task');

					return;
				}
			});
		});

		/**
		 * Save Event
		 */
		$('#task_breaker-submit-btn').click(function(e){
			
			e.preventDefault();
			
			var element = $(this);
				element.attr('disabled', true);
				element.text('Loading ...');

			var taskDescription = "";

			var MCEtask_breakerTaskDescription = tinymce.get( 'task_breakerTaskDescription' );

			if ( MCEtask_breakerTaskDescription ) {

				taskDescription = MCEtask_breakerTaskDescription.getContent();

			} else {

				taskDescription = $('#task_breakerTaskDescription').val();

			}

			$.ajax( {

				url: ajaxurl,

				data: {
					action: 'task_breaker_transactions_request',
					method: 'task_breaker_transaction_add_ticket',
					title: $('#task_breakerTaskTitle').val(),
					description: taskDescription,
					milestone_id: $('#task_breakerTaskMilestone').val(),
					project_id: task_breakerTaskConfig.currentProjectId,
					user_id: task_breakerTaskConfig.currentUserId,
					priority: $('#task_breaker-task-priority-select').val(),
					nonce: task_breakerProjectSettings.nonce
				},

				method: 'post',
				success: function(__message) {

					var message = JSON.parse(__message);

					if (message.message === 'success') {
						
						element.text('Save Task');
							element.removeAttr('disabled');
						
						$('#task_breakerTaskDescription').val('');
							$('#task_breakerTaskTitle').val('');

						location.href="#tasks/edit/"+message.response.id;	

					} else {

						$('#task_breaker-add-task-message').text(message.response).show().addClass('error');
						
						setTimeout(function(){
							$('#task_breaker-add-task-message').text('').hide().removeClass('error');
						}, 3000);

						element.text('Save Task');
							element.removeAttr('disabled');
					}
				}, 
				error: function() {

				}
			});
		});
		
		var __ThriveModel = Backbone.Model.extend({
			
			page: 1,
			current_page: 1,
			max_page: 1,
			min_page: 1,
			total: 0,
			total_pages: 0,
			
			initialize: function() {
				// do nothing
			},
			
			renderAddForm: function() {

				$('.task_breaker-tab-item-content').removeClass('active');
				$('#task_breaker-edit-task-list').addClass('hidden');

				$('#task_breaker-add-task').addClass('active');
			},
			
			renderEditForm: function( task_id ) {

				$('#task_breaker-edit-task-list').removeClass('hidden');
				$('.task_breaker-tab-item-content').removeClass('active');
				$('#task_breaker-edit-task').addClass('active');
				$('#task_breakerTaskEditTitle').val('').attr('disabled', true).val('loading...');
				$('#task_breaker-task-edit-select-id').attr('disabled', true);

				$('#task_breakerTaskId').val(task_id);

				var initThriveTaskEditDescription = tinymce.get('task_breakerTaskEditDescription');

				// Reset the editor
				if ( initThriveTaskEditDescription ) {
					initThriveTaskEditDescription.setContent('');
				} else {
					$('#task_breakerTaskEditDescription').val('');
				}

				$.ajax({
					url: ajaxurl,
					method: 'get',
					data: {
						action: 'task_breaker_transactions_request',
						method: 'task_breaker_transaction_fetch_task',
						id: task_id,
						nonce: task_breakerProjectSettings.nonce
					},

					success: function(__response) {

						var response = JSON.parse(__response);
						
						if ( response.message === "success" ) {
							
							$('#task_breakerTaskEditTitle').val(response.task.title).removeAttr('disabled');

							$('#task_breaker-task-edit-select-id').val(response.task.priority).removeAttr('disabled');

							var task_breakerTaskEditDescription = tinymce.get('task_breakerTaskEditDescription');
							if ( task_breakerTaskEditDescription ) {
								task_breakerTaskEditDescription.setContent( response.task.description );
							} else {
								$('#task_breakerTaskEditDescription').val( response.task.description );
							}

						}
					},
					error: function(error, errormessage, error2) {
						console.log(error2);
					}
				});
			}
		});
	
		var ThriveModel = new __ThriveModel();

		/**
		 * Thrive Task View
		 */
		var __ThriveTaskView = Backbone.View.extend({
			model: ThriveModel,
			el: 'body',
			id: 'task_breaker_tasks_metabox',
			priority: -1,
			search: '',
			showCompleted: 'no',
			events: {
				"click .task_breaker-task-tabs": 'switchView',
				"click .next-page": "nextPage",
				"click .prev-page": "prevPage",
				"click #task_breaker-task-search-submit": "searchTasks",
				"click .task_breaker-complete-ticket": 'completeTicket',
				"click .task_breaker-renew-task": 'renewTask',
				"change #task_breaker-task-filter-select": "filterByPriority",
			},
			
			switchView: function(e, targetID) {
				
				var element = null;

				if ( e ) {
					element = $(e.target);
				}

				$('#task_breaker-task-edit-tab').hide();
				$('.task_breaker-task-tabs').removeClass('ui-state-active');
					
				if ( targetID ) {
					$( targetID ).addClass('ui-state-active');
				} else {
					if ( element !== null ) {
						element.parent().addClass('ui-state-active');
					}
				}
			},
			prevPage: function(e) {

				e.preventDefault();
				var minimum_page = 1;
				
				if (this.model.get('page') > minimum_page) {

					var current_page = this.model.get('page');
					var next_page = --current_page;

					this.model.set({
						page: next_page
					});

					location.href="#tasks/page/" + this.model.get('page');
				}
				return;
			},

			nextPage: function(e) {
				
				e.preventDefault();
				
				var maximum_page = this.model.get('max_page'); 

				var current_page = this.model.get('page');
				
					if ( current_page < maximum_page ) {
						
						current_page = this.model.get('page');
						var next_page = ++current_page;

							this.model.set({
								page: next_page
							});

						location.href="#tasks/page/" + this.model.get('page');
					}

				return;

			},
			
			filterByPriority: function(e){
				
				selected = e.target.value;

				var priority = [];
					priority['1']  = 'normal';
					priority['2']  = 'high';
					priority['3']  = 'critical';

				var new_priority = priority[selected];

					if (new_priority) {
						location.href = '#tasks/show/'+new_priority;
					} else {
						this.priority = -1;
						location.href = '#tasks';
					}

			},

			searchTasks: function(e){

				var keywords = $('#task_breaker-task-search-field').val();
				
				if (keywords.length >= 1) {
					location.href = '#tasks/search/' + encodeURIComponent(keywords);
				} else {
					location.href = '#tasks';
				}

				return;
			},

			render: function() {

				var model = ThriveModel;
				var view = this;

				$('.task_breaker-tab-item-content').removeClass('active');
				$('#task_breaker-edit-task-list').addClass('hidden');
				$('#task_breaker-task-list').addClass('active');

				$('#task_breaker-action-preloader').css('display', 'block');

				$('#task_breaker-task-list-canvas').css('opacity', 0.25);
				$('#task_breaker-tasks-filter').css('opacity', 0.25);

				if (this.search.length === 0) {
					$('#task_breaker-task-search-field').val('');
				} else {
					$('#task_breaker-task-search-field').val(this.search);
				}

				$.ajax({
					url: ajaxurl,
					method: 'get',
					data: {
						action: 'task_breaker_transactions_request',
						method: 'task_breaker_transaction_fetch_task',
						page: model.get('page'),
						project_id: task_breakerTaskConfig.currentProjectId,
						priority: this.priority,
						search: this.search,
						show_completed: this.showCompleted,
						id: 0,
						nonce: task_breakerProjectSettings.nonce
					},
					success: function( __response ) {
						
						var response = JSON.parse( __response );
							
							if (response.task.stats) {
								
								model.set({
									max_page: response.task.stats.max_page,
									page: response.task.stats.current_page
								});
							}
							
							$('#task_breaker-task-list-canvas').html(response.html);
							$('#task_breaker-action-preloader').css('display', 'none');
							
							$('#task_breaker-task-list-canvas').css('opacity',1);
							$('#task_breaker-tasks-filter').css('opacity', 1);

							setTimeout(function(){
								$('#task_breaker-task-current-page-selector').val(model.get('page'));
							}, 2000);

							$('#task_breaker-task-filter-select').val(view.priority);
							
						return;

					},
					error: function(error, errormessage) {
						console.log(errormessage);
						$('#task_breaker-action-preloader').css('display', 'none');
					}
				});
			},

			completeTicket: function(e) {
				e.preventDefault();

				var item_task_id = e.currentTarget.dataset.task_id;
				var item_user_id = e.currentTarget.dataset.user_id;
				var item_row_container = $(e.currentTarget).parent().parent().parent();
					item_row_container.css('opacity', 0.25);

				$.ajax({
					url: ajaxurl,
					method: 'post',
					data: {
						action: 'task_breaker_transactions_request',
						method: 'task_breaker_transaction_complete_task',
						task_id: item_task_id,
						user_id: item_user_id,
						nonce: task_breakerProjectSettings.nonce
					},
					success: function( __response ) {
						
						var response = JSON.parse( __response );

						item_row_container.css( 'opacity', 1 );

						if ( response.message === 'success' && response.task_id !== 0 ) {

							var item = $(e.currentTarget);
							
							var item_row = item.parent().parent().parent();

								item_row.addClass('completed');

								$('a', item).text('Renew Task').parent().removeAttr('data-user_id').removeClass('task_breaker-complete-ticket').addClass('task_breaker-renew-task');

						} else {

							console.log('Request Success #424');

							this.error();

						}
					},
					error: function() {
						console.log('Request Error #431');
					}
				});
			},

			renewTask: function(e) {
				
				e.preventDefault();

				var item_row_container = $(e.currentTarget).parent().parent().parent();
					item_row_container.css('opacity', 0.25);

				var current_user_id = 1;	

				var item_task_id = e.currentTarget.dataset.task_id;
					
					$.ajax({
						url: ajaxurl,
						method: 'post',
						data: {
							action: 'task_breaker_transactions_request',
							method: 'task_breaker_transaction_renew_task',
							task_id: item_task_id,
						},
						success: function( __response ){
							
							var response = JSON.parse( __response );

							item_row_container.css('opacity', 1);

							if (response.message === 'success' && response.task_id !== 0) {
								var item = $(e.currentTarget);
								var item_row = item.parent().parent().parent();
									item_row.removeClass('completed');

									
									$('a', item).text('Complete Task').parent().attr('data-user_id', current_user_id).addClass('task_breaker-complete-ticket').removeClass('task_breaker-renew-task');
							}
						},
						error: function(){}
					});
			},

			initialize: function() {
				$('#task_breaker-task-current-page-selector').val(this.model.get('page'));
				$('#task_breaker-task-filter-select').val(this.priority);
			}
		});
		
		var ThriveTaskView = new __ThriveTaskView();
		
		/**
		 * Thrive Router
		 */
		var __ThriveRouter = Backbone.Router.extend({

			routes: {
				"": "index",
				"tasks": "index",
				"tasks/add": "add",
				"tasks/edit/:id": "edit",
				"tasks/page/:id": "navigatePage",
				"tasks/show/:priority": "filterByPriority",
				"tasks/search/:search": "search",

				"tasks/completed": "showCompleted"
			},

			view: ThriveTaskView,

			model: ThriveModel,

			index: function() {
				// reset paging
				this.model.set({
					page: 1
				});
				// reset priority
				this.view.priority = -1;
				// reset search
				this.view.search = "";
				// reset view
				this.view.showCompleted = 'no';

				this.view.render();
			},

			navigatePage: function(__page) {
				this.model.set({page: __page});
				this.view.render();
			},

			filterByPriority: function(priority_label) {
				
				var priority = {
					"normal": 1,
					"high": 2,
					"critical": 3
				};

				var new_priority = priority[priority_label];

				// reset paging
				this.model.set({
					page: 1
				});

				if (new_priority) {
					this.view.priority = parseInt(new_priority);
					this.view.render();
				}
			},

			search: function(keywords) {
				
				this.view.switchView(null, '#task_breaker-task-completed-tab');

				this.model.set({
					page: 1
				});
				// reset priority
				this.view.priority = -1;
				this.view.search = keywords;
				this.view.render();
			},

			showCompleted: function() {
				this.view.showCompleted = "yes";
				this.view.switchView(null, '#task_breaker-task-completed-tab');
				
				this.view.render();
			},

			add: function() {
				
				this.view.switchView(null, '#task_breaker-task-add-tab');
				ThriveModel.renderAddForm();
				
				var MCEtask_breakerTaskDescription = tinymce.get( 'task_breakerTaskDescription' );

					if ( MCEtask_breakerTaskDescription ) {
						MCEtask_breakerTaskDescription.setContent('');
					} else {
						$('#task_breakerTaskDescription').text('');
					}

				return;

			},

			edit: function(id) {
				this.view.switchView(null, '#task_breaker-task-edit-tab');
				$('#task_breaker-task-edit-tab').show();
				ThriveModel.renderEditForm(id);
				// call the post
			},
			initialize: function() {

			}
		}); 
	
		
		var ThriveRouter = new __ThriveRouter();
		
		Backbone.history.start();

		// prevent form submission
		$('#task_breaker-task-search-field').keypress(function(e){
		    if ( e.which == 13 ) e.preventDefault();
		}); 
	});//Window Onload.
});//document Ready