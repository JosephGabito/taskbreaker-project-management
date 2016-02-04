<div class="task-progress">

    <div class="task-progress-bar">
        <div class="task-progress-percentage" style="width:<?php echo absint( $tasks_progress ); ?>%;">
            <div class="task-progress-task-count-wrap">
                <div class="task-progress-task-count">
                    <?php
                        printf( _n( '%s Task', '%s Tasks', $tasks_total, 'task_breaker' ), '<span class="task_breaker-total-tasks">'. $tasks_total .'</span>' );
                    ?>
                </div>
            </div>
            <div class="task-progress-percentage-label">
                <span>
                    <?php echo absint( $tasks_progress ); ?>%
                    <?php _e( 'Completed', 'task_breaker' ); ?>
                </span>
            </div>
        </div>
    </div>
</div>
