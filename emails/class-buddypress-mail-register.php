<?php
/**
 * BuddyPress Mail Register
 *
 * @since   1.3.2
 * @package task-breaker\emails\class-buddypress-mail-register.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Task_Breaker_BP_Mail_Register {


	public function __construct() {

		// Register our new email post type and template.
		add_action(
			'bp_core_install_emails', array(
			$this,
			'new_task_comment_email_template',
			)
		);

		// Register the task comment template.
		add_action(
			'tb_new_task_comment', array(
			$this,
			'tb_new_task_comment',
			), 10, 1
		);

		// Register email settings under 'Settings' > 'Email'.
		add_action(
			'bp_notification_settings', array(
			$this,
			'tb_render_task_email_settings',
			), 0
		);

		return $this;
	}

	/**
	 * Defines the template for our new email post type.
	 *
	 * @return void
	 */
	function new_task_comment_email_template() {

		if ( ! function_exists( 'bp_get_email_post_type' ) ) {
			return;
		}

		// Do not create if it already exists and is not in the trash.
		$post_exists = post_exists( '[{{{site.name}}}] New task update.' );

		if ( 0 === $post_exists && 'publish' === get_post_status( $post_exists ) ) {

			return;

		}

		$email_content = '';
		$email_content .= "<a href=\"{{{user.url}}}\">{{user.display_name}}</a> has posted a new update in the task.\n";
		$email_content .= "\n<a href=\"{{{task.url}}}\">Go to the task discussion</a> to reply or catch up on the conversation.";

		// Create post object.
		$task_update_email_post = array(
		 'post_title' => __( '[{{{site.name}}}] New task update.', 'task_breaker' ),
		 'post_content' => $email_content, // HTML email content.
		 'post_excerpt' => $email_content, // Plain text email content.
		 'post_status' => 'publish',
		 'post_type' => bp_get_email_post_type(), // This is the post type for emails.
		);

		// Insert the email post into the database.
		$post_id = wp_insert_post( $task_update_email_post );
		if ( $post_id ) {

			   // Add our email to the taxonomy term 'post_received_comment'.
			   // Email is a custom post type, therefore use wp_set_object_terms.
			   $tt_ids = wp_set_object_terms( $post_id, 'task_received_an_update', bp_get_email_tax_type() );

			foreach ( $tt_ids as $tt_id ) {

				$term = get_term_by( 'term_taxonomy_id', absint( $tt_id ), bp_get_email_tax_type() );

				wp_update_term(
					absint( $term->term_id ), bp_get_email_tax_type(), array(
					'description' => __( 'A member of a project updated a task.', 'task_breaker' ),
					)
				);

			}
		}
	}

	// @todo send email to all assigned members.
	function tb_new_task_comment( $task_comment_object ) {

		// Add tokens to parse in email.
		$args = array(
		        'tokens' => array(
		        'site.name' => get_bloginfo( 'name' ),
		        'task.url' => $task_comment_object->task_url,
		        'user.url' => $task_comment_object->user_url,
		        'user.display_name' => $task_comment_object->user_display_name,
		  ),
		);

        if ( !empty( $task_comment_object->user_assigned ) ) {

            foreach ( $task_comment_object->user_assigned as $user_assigned ) {

                // task_breaker_print_r( $user_assigned->member_id );
                // Send args and user ID to receive email.
                // 
                
                $user_id = absint( $user_assigned->member_id );

                if ( ! empty ( $user_id ) ) {

                    $user_subscribed = bp_get_user_meta( absint( $user_id ), 'task_breaker_comment_new', true );

                    // Do not send email to comment owner.
                    if ( $user_id !== bp_loggedin_user_id() ) {

                        // New users who have not yet save the settings in the email settings.
                        // have blank value. It means, it is not either 'yes' or 'no'.
                        if ( ! $user_subscribed ) {

                            $user_subscribed = 'yes';

                        }

                        if ( 'yes' === $user_subscribed ) {

                            bp_send_email( 'task_received_an_update', (int) $user_id, $args );

                        }
                    }
                }
            }
        }
		
	}

	/**
	 * Renders the template for BuddyPress Email Settings
	 *
	 * @return void
	 */
	function tb_render_task_email_settings() {
		?>
	
		<table class="notification-settings" id="friends-notification-settings">

		<thead>
		  
	   <tr>

		 <th class="icon"></th>

		 <th class="title">
		<?php
		esc_html_e( 'Project Management', 'task_breaker' ); ?>
		 </th>

		 <th class="yes">
			<?php
			esc_html_e( 'Yes', 'task_breaker' ); ?>
		 </th>

		 <th class="no">
			<?php
			esc_html_e( 'No', 'task_breaker' ); ?>
		 </th>

		  </tr>

		</thead>

		<tbody>

		  <tr id="friends-notification-settings-request">
			
		 <td></td>
			
		 <td>
			<?php
			esc_html_e( 'A member of the project under the same task added a new update', 'task_breaker' ); ?>
		 </td>

			<?php
			$task_breaker_comment_new = bp_get_user_meta( bp_displayed_user_id(), 'task_breaker_comment_new', true ); ?>

            <?php if ( ! $task_breaker_comment_new ) { ?>
                
                <?php $task_breaker_comment_new = 'yes'; ?>

            <?php } ?>

		 <td class="yes">

		   <input type="radio" name="notifications[task_breaker_comment_new]" 
		   id="task-breaker-comment-new-yes" value="yes" <?php
			checked( $task_breaker_comment_new, 'yes', true ) ?> />

		   <label for="task-breaker-comment-new-yes" class="bp-screen-reader-text">

			<?php
			esc_html_e( 'Yes, send email', 'task_breaker' ); ?>

		   </label>

		 </td>

		 <td class="no">

		   <input type="radio" name="notifications[task_breaker_comment_new]" 
		   id="task-breaker-comment-new-no" value="no" <?php
			checked( $task_breaker_comment_new, 'no', true ) ?> />

		   <label for="task-breaker-comment-new-no" class="bp-screen-reader-text">
			  
			<?php
			esc_html_e( 'No, do not send email', 'task_breaker' ); ?>

		   </label>

		 </td>

		  </tr>


		  <tr id="friends-notification-settings-accepted">

		 <td></td>
			
		 <td>
			<?php
			esc_html_e( 'A new task is assigned to me', 'task_breaker' ); ?>
		 </td>

			<?php
			$task_breaker_task_new = bp_get_user_meta( bp_displayed_user_id(), 'task_breaker_task_new', true ); ?>

            <?php if ( ! $task_breaker_task_new ) { ?>
                
                <?php $task_breaker_task_new = 'yes'; ?>

            <?php } ?>
			
		 <td class="yes">

		   <input type="radio" name="notifications[task_breaker_task_new]" 
		   id="task-breaker-task-new-yes" value="yes" <?php
			checked( $task_breaker_task_new, 'yes', true ) ?> />

		   <label for="task-breaker-task-new-yes" class="bp-screen-reader-text">

			<?php
			esc_html_e( 'Yes, send email', 'task_breaker' ); ?>

		   </label>

		 </td>

		 <td class="no">

		   <input type="radio" name="notifications[task_breaker_task_new]" 
		   id="task_breaker_task_new_no" value="no" <?php
			checked( $task_breaker_task_new, 'no', true ) ?> />

		   <label for="notification-friends-friendship-accepted-no" class="bp-screen-reader-text">

			<?php
			esc_html_e( 'No, do not send email', 'task_breaker' ); ?>

		   </label>
		 </td>

		  </tr>
		  
		</tbody>
		
		</table>
		<?php
	} // End function tb_render_task_email_settings().
} // End class 'Task_Breaker_BP_Mail_Register'.

$Task_Breaker_BP_Mail_Register = new Task_Breaker_BP_Mail_Register();
