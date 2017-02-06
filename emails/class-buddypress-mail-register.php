<?php
/**
 * BuddyPress Mail Register
 *
 * @since   1.3.2
 * @package task-breaker\emails\class-buddypress-mail-register.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

final class Task_Breaker_BP_Mail_Register {

	public function __construct() {

		// Register the new task email template.
		add_action( 'bp_core_install_emails', array( $this, 'new_task_email_template' ) );

		// Register our new email post type and template.
		add_action( 'bp_core_install_emails', array( $this, 'new_task_comment_email_template' ) );

		// Register the new task mailer.
		add_action( 'tb_new_task', array( $this, 'tb_new_task' ), 10, 1);

		// Register the task comment mailer.
		add_action( 'tb_new_task_comment', array( $this, 'tb_new_task_comment' ), 10, 1 );

		// Register email settings under 'Settings' > 'Email'.
		add_action( 'bp_notification_settings', array( $this, 'tb_render_task_email_settings', ), 0 );

		return $this;

	}

	function new_task_email_template() {

		if ( ! function_exists( 'bp_get_email_post_type' ) ) {
			return;
		}

		// Do not create if it already exists and is not in the trash.
		$post_exists = post_exists( '[{{{site.name}}}] There is a new task assigned to you.' );

		if ( 0 === $post_exists && 'publish' === get_post_status( $post_exists ) ) {

			return;

		}

		$email_content = '';
		$email_content .= "There is a new task assigned to you.\n";
		$email_content .= "\n<a href=\"{{{task.url}}}\">Take me to my new task</a>";

		// Create post object.
		$task_update_email_post = array(
			 'post_title' => __( '[{{{site.name}}}] There is a new task waiting for you.', 'task_breaker' ),
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
			   $tt_ids = wp_set_object_terms( $post_id, 'user_assigned_a_task', bp_get_email_tax_type() );

				foreach ( $tt_ids as $tt_id ) {

				$term = get_term_by( 'term_taxonomy_id', absint( $tt_id ), bp_get_email_tax_type() );

				wp_update_term(
					absint( $term->term_id ), bp_get_email_tax_type(), array(
					'description' => __( 'A member of a project received a new ask.', 'task_breaker' ),
					)
				);

			}
		}
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

	/**
	 * Sends email to users when there is a new task assigned to them.
	 * @param  mixed $task Contains the email tokens that we should passed to.
	 * @return void
	 */
	function tb_new_task( $task ) {

		// Bail out if notifications are disabled or BP version does not support e-mail api.
		if ( ! function_exists( 'bp_send_email' ) ) {
			return;
		}

		// Add the tokens to parse in the email template.
		$args = array(
				'tokens' => array(
						'site.name' => get_bloginfo( 'name' ),
						'task.url' => $task->task_url
					)
			);

		if ( ! empty( $task->task_assigned_members ) ) {

			foreach( $task->task_assigned_members as $user_assigned_id ) {

				$user_id = absint( $user_assigned_id );

                if ( ! empty ( $user_id ) ) {

                    $user_subscribed = bp_get_user_meta( absint( $user_id ), 'task_breaker_task_new', true );

                    // Do not send email to self.
                    if ( $user_id !== bp_loggedin_user_id() ) {

                        // New users who have not yet save the settings in the email settings.
                        // have blank value. It means, it is not either 'yes' or 'no'.
                        if ( ! $user_subscribed ) {

                            $user_subscribed = 'yes';

                        }

                        if ( 'yes' === $user_subscribed ) {

                            bp_send_email( 'user_assigned_a_task', (int) $user_id, $args );

                        }
                    }
                }
			}
		}
		

	}
	/**
	 * Sends email to user assigned when there is a new comment
	 * @param  mixed $task_comment Callback parameter for 'tb_new_task_comment' action
	 * @return void
	 */
	function tb_new_task_comment( $task_comment ) {
		
		// Bail out if notifications are disabled or BP version does not support e-mail api.
		if ( ! function_exists( 'bp_send_email' ) ) {
			return;
		}

		// Add tokens to parse in email.
		$args = array(
		        'tokens' => array(
		        'site.name' => get_bloginfo( 'name' ),
		        'task.url' => $task_comment->task_url,
		        'user.url' => $task_comment->user_url,
		        'user.display_name' => $task_comment->user_display_name,
		  	),
		);

        if ( !empty( $task_comment->user_assigned ) ) {

            foreach ( $task_comment->user_assigned as $user_assigned ) {

                // Send args and user ID to receive email.
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
		
		include TASKBREAKER_DIRECTORY_PATH . 'templates/email-notifications-settings.php';

		return;
	} // End function tb_render_task_email_settings().
} // End class 'Task_Breaker_BP_Mail_Register'.

$Task_Breaker_BP_Mail_Register = new Task_Breaker_BP_Mail_Register();
