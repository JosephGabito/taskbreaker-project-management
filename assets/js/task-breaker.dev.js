jQuery(document).ready(function($) {
	'use strict'
	$(window).load( function() {  

/**
 * This global object will hold the string that contains the name of the file attached in to task.
 * @type Object
 */
var taskbreaker_file_attachments = {
	attached_files: ''
};
/**
 * This function serves as a callback function for the file attachment event handler.
 * @param  object event The onchange event callback argument.
 * @return void
 */
var taskbreaker_process_file_attachment = function ( event, container_id, __form_data ) {

    // The upload file event object.
    var files = event.target.files;

    if ( files.length <= 0 ) {
        return;
    }
    
    // The form data.
    var data = new FormData();
    // The unique container that will hold the file attachments.
    var container = '#' + container_id + ' ';
    // The name of the file selected.
    var file_name = event.target.files[0].name;
    // The file errors count
    var file_errors = 0;

    if ( files.length >= 1 ) {
        $.each( files, function() {
            if ( this.size > parseInt( task_breakerProjectSettings.max_file_size ) ) {
                file_errors++;
            }
        });
    }

    if ( file_errors >= 1 ) {
        alert('There was an error uploading your file. File size exceeded the allowed number of bytes per request.');
        return;
    }

    // Change the file name accordingly.
    $( container + '.tasbreaker-file-attached').html( file_name );

    // Append all files into data form data.
    $.each( files, function( key, value ) {
        data.append( key, value );
    });

    // Append __form_data attribute if not empty.
    if ( typeof __form_data !== 'null' ) {
    	$.each( __form_data, function(k, v){
    		data.append(k, v);
    	});
    }

    // Append the action.
    data.append( 'action', 'task_breaker_transactions_request' );
    // Append the method.
    data.append( 'method', 'task_breaker_transaction_task_file_attachment' );
    // Append the nonce.
    data.append( 'nonce', task_breakerProjectSettings.nonce );
    // Remove any existing error messages.
    $( container + '.taskbreaker-upload-error' ).remove();
    // Clear any progress messages.
    $( container + '.taskbreaker-upload-error-text-helper').removeClass('active');
    $( container + '.taskbreaker-upload-success-text-helper').removeClass('active');

    // Begin ajax request.
    $.ajax({
        url: task_breakerAjaxUrl,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files.
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request.
        success: function( response, textStatus, jqXHR )
        {
           
            if( typeof response.error === 'undefined' )
            {   
                if ( response !== 0 ) {

                    if ( response.message === 'fail' ) {
                        taskbreaker_file_attachments.attached_files = '';
                        $( container + '.tb-file-attachment-progress').parent().append('<div class="taskbreaker-upload-error">'+response.response+'</div>');
                        $( container + '.taskbreaker-upload-error-text-helper').addClass('active');
                        $( container + '.taskbreaker-upload-success-text-helper').removeClass('active');
                    } else {
                        taskbreaker_file_attachments.attached_files = response.file;
                        $( container + '.taskbreaker-upload-error').remove();
                        $( container + '.taskbreaker-upload-error-text-helper').removeClass('active');
                        $( container + '.taskbreaker-upload-success-text-helper').addClass('active');
                    }
                    
                } else {
                    $( container + '.taskbreaker-upload-error-text-helper').addClass('active');
                    $( container + '.taskbreaker-upload-success-text-helper').removeClass('active');
                    $( container + '.tb-file-attachment-progress').parent().append('<div class="taskbreaker-upload-error">The application did not received any response from the server. Try uploading smaller files.</div>');
                    taskbreaker_file_attachments.attached_files = '';
                }
                
            }
            else
            {
                // Handle errors here
                console.log('File attachment errors debug: ' + response.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            // Handle errors here
            console.log('File attachment errors debug: ' + textStatus);
            // STOP LOADING SPINNER
        },
        xhr: function(){

            var myXhr = $.ajaxSettings.xhr();
            var progress = 0;
            var progress_percentage = '0%';

            if ( myXhr.upload ) {

                // For handling the progress of the upload
                $( container + '.tb-file-attachment-progress-wrap').addClass('active');
                $( '#task_breaker-submit-btn').attr('disabled', true);
                $( '#task_breaker-edit-btn').attr('disabled', true);

                myXhr.upload.addEventListener('progress', function(e) {

                    if ( e.lengthComputable ) {
                        $('progress').attr({
                            value: e.loaded,
                            max: e.total,
                        });
                        progress = ( e.loaded / e.total ) * 100;
                        if ( typeof progress === 'number' ) {
                            progress_percentage = Math.floor( progress ) + '%';
                            $( container + '.tb-file-attachment-progress-movable').css({
                                width: progress_percentage
                            });
                            $( container + '.taskbreaker-upload-progress-value').html( progress_percentage );
                        }
                    }

                } , false );

            }
            return myXhr;
        },
        complete: function() {
            console.log('finished');
            $( '#task_breaker-submit-btn').removeAttr( 'disabled' );
            $( '#task_breaker-edit-btn').removeAttr( 'disabled' );
        }
    });
};
var __ThriveProjectModel = Backbone.View.extend({
    id: 0,
    project_id: task_breakerProjectSettings.project_id,
    page: 1,
    priority: -1,
    current_page: 1,
    max_page: 1,
    min_page: 1,
    total: 0,
    show_completed: 'no',
    total_pages: 0,
});

var ThriveProjectModel = new __ThriveProjectModel();

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

var __ThriveProjectRoute = Backbone.Router.extend({

    routes: {
        "tasks": "index",
        "tasks/dashboard": "dashboard",
        "tasks/settings": "settings",
        "tasks/completed": "completed_tasks",
        "tasks/add": "add",
        "tasks/edit/:id": "edit",
        "tasks/page/:page": "next",
        "tasks/view/:id": "view_task",
        "tasks/search/:search_keyword": 'search',
    },
    view: ThriveProjectView,
    model: ThriveProjectModel,
    index: function() {

        this.view.switchView(null, '#task_breaker-project-tasks-context');
        this.model.page = 1;
        this.model.id = 0;
        this.model.show_completed = 'no';

        this.view.search = '';
        this.view.render();
    },

    dashboard: function() {
        this.view.switchView(null, '#task_breaker-project-dashboard-context');
    },
    settings: function() {
        this.view.switchView(null, '#task_breaker-project-settings-context');
    },
    add: function() {
        this.view.switchView(null, '#task_breaker-project-add-new-context');

        $('#task_breaker-project-add-new').css('display', 'block');
        $('#task-user-assigned').val("");
        this.view.autoSuggestMembers( $("#task-user-assigned"), true, null );

        if ( tinymce.editors.task_breakerTaskDescription ) {
            tinymce.editors.task_breakerTaskDescription.setContent('');
        }
    },
    completed_tasks: function() {

        this.view.switchView(null, '#task_breaker-project-tasks-context');

        this.model.show_completed = 'yes';
        this.view.render();
    },
    edit: function(task_id) {
        this.view.showEditForm(task_id);
        $('#task_breaker-edit-task-message').html('');
    },
    next: function(page) {
        this.model.page = page;
        this.view.render();
    },
    view_task: function(task_id) {
        this.model.id = task_id;
        this.view.single(task_id);
        this.view.switchView(null, '#task_breaker-project-tasks-context');
    },
    search: function(keywords) {
        this.model.page = 1;
        this.model.id = 0;
        this.view.search = keywords;
        this.view.render();
    }
});

var ThriveProjectRoute = new __ThriveProjectRoute();

ThriveProjectRoute.on('route', function(route) {
    if ('view_task' === route) {
        this.view.hideFilters();
    } else {
        this.view.showFilters();
    }
});

Backbone.history.start();

/**
 * This variable is really important for holding client uploaded files.
 * @type string The file name.
 */
$('#task_breaker-submit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";
    var __taskEditor = tinymce.get( 'task_breakerTaskDescription' );

    if ( __taskEditor ) {
       taskDescription =  __taskEditor.getContent();
    } else {
       taskDescription = $( '#task_breakerTaskDescription' ).val();
    }

    $.ajax({
        url: ajaxurl,
        data: {

            action: 'task_breaker_transactions_request',
            method: 'task_breaker_transaction_add_ticket',

            description: taskDescription,

            title: $('#task_breakerTaskTitle').val(),
            milestone_id: $('#task_breakerTaskMilestone').val(),
            priority: $('select#task_breaker-task-priority-select').val(),

            nonce: task_breakerProjectSettings.nonce,

            project_id: task_breakerTaskConfig.currentProjectId,
            user_id: task_breakerTaskConfig.currentUserId,
            user_id_collection: $('select#task-user-assigned').val(),
            file_attachments: taskbreaker_file_attachments.attached_files
        },

        method: 'post',

        success: function( message ) {

            // Total tasks view.
            var total_tasks = parseInt( $('.task_breaker-total-tasks').text().trim() );

            // Remaining tasks view
            var remaining_tasks = parseInt( $('.task_breaker-remaining-tasks-count').text().trim() );

           // console.log( message );

            if ( message.message === 'success' ) {

                element.text('Save Task');

                element.removeAttr('disabled');

                $('#task_breakerTaskDescription').val('');

                $('#task_breakerTaskTitle').val('');

                ThriveProjectView.updateStats( message.stats );

                location.href = "#tasks/view/" + message.response.id;

            } else {

                $('#task_breaker-add-task-message').html('<p class="error">'+message.response+'</p>').show().addClass('error');

                element.text('Save Task');

                element.removeAttr('disabled');

            }
        },
        error: function() {

        }
    }); // End $.ajax call.
}); // End $('#task_breaker-submit-btn').click() call.

/**
 * Attach event to file attachment. When changed upload the file to user logged in '/tmp' directory.
 * @return void
 */
$('#task-breaker-form-file-attachment-field').on( 'change', function( event ) {
    
    taskbreaker_process_file_attachment( event, 'taskbreaker-file-attachment-add' );

    return;
});

taskbreaker_file_attachments.attached_files = '';

$('#task_breaker-edit-btn').click( function( e ) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";

    var taskDescriptionObject = tinymce.get( 'task_breakerTaskEditDescription' );

    if ( taskDescriptionObject ) {

        taskDescription = taskDescriptionObject.getContent();

    } else {

        taskDescription = $('#task_breakerTaskEditDescription').val();
        
    }

    var httpRequestParameters = {
        description: taskDescription,
        nonce: task_breakerProjectSettings.nonce,
        project_id: task_breakerTaskConfig.currentProjectId,
        user_id: task_breakerTaskConfig.currentUserId,

        action: 'task_breaker_transactions_request',
        method: 'task_breaker_transaction_edit_ticket',

        title: $('#task_breakerTaskEditTitle').val(),
        milestone_id: $('#task_breakerTaskMilestone').val(),
        id: $('#task_breakerTaskId').val(),
        priority: $('select[name="task_breaker-task-edit-priority"]').val(),
        user_id_collection: $('select#task-user-assigned-edit').val(),
        file_attachments: taskbreaker_file_attachments.attached_files
    }

    $.ajax({

        url: ajaxurl,
        data: httpRequestParameters,

        method: 'post',

        success: function( response ) {

            var message = "<p class='task-breaker-message success'>Task successfully updated <a href='#tasks/view/" + response.id + "'>&#65515; View</a></p>";

            if ( 'fail' === response.message && 'no_changes' !== response.type ) {

                message = "<p class='task-breaker-message danger'>There was an error updating the task. All fields are required.</a></p>";

            }

            if ( 'fail' === response.message && 'unauthorized' === response.type ) {

                message = "<p class='task-breaker-message danger'>You are not allowed to modify this task. Only group project administrators and group projects moderators are allowed.</a></p>";

            }

            $('#task_breaker-edit-task-message').html( message ).show();

            element.attr('disabled', false);

            element.text('Update Task');

            $('html, body').animate({
                scrollTop: $("#task_breaker-edit-task-message").offset().top - 300
            }, 100);

            return;

        },

        error: function() {

            // Todo: Better handling of http errors and timeouts.
            console.log('An Error Occured [task_breaker.js]#311');

            return;
        }
    });
}); // end $('#task_breaker-edit-btn').click()

/**
 * Attach event to file attachment. When changed upload the file to user logged in '/tmp' directory.
 * @return void
 */
$('#task-breaker-form-file-attachment-edit-field').on( 'change', function( event ) {
    console.log('test');
    var form_attr = {
        'edit_file_attachment': 'yes'
    };
    taskbreaker_process_file_attachment( event, 'taskbreaker-file-attachment-edit', form_attr  );

    return;
});

$('#task_breaker-project').on('click', '#taskbreaker-unlink-file-btn > a', function(e){
    e.preventDefault();
    var __confirm = confirm("Are you sure you want to delete this file attachment? This process is not reversible.");
        if ( __confirm ) {
            console.log('deleting file...');
        }
    var __ticket_id = $('#task_breakerTaskId').val();
    $('.tasbreaker-file-attached').html('Deleting file attachment...');
    $.ajax({
        method: 'POST',
        url: task_breakerAjaxUrl,
        data: {
            nonce: task_breakerProjectSettings.nonce,
            ticket_id: __ticket_id,
            action: 'task_breaker_transactions_request',
            method: 'task_breaker_transaction_delete_ticket_attachment'
        },
        success: function( response ) {
            $('.tasbreaker-file-attached').html('No files attached');
            $('#taskbreaker-unlink-file-btn > a').remove();
            // Clear the flies
            taskbreaker_file_attachments.attached_files = '';
        }
    });   
});
 // Delete Task Single
 $('body').on('click', '#task_breaker-delete-btn', function() {

    var _delete_confirm = confirm("Are you sure you want to delete this task? This action is irreversible");

    if (!_delete_confirm) {
       return;
    }

    var $element = $(this);

    var task_id = parseInt( ThriveProjectModel.id );

    var task_project_id = parseInt( ThriveProjectModel.project_id );

    var __http_params = {

       action: 'task_breaker_transactions_request',
       method: 'task_breaker_transaction_delete_ticket',
       id: task_id,
       project_id: task_project_id,
       nonce: task_breakerProjectSettings.nonce

   };

   ThriveProjectView.progress(true);

   $element.text('Deleting ...');

   $.ajax({

       url: ajaxurl,
       data: __http_params,
       method: 'post',
       success: function( response ) {

            ThriveProjectView.progress( false );

            ThriveProjectView.updateStats( response.stats );

            if ( 'fail' === response.message) {

                var message = "<p class='task-breaker-message danger'>"+response.message_text+"</p>";
                
                $('#task_breaker-edit-task-message').html( message ).show();

                return false;

            } else {

                location.href = "#tasks";

                ThriveProjectView.switchView(null, '#task_breaker-project-tasks-context');
                
            }

            $element.text('Delete');

       },

       error: function() {

           ThriveProjectView.progress(false);

           $element.text('Delete');

       }
   });
 }); // End Delete Task

  $('body').on('click', '#updateTaskBtn', function() {

      var updateTaskBtn = $(this);

      updateTaskBtn.attr('disabled', 'disabled');

      var comment_ticket_id = ThriveProjectModel.id,
          comment_details = $('#task-comment-content').val(),
          task_priority = $('#task_breaker-task-priority-update-select').val(),
          comment_completed = $('input[name=task_commment_completed]:checked').val(),
          task_project_id = parseInt( ThriveProjectModel.project_id );

      if (0 === comment_ticket_id) {
          return;
      }

      // notify the user when submitting the comment form
      ThriveProjectView.progress(true);

      var __http_params = {
          action: 'task_breaker_transactions_request',
          method: 'task_breaker_transaction_add_comment_to_ticket',
          ticket_id: comment_ticket_id,
          priority: task_priority,
          details: comment_details,
          completed: comment_completed,
          project_id: task_project_id,
          nonce: task_breakerProjectSettings.nonce
      };

      $.ajax({
          url: ajaxurl,
          data: __http_params,
          method: 'post',
          success: function( response ) {

              updateTaskBtn.attr('disabled', false);
              ThriveProjectView.progress( false );

              $('#task-comment-content').val('');
              $('#task-lists').append(response.result);


              if ("yes" === comment_completed) {

                  // disable old radios
                  $('#ticketStatusInProgress').attr('disabled', true).attr('checked', false);
                  $('#ticketStatusComplete').attr('disabled', true).attr('checked', false);
                  $('#comment-completed-radio').addClass('hide');
                  // enable new radios
                  $('#ticketStatusCompleteUpdate').attr('disabled', false).attr('checked', true);
                  $('#ticketStatusReOpenUpdate').attr('disabled', false);
                  $('#task_breaker-comment-completed-radio').removeClass('hide');

              }

              if ( "reopen" === comment_completed ) {

                  // Enable old radios
                  $('#ticketStatusInProgress').attr('disabled', false).attr('checked', true);
                  $('#ticketStatusComplete').attr('disabled', false).attr('checked', false);
                  $('#comment-completed-radio').removeClass('hide');
                  // Disable new radios
                  $('#ticketStatusCompleteUpdate').attr('disabled', true).attr('checked', false);
                  $('#ticketStatusReOpenUpdate').attr('disabled', true);
                  $('#task_breaker-comment-completed-radio').addClass('hide');

              }
              // console.log(response.stats);
              ThriveProjectView.updateStats( response.stats );
          },
          error: function() {
              updateTaskBtn.attr('disabled', false);
              ThriveProjectView.progress(false);
          }
      });
  }); // end UpdateTask

// Delete Comment Event.
$('body').on('click', 'a.task_breaker-delete-comment', function(e) {

    e.preventDefault();

    // Ask the user to confirm if he/she really wanted to delete the task comment.
    var confirm_delete = confirm("Are you sure you want to delete this comment? This action is irreversible. ");

    // Exit if the user decided to cancel the task comment.
    if (!confirm_delete) {
        return false;
    }

    var $element = $(this);

    var comment_ticket = parseInt($(this).attr('data-comment-id'));

    var __http_params = {
        action: 'task_breaker_transactions_request',
        method: 'task_breaker_transaction_delete_comment',
        comment_id: comment_ticket,
        nonce: task_breakerProjectSettings.nonce
    };

    // Send request to server to delete the comment.
    ThriveProjectView.progress(true);

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( response ) {

            ThriveProjectView.progress(false);

            if (response.message == 'success') {

                $element.parent().parent().parent().parent().fadeOut(function() {
                    $(this).remove();
                });

            } else {

                this.error();

            }
        },
        error: function() {
            ThriveProjectView.progress(false);
            $element.parent().append('<p class="error">Transaction Error: There was an error trying to delete this comment.</p>');
        }
    });
}); // end Delete Comment

/**
 * Add new project script
 *
 * @Todo: Current handle for adding project is inside archive.js
 */

// Update Project
$('body').on('click', '#task_breakerUpdateProjectBtn', function() {

    var element = $(this);

    var projectContent = "";

    var __projectContentObj = tinymce.get( 'task_breakerProjectContent' );

        if ( __projectContentObj ) {

            projectContent = __projectContentObj.getContent();

        } else {

            projectContent = $('#task_breakerProjectContent').val();

        }

    var __http_params = {
        action: 'task_breaker_transactions_request',
        method: 'task_breaker_transactions_update_project',
        id: parseInt( $('#task_breaker-project-id').val() ),
        title: $( '#task_breaker-project-name' ).val(),
        content: projectContent,
        group_id: parseInt( $('select[name=task_breaker-project-assigned-group]').val() ),
        nonce: task_breakerProjectSettings.nonce
    };

    element.attr('disabled', true).text('Updating ...');

    ThriveProjectView.progress(true);

    $('.task_breaker-project-updated').remove();

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( response ) {

            ThriveProjectView.progress(false);

            element.attr('disabled', false).text('Update Project');

            if (response.message === 'success') {

                // Update the project title.
                $('article .entry-header > .entry-title').text($('#task_breaker-project-name').val());

                element.parent().parent().prepend(
                    '<div id="message" class="task_breaker-project-updated success updated">' +
                    '<p>Project details successfully updated.</p>' +
                    '</div>'
                );

                location.reload();

            } else {

                if ("authentication_error" === response.type ) {

                    element.parent().parent().prepend(
                        '<div id="message" class="task_breaker-project-updated error updated">' +
                        '<p>Only group administrators and moderators can update the project settings.</p>' +
                        '</div>'
                    );

                } else {

                    element.parent().parent().prepend(
                        '<div id="message" class="task_breaker-project-updated success updated">' +
                        '<p>There was an error saving the project. All fields are required.</p>' +
                        '</div>'
                    );

                }

            }

            ThriveProjectView.progress(false);

            setTimeout(function() {

                $('.task_breaker-project-updated').fadeOut();

            }, 3000);

            return;

        },

        error: function() {

            alert('connection failure');
            return;

        }
    });
}); // Project Update End.

 $('body').on('click', '#task_breakerDeleteProjectBtn', function() {


     if ( !confirm('Are you sure you want to delete this project? All the tickets under this project will be deleted as well. This action cannot be undone.')) {
         return;
     }

     var project_id = $('#task_breaker-project-id').val();

     var __http_params = {
         action: 'task_breaker_transactions_request',
         method: 'task_breaker_transactions_delete_project',
         id: project_id,
         nonce: task_breakerProjectSettings.nonce
     };

     $(this).text('Deleting...');

     $.ajax({

         url: ajaxurl,

         method: 'post',

         data: __http_params,

         success: function( response ) {

             if (response.message == 'success') {

                 window.location = response.redirect;

             } else {

                this.error();

             }

             return;

         },

         error: function() {

            alert('There was an error trying to delete this post. Try again later.');

         }
     });

 });

}); // end $(window).load();
}); // end jQuery(document).ready();
