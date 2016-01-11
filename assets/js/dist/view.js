var __ThriveProjectView = Backbone.View.extend({

    el: 'body',
    model: ThriveProjectModel,
    search: '',
    template: '',
    events: {
        "click .task_breaker-project-tab-li-item-a": "switchView",
        "click .next-page": "next",
        "click .prev-page": "prev",
        "click #task_breaker-task-search-submit": "searchTasks",
        "change #task_breaker-task-filter-select": "filter"
    },

    switchView: function(e, elementID) {

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

    searchTasks: function() {
        
        var keywords = $('#task_breaker-task-search-field').val();

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
        this.template = 'task_breaker_ticket_single';
        // load the task
        this.renderTask(function( httpResponse ) {

            __this.progress( false );
            var response = JSON.parse( httpResponse );

            if (response.html) {
                $('#task_breaker-project-tasks').html(response.html);
            }
        });
    },

    showEditForm: function(task_id) {

        this.progress(true);
        var __this = this;

        var __taskEditor = tinymce.get('task_breakerTaskEditDescription');

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
        this.renderTask( function( httpResponse ) {

            __this.progress( false );

            var response = JSON.parse( httpResponse );

            if ( response.task ) {
                
                var task = response.task;

                var taskEditor = tinymce.get('task_breakerTaskEditDescription');

                $('#task_breakerTaskId').val(task.id).removeAttr("disabled");
                $('#task_breakerTaskEditTitle').val(task.title).removeAttr("disabled");
                
                if ( taskEditor ) {
                    taskEditor.setContent( task.description );
                } else {
                    $( '#task_breakerTaskEditDescription' ).val( task.description );
                }

                $( "#task_breaker-task-edit-select-id" ).val( task.priority ).change().removeAttr("disabled");

            }

            return;
            
        });

    },

    renderTask: function(__callback) {
        $.ajax({
            url: ajaxurl,
            method: 'get',
            data: {
                action: 'task_breaker_transactions_request',
                method: 'task_breaker_transaction_fetch_task',
                id: this.model.id,
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
            data: {
                action: 'task_breaker_transactions_request',
                method: 'task_breaker_transaction_fetch_task',
                id: this.model.id,
                project_id: this.model.project_id,
                page: this.model.page,
                search: this.search,
                priority: this.model.priority,
                template: 'task_breaker_the_tasks',
                show_completed: this.model.show_completed,
                nonce: task_breakerProjectSettings.nonce
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
                    $('#task_breaker-project-tasks').html(response.html);
                }

                if (0 === response.task.length) {
                    $('#task_breaker-project-tasks').html('<div class="error" id="message"><p>No tasks found. If you\'re trying to find a task, kindly try different keywords and/or filters.</p></div>');
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

        $( '.task_breaker-total-tasks' ).text( stats.total );
        $( '.task_breaker-remaining-tasks-count' ).text( stats.remaining );
        $( '.task-progress-completed' ).text( stats.completed );
        $( '.task-progress-percentage-label > span' ).text( stats.progress );

        // Update the progress bar css width.
        $( '.task-progress-percentage' ).css({
            width: Math.ceil( ( ( stats.completed / stats.total ) * 100 ) ) + '%'
        });
        
    }
});

var ThriveProjectView = new __ThriveProjectView();
