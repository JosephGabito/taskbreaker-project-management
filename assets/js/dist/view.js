var __ThriveProjectView = Backbone.View.extend({

    el: 'body',
    model: ThriveProjectModel,
    search: '',
    template: '',
    events: {
        "click .task_breaker-project-tab-li-item-a": "switchView",
        "click .next-page": "next",
        "click .prev-page": "prev",
        "submit #task-breaker-search-task-form": "searchTasks",
        "change #task_breaker-task-filter-select": "filter"
    },

    switchView: function( e, elementID ) {

        if ( e ) {
            
            var $elementClicked = $( e.currentTarget );
            // Disable clicking on the 'Add New Tab' if we are on 'Task Add' Route.
            var $tab_disabled = ['task_breaker-project-edit-tab', 'task_breaker-project-edit', 'task_breaker-project-add-new'];
            var $is_tab_enabled = $.inArray( $elementClicked.attr( 'id' ), $tab_disabled );
            if ( -1 !== $is_tab_enabled ) {
                return false;
            } 

        }

        // Disable any stay files and progress.
        taskbreaker_file_attachments.attached_files = '';
        $('.tasbreaker-file-attached').html('No Files Selected.');
        $('.tb-file-attachment-progress-wrap').removeClass('active');

        // Disable edit tab.
        $('#task_breaker-project-edit-tab').css('display', 'none');
        $('#task_breaker-project-add-new').css('display', 'none');

        $('.task_breaker-project-tab-li-item').removeClass('active');
        $('.task_breaker-project-tab-content-item').removeClass('active');

        var $active_content = "";

        if (e) {

            var $element = $(e.currentTarget);

            $active_content = $element.attr('data-content');

            // Activate selected tab.
            $element.parent().addClass('active');

            $('div[data-content=' + $active_content + ']').addClass('active');

        } else {

            $(elementID).addClass('active');

            $active_content = $(elementID).attr('data-content');

            $('a[data-content=' + $active_content + ']').parent().addClass('active');
        }
    },

    hideFilters: function() {
        $('#task_breaker-tasks-filter').hide();
    },

    showFilters: function() {
        $('#task_breaker-tasks-filter').show();
    },

    searchTasks: function( event ) {
        
        var keywords = $('#task_breaker-task-search-field').val();

        if ( 0 === keywords.length ) {
            location.href = '#tasks';
        } else {
            location.href = '#tasks/search/' + encodeURI(keywords);
        }

        return false;

    },

    filter: function(e) {
        this.model.priority = e.currentTarget.value;

        var currentRoute = Backbone.history.getFragment();

        if (currentRoute != 'tasks') {
            location.href = '#tasks';
        } else {
            this.render();
        }
    },

    next: function(e) {
        e.preventDefault();
        var currPage = this.model.page;
        if (currPage < this.model.max_page) {
            this.model.page = ++currPage;
            location.href = '#tasks/page/' + this.model.page;
        }
    },

    prev: function(e) {
        e.preventDefault();
        var currPage = this.model.page;
        if (currPage > this.model.min_page) {
            this.model.page = --currPage;
            location.href = '#tasks/page/' + this.model.page;
        }
    },

    single: function(ticket_id) {
        this.progress(true);
        var __this = this;
        this.template = 'single_task_index';
        // load the task
        this.renderTask(function( response ) {

            __this.progress( false );

            if ( response.message == 'fail' ) {
                $('#task_breaker-project-tasks').html("<p class='info' id='message'>"+response.message_long+"</p>");
            }

            if ( response.html ) {
                $('#task_breaker-project-tasks').html(response.html);
            }
        });
    },

    showEditForm: function(task_id) {

        var __this = this;

        var __taskEditor = '';

        if ( typeof tinymce !== 'undefined' ) {
            
            __taskEditor = tinymce.get('task_breakerTaskEditDescription');

        } 

        this.progress(true);

        if ( __taskEditor ) {
            __taskEditor.setContent( '' );
        } else {
            $( '#task_breakerTaskEditDescription' ).val( '' );
        }

        $('.task_breaker-project-tab-content-item').removeClass('active');
        $('.task_breaker-project-tab-li-item').removeClass('active');
        $('a#task_breaker-project-edit-tab').css('display', 'block').parent().addClass('active');
        $('#task_breaker-project-edit-context').addClass('active');

        $('#task_breakerTaskId').attr('disabled', true).val('loading...');
        $('#task_breakerTaskEditTitle').attr('disabled', true).val('loading...');
        $("#task_breaker-task-edit-select-id").attr('disabled', true);

        this.model.id = task_id;

        // Render the task.
        this.renderTask( function( response ) {

            __this.progress( false );

            if ( response.task ) {

                var task = response.task;

                var taskEditor = '';

                if ( typeof tinymce !== 'undefined' ) {
                    taskEditor = tinymce.get('task_breakerTaskEditDescription');
                }

                $('#task_breakerTaskId').val(task.id).removeAttr("disabled");

                $('#task_breakerTaskEditTitle').val(task.title).removeAttr("disabled");

                if ( taskEditor )
                {
                    taskEditor.setContent( task.description );
                } else
                {
                    $( '#task_breakerTaskEditDescription' ).val( task.description );
                }

                $("#task-user-assigned-edit").val('');

                if ( document.getElementById("task-user-assigned-edit") ) {
                    document.getElementById("task-user-assigned-edit").options.length = 0;
                }

                $.each( task.assign_users_meta.members_stack, function( key, val ) {
                    var option = document.createElement("option");
                        option.value = val.ID;
                        option.text  = val.display_name;
                        option.selected  = "selected";
                        document.getElementById("task-user-assigned-edit").appendChild( option );
                });

                __this.autoSuggestMembers( $("#task-user-assigned-edit"), true, task );

                $( "#task_breaker-task-edit-select-id" ).val( task.priority ).change().removeAttr("disabled");

                // Update Files Attached here..
                $('#task-breaker-form-file-attachment-edit-field').removeAttr('disabled');
                if ( task.meta ) {
                    $.each ( task.meta, function( key, val ){
                        if ( "file_attachment" === val.meta_key ) {
                            var unlink_file_template = '';
                            $('#taskbreaker-file-attachment-edit .tasbreaker-file-attached').html(val.meta_value);
                            // Assign the existing file to client file.
                            taskbreaker_file_attachments.attached_files = val.meta_value;
                            unlink_file_template += '<a href="#" title="Click to remove file attachment" data-attachment="'+val.meta_value+'">&times;</a>';
                            $('#taskbreaker-unlink-file-btn').html( unlink_file_template );
                        }
                    });
                } else {
                    $('#taskbreaker-file-attachment-edit .tasbreaker-file-attached').html('No files attached');
                    $('#taskbreaker-unlink-file-btn a').remove();
                }

            }

            return;

        });

    },

    renderTask: function( __callback ) {
        $.ajax({
            url: ajaxurl,
            method: 'get',
            dataType: 'json',
            data: {
                action: 'task_breaker_transactions_request',
                method: 'task_breaker_transaction_fetch_task',
                id: this.model.id,
                project_id: this.model.project_id,
                template: this.template,
                nonce: task_breakerProjectSettings.nonce
            },
            success: function( httpResponse ) {
                __callback( httpResponse );
            }
        });
    },

    render: function() {

        var __this = this;
        this.progress(true);

        $.ajax({
            url: ajaxurl,
            method: 'get',
            dataType: 'json',
            data: {
                action: 'task_breaker_transactions_request',
                method: 'task_breaker_transaction_fetch_task',
                id: this.model.id,
                project_id: this.model.project_id,
                page: this.model.page,
                search: this.search,
                priority: this.model.priority,
                template: 'render_tasks',
                show_completed: this.model.show_completed,
                nonce: task_breakerProjectSettings.nonce
            },
            success: function( response ) {

                __this.progress(false);

                if (response.message == 'success') {
                    if (response.task.stats) {
                        // update model max_page and min_page
                        ThriveProjectModel.max_page = response.task.stats.max_page;
                        ThriveProjectModel.min_page = response.task.stats.min_page;
                    }
                    // render the result
                    $('#task_breaker-project-tasks').html(response.html);
                }

                if ( 0 === response.task.length ) {
                    $('#task_breaker-project-tasks').html('<div class="task-breaker-message danger">No tasks found. Try different keywords and filters.</div>');
                }

            },
            error: function() {

            }
        });
    },

    initialize: function() {

    },

    progress: function(isShow) {

        var __display = 'none';
        var __opacity = 1;

        if ( isShow ) {
            __display = 'block';
            __opacity = 0.25;
        }

        $('#task_breaker-preloader').css({
            display: __display
        });

        $('#task_breaker-project-tasks').css({
            opacity: __opacity
        });

        return;
    },

    updateStats: function( stats ) {

        var priority = null;
        var task_status = null;

        if ( stats.status ) {
            priority = stats.status.priority;
            task_status = stats.status.task_status;
        }

        if ( task_status ) {
            $('#task-details-status').text( task_status ).removeClass("open close").addClass( task_status.toLowerCase() );
        }

        if ( priority ) {
            $('#task-details-priority').text( priority ).removeClass("normal high critical").addClass( priority.toLowerCase() );
        }

        $( '.task_breaker-total-tasks' ).text( stats.total );
        $( '.task_breaker-remaining-tasks-count' ).text( stats.remaining );
        $( '.task-progress-completed' ).text( stats.completed );
        $( '.task-progress-percentage-label > span' ).text( stats.progress );

        // Update the progress bar css width.
        $( '.task-progress-percentage' ).css({
            width: Math.ceil( ( ( stats.completed / stats.total ) * 100 ) ) + '%'
        });

    },

    autoSuggestMembers: function( selectElement, clearSelect, task ) {

        if ( ! selectElement ) {
            return;
        }

        var $resultTemplate = function( result ) {

			if ( result.avatar ) {

			    var $state = $('<span><img class="result-template-avatar" src="'+result.avatar+'" alt="s" />'+result.text+'</span>');
			}

			return $state;
		}


		selectElement.select2({
			maximumInputLength: 20,
			placeholder: "Type member\'s name...",
			allowClear: true,
			minimumResultsForSearch: Infinity,
			minimumInputLength: 2,
			tag: true,
			ajax: {

				data: function ( params ) {

					var query = {
						action: 'task_breaker_transactions_request',
						method: 'task_breaker_transactions_user_suggest',
						nonce: task_breakerProjectSettings.nonce,
						group_id: task_breakerProjectSettings.current_group_id,
						term: params.term,
						user_id_collection: 0
					}

					if ( selectElement.val() ) {
						query.user_id_collection = selectElement.val();
					}

					return query;
				},
				url: task_breakerAjaxUrl,
				delay: 250,
				cache: true
			},
			templateResult: $resultTemplate
		});
    }
});

var ThriveProjectView = new __ThriveProjectView();
