	<div id="thrive-tasks-filter">
		<div class="clearfix">
			<div class="thrive-tabs-tabs">
				<ul>
				    <li id="thrive-task-list-tab" class="thrive-task-tabs active"><a href="#tasks"><span class="dashicons dashicons-list-view"></span> Tasks List</a></li>
				    <li id="thrive-task-completed-tab" class="thrive-task-tabs"><a href="#tasks/completed"><span class="dashicons dashicons-yes"></span> Completed</a></li>
				    <li id="thrive-task-add-tab" class="thrive-task-tabs"><a href="#tasks/add"><span class="dashicons dashicons-plus"></span> New Task</a></li>
				    <li id="thrive-task-edit-tab" class="thrive-task-tabs hidden" style="display: none;"><a href="#thrive-edit-task">Edit Task</a></li>
				</ul>
			</div>
		</div>
		<div class="clearfix">
			<div class="alignleft">
				<select name="thrive-task-filter-select-action" id="thrive-task-filter-select">
					<option value="-1" selected="selected"><?php _e('Show All', 'thrive'); ?></option>
					<option value="1"><?php _e('Normal Priority', 'thrive'); ?></option>
					<option value="2"><?php _e('High Priority', 'thrive'); ?></option>
					<option value="3"><?php _e('Critical Priority', 'thrive'); ?></option>
				</select>
			</div><!--.alignleft actions bulkactions-->

			<div class="alignright">
				<p class="thrive-search-box">
					<label class="screen-reader-text">
						<?php _e('Search Tasks:', 'thrive'); ?>
					</label>
					<input maxlength="160" placeholder="<?php _e('Search Task', 'thrive'); ?>" type="search" id="thrive-task-search-field" name="thrive-task-search" value="">
					<input type="button" id="thrive-task-search-submit" class="button" value="<?php _e('Apply', 'thrive'); ?>">
				</p><!--.search box-->
			</div>
		</div>
	</div><!--#thrive-task-filter-->