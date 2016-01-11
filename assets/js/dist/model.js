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
