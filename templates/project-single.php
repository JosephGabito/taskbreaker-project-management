<?php if ( bp_is_active( 'groups' ) ) { ?>
    <div id="task_breaker-project">

        <?php if ( task_breaker_can_view_project( $args->ID ) ) { ?>

            <?php include task_breaker_template_dir(). '/project-heading.php'; ?>

            <div class="task_breaker-project-tabs">

                <ul id="task_breaker-project-tab-li">
                    <li class="task_breaker-project-tab-li-item active">
                        <a data-content="task_breaker-project-dashboard" class="task_breaker-project-tab-li-item-a" href="#tasks/dashboard">
                            <?php esc_html_e('Dashboard', 'task_breaker'); ?>
                        </a>
                    </li>
                    <li class="task_breaker-project-tab-li-item">
                        <a data-content="task_breaker-project-tasks" class="task_breaker-project-tab-li-item-a" href="#tasks">
                            <?php esc_html_e('Tasks', 'task_breaker'); ?>
                        </a>
                    </li>
                    <li class="task_breaker-project-tab-li-item">
                        <a data-content="task_breaker-project-add-new" id="task_breaker-project-add-new" class="task_breaker-project-tab-li-item-a" href="#tasks/add">
                            <?php esc_html_e('Add New', 'task_breaker'); ?>
                        </a>
                    </li>
                    <li class="task_breaker-project-tab-li-item">
                        <a data-content="task_breaker-project-edit" id="task_breaker-project-edit-tab" class="task_breaker-project-tab-li-item-a" href="#">
                            <?php esc_html_e('Edit', 'task_breaker'); ?>
                        </a>
                    </li>
                    <li class="task_breaker-project-tab-li-item">
                        <a data-content="task_breaker-project-settings" class="task_breaker-project-tab-li-item-a" href="#tasks/settings">
                            <?php esc_html_e('Settings', 'task_breaker'); ?>
                        </a>
                    </li>
                </ul>

            </div><!--.task_breaker-project-tabs-->
            <div id="task_breaker-project-tab-content">
                <?php
                    if ( $post->post_type == 'project' ) {
                        include task_breaker_template_dir(). '/project.php';
                    }
                ?>
            </div>
        <?php } else { ?>
            <div class="row">
                <div class="col-xs-1"><i class="material-icons md-36">lock</i></div>
                <div class="col-xs-11">
                    <p>
                        <?php esc_attr_e('This project can only be accessed by group members. Use the button below join the group and receive an access to this project.','task_breaker'); ?>
                    </p>
                </div>
            </div>

            <?php $group_id = absint( get_post_meta( $args->ID, 'task_breaker_project_group_id', true ) ); ?>

            <?php $group = groups_get_group(array('link_class'=>'button','group_id'=> $group_id )); ?>

            <?php $join_link = wp_nonce_url( bp_get_group_permalink( $group ) . 'join', 'groups_join_group' ); ?>

            <a class="button" href="<?php echo esc_url( $join_link ); ?>" title="<?php esc_attr_e('Join Group', 'task_breaker'); ?>">
                <?php esc_attr_e('Join Group', 'task_breaker'); ?>
            </a>

        <?php } ?>
    </div><!--#task_breaker-project-->
<?php } else { ?>
    <p id="message" class="info">
        <?php _e('Please enable BuddyPress Groups Components.', 'task_breaker'); ?>
    </p>
<?php } ?>
