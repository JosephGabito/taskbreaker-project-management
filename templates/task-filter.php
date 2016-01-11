	<div id="task_breaker-tasks-filter">
		<div class="clearfix">
			<div class="task_breaker-tabs-tabs">
				<ul>
				    <li id="task_breaker-task-list-tab" class="task_breaker-task-tabs active"><a href="#tasks"><span class="dashicons dashicons-list-view"></span> Tasks List</a></li>
				    <li id="task_breaker-task-completed-tab" class="task_breaker-task-tabs"><a href="#tasks/completed"><span class="dashicons dashicons-yes"></span> Completed</a></li>
				    <li id="task_breaker-task-add-tab" class="task_breaker-task-tabs"><a href="#tasks/add"><span class="dashicons dashicons-plus"></span> New Task</a></li>
				    <li id="task_breaker-task-edit-tab" class="task_breaker-task-tabs hidden" style="display: none;"><a href="#task_breaker-edit-task">Edit Task</a></li>
				</ul>
			</div>
		</div>
		<div class="clearfix">
			<div class="alignleft">
				<select name="task_breaker-task-filter-select-action" id="task_breaker-task-filter-select">
					<option value="-1" selected="selected"><?php _e('Show All', 'task_breaker'); ?></option>
					<option value="1"><?php _e('Normal Priority', 'task_breaker'); ?></option>
					<option value="2"><?php _e('High Priority', 'task_breaker'); ?></option>
					<option value="3"><?php _e('Critical Priority', 'task_breaker'); ?></option>
				</select>
			</div><!--.alignleft actions bulkactions-->

			<div class="alignright">
				<p class="task_breaker-search-box">
					<label class="screen-reader-text">
						<?php _e('Search Tasks:', 'task_breaker'); ?>
					</label>
					<input maxlength="160" placeholder="<?php _e('Search Task', 'task_breaker'); ?>" type="search" id="task_breaker-task-search-field" name="task_breaker-task-search" value="">
					<input type="button" id="task_breaker-task-search-submit" class="button" value="<?php _e('Apply', 'task_breaker'); ?>">
				</p><!--.search box-->
			</div>
		</div>
	</div><!--#task_breaker-task-filter-->