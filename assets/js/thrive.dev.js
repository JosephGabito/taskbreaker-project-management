jQuery(document).ready(function($) {

	'use strict'

	$(window).load( function() { 

var __ThriveProjectModel = Backbone.View.extend({
    id: 0,
    project_id: thriveProjectSettings.project_id,
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
        "click .thrive-project-tab-li-item-a": "switchView",
        "click .next-page": "next",
        "click .prev-page": "prev",
        "click #thrive-task-search-submit": "searchTasks",
        "change #thrive-task-filter-select": "filter"
    },

    switchView: function(e, elementID) {

        $('#thrive-project-edit-tab').css('display', 'none');
        $('#thrive-project-add-new').css('display', 'none');

        $('.thrive-project-tab-li-item').removeClass('active');
        $('.thrive-project-tab-content-item').removeClass('active');

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
        $('#thrive-tasks-filter').hide();
    },

    showFilters: function() {
        $('#thrive-tasks-filter').show();
    },

    searchTasks: function() {
        
        var keywords = $('#thrive-task-search-field').val();

        if ( 0 === keywords.length ) {
            location.href = '#tasks';
        } else {
            location.href = '#tasks/search/' + encodeURI(keywords);
        }

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
        this.template = 'thrive_ticket_single';
        // load the task
        this.renderTask(function( httpResponse ) {

            __this.progress( false );
            var response = JSON.parse( httpResponse );

            if (response.html) {
                $('#thrive-project-tasks').html(response.html);
            }
        });
    },

    showEditForm: function(task_id) {

        this.progress(true);
        var __this = this;

        var __taskEditor = tinymce.get('thriveTaskEditDescription');

        if ( __taskEditor ) {
            __taskEditor.setContent( '' );
        } else {
            $( '#thriveTaskEditDescription' ).val( '' );
        }

        $('.thrive-project-tab-content-item').removeClass('active');
        $('.thrive-project-tab-li-item').removeClass('active');
        $('a#thrive-project-edit-tab').css('display', 'block').parent().addClass('active');
        $('#thrive-project-edit-context').addClass('active');

        $('#thriveTaskId').attr('disabled', true).val('loading...');
        $('#thriveTaskEditTitle').attr('disabled', true).val('loading...');
        $("#thrive-task-edit-select-id").attr('disabled', true);

        this.model.id = task_id;

        // Render the task.
        this.renderTask( function( httpResponse ) {

            __this.progress( false );

            var response = JSON.parse( httpResponse );

            if ( response.task ) {
                
                var task = response.task;

                var taskEditor = tinymce.get('thriveTaskEditDescription');

                $('#thriveTaskId').val(task.id).removeAttr("disabled");
                $('#thriveTaskEditTitle').val(task.title).removeAttr("disabled");
                
                if ( taskEditor ) {
                    taskEditor.setContent( task.description );
                } else {
                    $( '#thriveTaskEditDescription' ).val( task.description );
                }

                $( "#thrive-task-edit-select-id" ).val( task.priority ).change().removeAttr("disabled");

            }

            return;
            
        });

    },

    renderTask: function(__callback) {
        $.ajax({
            url: ajaxurl,
            method: 'get',
            data: {
                action: 'thrive_transactions_request',
                method: 'thrive_transaction_fetch_task',
                id: this.model.id,
                template: this.template,
                nonce: thriveProjectSettings.nonce
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
            data: {
                action: 'thrive_transactions_request',
                method: 'thrive_transaction_fetch_task',
                id: this.model.id,
                project_id: this.model.project_id,
                page: this.model.page,
                search: this.search,
                priority: this.model.priority,
                template: 'thrive_the_tasks',
                show_completed: this.model.show_completed,
                nonce: thriveProjectSettings.nonce
            },
            success: function( httpResponse ) {

                __this.progress(false);

                var response = JSON.parse( httpResponse );

                if (response.message == 'success') {
                    if (response.task.stats) {
                        // update model max_page and min_page
                        ThriveProjectModel.max_page = response.task.stats.max_page;
                        ThriveProjectModel.min_page = response.task.stats.min_page;
                    }
                    // render the result
                    $('#thrive-project-tasks').html(response.html);
                }

                if (0 === response.task.length) {
                    $('#thrive-project-tasks').html('<div class="error" id="message"><p>No tasks found. If you\'re trying to find a task, kindly try different keywords and/or filters.</p></div>');
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

        $('#thrive-preloader').css({
            display: __display
        });

        $('#thrive-project-tasks').css({
            opacity: __opacity
        });

        return;
    },

    updateStats: function( stats ) {

        $( '.thrive-total-tasks' ).text( stats.total );
        $( '.thrive-remaining-tasks-count' ).text( stats.remaining );
        $( '.task-progress-completed' ).text( stats.completed );
        $( '.task-progress-percentage-label > span' ).text( stats.progress );

        // Update the progress bar css width.
        $( '.task-progress-percentage' ).css({
            width: Math.ceil( ( ( stats.completed / stats.total ) * 100 ) ) + '%'
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

        this.view.switchView(null, '#thrive-project-tasks-context');

        this.model.page = 1;
        this.model.id = 0;
        this.model.show_completed = 'no';

        this.view.search = '';
        this.view.render();
    },

    dashboard: function() {
        this.view.switchView(null, '#thrive-project-dashboard-context');
    },
    settings: function() {
        this.view.switchView(null, '#thrive-project-settings-context');
    },
    add: function() {
        this.view.switchView(null, '#thrive-project-add-new-context');
        $('#thrive-project-add-new').css('display', 'block');
        
        if ( tinymce.editors.thriveTaskDescription ) {
            tinymce.editors.thriveTaskDescription.setContent('');
        }
    },
    completed_tasks: function() {

        this.view.switchView(null, '#thrive-project-tasks-context');

        this.model.show_completed = 'yes';
        this.view.render();
    },
    edit: function(task_id) {
        this.view.showEditForm(task_id);
        $('#thrive-edit-task-message').html('');
    },
    next: function(page) {
        this.model.page = page;
        this.view.render();
    },
    view_task: function(task_id) {
        this.model.id = task_id;
        this.view.single(task_id);
        this.view.switchView(null, '#thrive-project-tasks-context');
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

$('#thrive-submit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";
    var __taskEditor = tinymce.get( 'thriveTaskDescription' );

    if ( __taskEditor ) {
       taskDescription =  __taskEditor.getContent();
    } else {
       taskDescription = $( '#thriveTaskDescription' ).val();
    }

    $.ajax({
        url: ajaxurl,
        data: {
            
            action: 'thrive_transactions_request',
            method: 'thrive_transaction_add_ticket',
            
            description: taskDescription,
            
            title: $('#thriveTaskTitle').val(),
            milestone_id: $('#thriveTaskMilestone').val(),
            priority: $('#thrive-task-priority-select').val(),

            nonce: thriveProjectSettings.nonce,

            project_id: thriveTaskConfig.currentProjectId,
            user_id: thriveTaskConfig.currentUserId
        },

        method: 'post',

        success: function( message ) {

            // Total tasks view.
            var total_tasks = parseInt( $('.thrive-total-tasks').text().trim() );

            // Remaining tasks view
            var remaining_tasks = parseInt( $('.thrive-remaining-tasks-count').text().trim() );

            message = JSON.parse( message );

           // console.log( message ); 

            if ( message.message === 'success' ) {

                element.text('Save Task');

                element.removeAttr('disabled');

                $('#thriveTaskDescription').val('');

                $('#thriveTaskTitle').val('');
                
                ThriveProjectView.updateStats( message.stats );

                location.href = "#tasks/view/" + message.response.id;


            } else {

                $('#thrive-add-task-message').html('<p class="error">'+message.response+'</p>').show().addClass('error');

              

                element.text('Save Task');
                
                element.removeAttr('disabled');

            }
        },
        error: function() {

        }
    }); // end $.ajax
}); // end $('#thrive-submit-btn').click()

$('#thrive-edit-btn').click(function(e) {

    e.preventDefault();

    var element = $(this);

    element.attr('disabled', true);
    element.text('Loading ...');

    var taskDescription = "";
    var taskDescriptionObject = tinymce.get( 'thriveTaskEditDescription' );

    if ( taskDescriptionObject ) {
        taskDescription = taskDescriptionObject.getContent();
    } else {
        taskDescription = $('#thriveTaskEditDescription').val();
    }

    $.ajax({

        url: ajaxurl,
        data: {

            description: taskDescription,
            nonce: thriveProjectSettings.nonce,
            project_id: thriveTaskConfig.currentProjectId,
            user_id: thriveTaskConfig.currentUserId,

            action: 'thrive_transactions_request',
            method: 'thrive_transaction_edit_ticket',

            title: $('#thriveTaskEditTitle').val(),
            milestone_id: $('#thriveTaskMilestone').val(),
            id: $('#thriveTaskId').val(),
            priority: $('select[name="thrive-task-edit-priority"]').val()

        }, 

        method: 'post',

        success: function( httpResponse ) {

            var response = JSON.parse( httpResponse );

            var message = "<p class='success'>Task successfully updated <a href='#tasks/view/" + response.id + "'>&#65515; View</a></p>";

            if ('fail' === response.message && 'no_changes' !== response.type) {

                message = "<p class='error'>There was an error updating the task. All fields are required.</a></p>";

            }
 
            $('#thrive-edit-task-message').html(message).show();

            element.attr('disabled', false);

            element.text('Update Task');

            return;

        },
        
        error: function() {

            // Todo: Better handling of http errors and timeouts.
            console.log('An Error Occured [thrive.js]#311');

            return;
        }
    });
}); // end $('#thrive-edit-btn').click()

 // Delete Task Single
 $('body').on('click', '#thrive-delete-btn', function() {

    var _delete_confirm = confirm("Are you sure you want to delete this task? This action is irreversible");

    if (!_delete_confirm) {
       return;
    }

    var $element = $(this);

    var task_id = parseInt( ThriveProjectModel.id );

    var task_project_id = parseInt( ThriveProjectModel.project_id );

    var __http_params = {

       action: 'thrive_transactions_request',
       method: 'thrive_transaction_delete_ticket',
       id: task_id,
       project_id: task_project_id,
       nonce: thriveProjectSettings.nonce

   };

   ThriveProjectView.progress(true);

   $element.text('Deleting ...');

   $.ajax({

       url: ajaxurl,
       data: __http_params,
       method: 'post',
       success: function( httpResponse ) {
            
            var response = JSON.parse( httpResponse );
           
            ThriveProjectView.progress(false);

            ThriveProjectView.updateStats( response.stats );

            location.href = "#tasks";

            ThriveProjectView.switchView(null, '#thrive-project-tasks-context');

            $element.text('Delete');

       },

       error: function() {
           ThriveProjectView.progress(false);
           location.href = "#tasks";
           ThriveProjectView.switchView(null, '#thrive-project-tasks-context');
           $element.text('Delete');

       }
   });
 }); // End Delete Task

  $('body').on('click', '#updateTaskBtn', function() {

      var comment_ticket = ThriveProjectModel.id,
          comment_details = $('#task-comment-content').val(),
          task_priority = $('#thrive-task-priority-update-select').val(),
          comment_completed = $('input[name=task_commment_completed]:checked').val(),
          task_project_id = parseInt( ThriveProjectModel.project_id );

      if (0 === comment_ticket) {
          return;
      }

      if (0 === comment_details.length) {
          return;
      }

      // notify the user when submitting the comment form
      ThriveProjectView.progress(true);

      var __http_params = {
          action: 'thrive_transactions_request',
          method: 'thrive_transaction_add_comment_to_ticket',
          ticket_id: comment_ticket,
          priority: task_priority,
          details: comment_details,
          completed: comment_completed,
          project_id: task_project_id,
          nonce: thriveProjectSettings.nonce
      };

      $.ajax({
          url: ajaxurl,
          data: __http_params,
          method: 'post',
          success: function( httpResponse ) {

              var response = JSON.parse( httpResponse );

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
                  $('#thrive-comment-completed-radio').removeClass('hide');

              }

              if ( "reopen" === comment_completed ) {

                  // Enable old radios
                  $('#ticketStatusInProgress').attr('disabled', false).attr('checked', true);
                  $('#ticketStatusComplete').attr('disabled', false).attr('checked', false);
                  $('#comment-completed-radio').removeClass('hide');
                  // Disable new radios
                  $('#ticketStatusCompleteUpdate').attr('disabled', true).attr('checked', false);
                  $('#ticketStatusReOpenUpdate').attr('disabled', true);
                  $('#thrive-comment-completed-radio').addClass('hide');

              }

             // console.log(response.stats);

              ThriveProjectView.updateStats( response.stats );
              
          },
          error: function() {

              ThriveProjectView.progress(false);
          }
      });
  }); // end UpdateTask

// Delete Comment Event.
$('body').on('click', 'a.thrive-delete-comment', function(e) {

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
        action: 'thrive_transactions_request',
        method: 'thrive_transaction_delete_comment',
        comment_id: comment_ticket,
        nonce: thriveProjectSettings.nonce
    };

    // Send request to server to delete the comment.
    ThriveProjectView.progress(true);

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( httpResponse ) {

            ThriveProjectView.progress(false);

            var response = JSON.parse( httpResponse );

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

// Update Project
$('body').on('click', '#thriveUpdateProjectBtn', function() {

    var element = $(this);

    var projectContent = "";

    var __projectContentObj = tinymce.get( 'thriveProjectContent' );

        if ( __projectContentObj ) {

            projectContent = __projectContentObj.getContent();

        } else {

            projectContent = $('#thriveProjectContent').val();

        }

    var __http_params = {
        action: 'thrive_transactions_request',
        method: 'thrive_transactions_update_project',
        id: parseInt( $('#thrive-project-id').val() ),
        title: $( '#thrive-project-name' ).val(),
        content: projectContent,
        group_id: parseInt( $('select[name=thrive-project-assigned-group]').val() ),
        nonce: thriveProjectSettings.nonce
    };

    element.attr('disabled', true).text('Updating ...');

    ThriveProjectView.progress(true);

    $('.thrive-project-updated').remove();

    $.ajax({
        url: ajaxurl,
        data: __http_params,
        method: 'post',
        success: function( httpResponse ) {

            var response = JSON.parse( httpResponse );

            ThriveProjectView.progress(false);

            element.attr('disabled', false).text('Update Project');

            if (response.message === 'success') {

                // Update the project title.
                $('article .entry-header > .entry-title').text($('#thrive-project-name').val());

                element.parent().parent().prepend(
                    '<div id="message" class="thrive-project-updated success updated">' +
                    '<p>Project details successfully updated.</p>' +
                    '</div>'
                );

            } else {

                element.parent().parent().prepend(
                    '<div id="message" class="thrive-project-updated success updated">' +
                    '<p>There was an error saving the project. All fields are required.</p>' +
                    '</div>'
                );

            }

            ThriveProjectView.progress(false);

            setTimeout(function() {

                $('.thrive-project-updated').fadeOut();

            }, 3000);

            return;

        },

        error: function() {

            alert('connection failure');
            return;

        }
    });
}); // Project Update End.

 $('body').on('click', '#thriveDeleteProjectBtn', function() {


     if ( !confirm('Are you sure you want to delete this project? All the tickets under this project will be deleted as well. This action cannot be undone.')) {
         return;
     }

     var project_id = $('#thrive-project-id').val();

     var __http_params = {
         action: 'thrive_transactions_request',
         method: 'thrive_transactions_delete_project',
         id: project_id,
         nonce: thriveProjectSettings.nonce
     };

     $(this).text('Deleting...');

     $.ajax({
         
         url: ajaxurl,
         
         method: 'post',
         
         data: __http_params,

         success: function( httpResponse ) {

             var response = JSON.parse( httpResponse );

             if (response.message == 'success') {

                 window.location = response.redirect;

             } else {
                 console.log('__success_callback');

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