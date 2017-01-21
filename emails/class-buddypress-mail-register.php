<?php
/**
 * BuddyPress Mail Register
 *
 * @since  1.3.2
 * @package task-breaker\emails\class-buddypress-mail-register.php
 */

if ( ! defined( 'ABSPATH' ) ) { 
  exit; 
}

final class TaskBreakerBPMailRegister {

  public function __construct() {

    add_action( 'bp_core_install_emails', array( $this, 'new_task_comment_email_template' ) );

    add_action( 'tb_new_task_comment', array( $this, 'tb_new_task_comment' ), 10, 1 );

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
 
    if ( $post_exists != 0 && get_post_status( $post_exists ) == 'publish' )
       return;
    
    $email_content = "";

    $email_content .= "<a href=\"{{{user.url}}}\">{{user.display_name}}</a> has posted a new update in the task.\n";
    
    $email_content .= "\n<a href=\"{{{task.url}}}\">Go to the task discussion</a> to reply or catch up on the conversation.";

    // Create post object.
    $task_update_email_post = array(
      'post_title'    => __( '[{{{site.name}}}] New task update.', 'task_breaker' ),
      'post_content'  => $email_content,  // HTML email content.
      'post_excerpt'  => $email_content,  // Plain text email content.
      'post_status'   => 'publish',
      'post_type' => bp_get_email_post_type() // this is the post type for emails
    );
 
    // Insert the email post into the database.
    $post_id = wp_insert_post( $task_update_email_post );
 
    if ( $post_id ) {

        // add our email to the taxonomy term 'post_received_comment'
        // Email is a custom post type, therefore use wp_set_object_terms
        $tt_ids = wp_set_object_terms( $post_id, 'task_received_an_update', bp_get_email_tax_type() );

        foreach ( $tt_ids as $tt_id ) {

            $term = get_term_by( 'term_taxonomy_id', (int) $tt_id, bp_get_email_tax_type() );

            wp_update_term( (int) $term->term_id, bp_get_email_tax_type(), array(
                'description' => __( 'A member of a project updated a task.', 'task_breaker' ),
            ));

        }
    }
 
  }

  // @todo send email to all assigned members.
  function tb_new_task_comment( $task_comment_object ) {
      // add tokens to parse in email
      $args = array(
          'tokens' => array(
              'site.name' => get_bloginfo( 'name' ),
              'task.url' => $task_comment_object->task_url,
              'user.url' => $task_comment_object->user_url,
              'user.display_name' => $task_comment_object->user_display_name,
          ),
      );
      // send args and user ID to receive email
      bp_send_email( 'task_received_an_update', (int) 1, $args );
  }

}

$TaskBreakerBPMailRegister = new TaskBreakerBPMailRegister();