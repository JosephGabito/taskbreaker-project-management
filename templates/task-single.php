<?php if ( empty ( $args ) ) return; ?>

<?php // Only allow members who has an access view to view the task; ?>
<?php if ( ! task_breaker_can_see_project_tasks( $args->project_id ) ) { ?>
    <div id="task_breaker-single-task">
        <p class="info" id="message">
            <?php _e("Unable to access the task details. Only group members can access this page.", "task_breaker"); ?>
        </p>
    </div>
    <?php return; ?>
<?php } ?>

<div id="task_breaker-single-task">

    <div id="task_breaker-single-task-details">

        <?php
            $priority_label = array(
                '1' => __( 'Normal', 'task_breaker' ),
                '2' => __( 'High', 'task_breaker' ),
                '3' => __( 'Critical', 'task_breaker' ),
            );
        ?>

        <div id="task-details-priority" class="task-priority <?php echo sanitize_title( $priority_label[$args->priority] ); ?>">
            <?php echo esc_html( $priority_label[ $args->priority ] ); ?>
        </div>

        <?php if ( 0 != $args->completed_by ) { ?>
            <div id="task-details-status" class="task-status completed">
                <?php esc_html_e( 'Completed', 'task_breaker' ); ?>
            </div>
        <?php } else { ?>
            <div id="task-details-status" class="task-status open">
                <?php esc_html_e( 'Open', 'task_breaker' ); ?>
            </div>
        <?php } ?>
        <h2>
            <?php echo esc_html( $args->title ); ?>
            <span class="clearfix"></span>
        </h2>

        <div class="task-content">
            <?php echo do_shortcode( $args->description ); ?>
        </div>

        <div class="task-content-meta">
            <div class="alignright">
                <a href="#tasks" title="<?php _e( 'Tasks List', 'task_breaker' ); ?>" class="button">
                    <?php _e( '&larr; Tasks List', 'task_breaker' ); ?>
                </a>
                <a href="#tasks/edit/<?php echo intval( $args->id ); ?>" class="button">
                    <?php _e( 'Edit', 'task_breaker' ); ?>
                </a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div><!--#task_breaker-single-task-details-->

    <ul id="task-lists">
        <li class="task_breaker-task-discussion">
            <h3>
                <?php _e( 'Discussion', 'task_breaker' ); ?>
            </h3>
        </li>
        <?php $comments = task_breaker_get_tasks_comments( $args->id ); ?>
        <?php if ( ! empty( $comments ) ) { ?>
            <?php foreach ( $comments as $comment ) { ?>
                <?php echo task_breaker_comments_template( $comment, (array) $args ); ?>
            <?php } ?>
        <?php } ?>

    </ul><!--#task-lists-->

    <?php if ( task_breaker_can_add_task_comment( $args->project_id ) ) { ?>

        <div id="task-editor">
            <div id="task-editor_update-status" class="task_breaker-form-field">
                <?php
                $completed = 'no';
                if ( absint( $args->completed_by ) !== 0 ) {
                    $completed = 'yes';
                }
                ?>
                    <div id="comment-completed-radio">
                        <?php if ( $completed === 'no' ) { ?>
                        <div class="pull-left">
                            <label for="ticketStatusInProgress">
                                <input <?php echo $completed === 'no' ?  'checked': ''; ?> id="ticketStatusInProgress" type="radio" value="no" name="task_commment_completed">
                                <small><?php _e( 'In Progress', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                        <?php } ?>
                        <div class="pull-left">
                            <label for="ticketStatusComplete">
                                <input <?php echo $completed === 'yes' ? 'checked': ''; ?> id="ticketStatusComplete" type="radio" value="yes" name="task_commment_completed">
                                <small><?php _e( 'Completed', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                        <?php if ( $completed === 'yes' ) { ?>
                        <div class="alignleft">
                            <label for="ticketStatusReOpen">
                                <input id="ticketStatusReOpen" type="radio" value="reopen" name="task_commment_completed">
                                <small><?php _e( 'Reopen Task', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                        <?php } ?>
                    </div>
                    <!--On Complete -->
                    <div id="task_breaker-comment-completed-radio" class="hide">
                        <div class="alignleft">
                            <label for="ticketStatusCompleteUpdate">
                                <input disabled id="ticketStatusCompleteUpdate" type="radio" value="yes" name="task_commment_completed">
                                <small><?php _e( 'Completed', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                        <div class="alignleft">
                            <label for="ticketStatusReOpenUpdate">
                                <input disabled id="ticketStatusReOpenUpdate" type="radio" value="reopen" name="task_commment_completed">
                                <small><?php _e( 'Reopen Task', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                    </div>

                    <!-- On ReOpen -->
                    <div id="task_breaker-comment-reopen-radio" class="hide">
                        <div class="alignleft">
                            <label disabled for="ticketStatusReOpenInProgress">
                                <input id="ticketStatusReOpenInProgress" type="radio" value="yes" name="task_commment_completed">
                                <small><?php _e( 'In Progress', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                        <div class="alignleft">
                            <label disabled for="ticketStatusReOpenComplete">
                                <input disabled id="ticketStatusReOpenComplete" type="radio" value="reopen" name="task_commment_completed">
                                <small><?php _e( 'Complete', 'task_breaker' ); ?></small>
                            </label>
                        </div>
                    </div>

                <div class="clearfix"></div>
            </div>

            <div id="task-editor_update-content" class="task_breaker-form-field">
                <textarea id="task-comment-content" rows="5" width="100"></textarea>
            </div>

            <div id="task-editor_update-priority" class="task_breaker-form-field">
                <label for="task_breaker-task-priority-select" class="task_breaker-form-field">
                    <?php _e( 'Update Priority:', 'task_breaker' ); ?>
                    <?php task_breaker_task_priority_select( $select = absint( $args->priority ), $name = 'task_breaker-task-priority-update-select', $id = 'task_breaker-task-priority-update-select' );?>
                </label>
            </div>

            <div id="task-editor_update-submit">
                <button type="button" id="updateTaskBtn" class="button">
                    <?php _e( 'Update Task', 'task_breaker' ); ?>
                </button>
            </div>
        </div><!--#task-editor-->
    <?php } else { ?>
        <div id="task-editor">
            <p class="error" id="message">
                <?php _e('Ops! You cannot add progress to this task', 'task_breaker'); ?>
            </p>
        </div>
    <?php } ?>
</div>
