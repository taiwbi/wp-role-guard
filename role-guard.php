<?php
/*
* Plugin Name: Role Guard Content Shield
* Description: This plugin restricts access to specific content based on user roles.
* Author: taiwbi
* Version: 1.0
* Author URI: https://taiwbi.com
* License: GPLv2 or later
*/

function rgsc_meta_box()
{
  add_meta_box('rgsc_user', 'User Requirement', 'rgsc_meta_show', 'post', 'normal', 'high', 'rgsc_meta_show');
}
add_action('add_meta_boxes', 'rgsc_meta_box');

function rgsc_meta_show(WP_Post $post)
{
  // Add a nonce field
  wp_nonce_field('rgsc_meta', 'rgsc_meta_show');

  $value = get_post_meta($post->ID, 'rgsc_user', true);


  echo '<label for="rgsc_user">Minimum User Role</label>';
  ?>
  <select id="rgsc_user" name="rgsc_user">
  <option value="everyone" default>Everyone</option>
  <option value="subscriber">Subscriber</option>
  <option value="contributor">Contributor</option>
  <option value="author">Author</option>
  <option value="editor">Editor</option>
  <option value="administrator">Administrator</option>
  </select>
  <script>
  let rgsc_user = "<?php echo $value ?>"
  document.querySelectorAll("#rgsc_user > option").forEach((element) => {
    element.selected = false;
  });
  document.querySelector("#rgsc_user option[value='" + rgsc_user +"']").selected = true;
  </script>
  <?php
}

function rgsc_save_meta($post_id)
{
  if (!isset($_POST['rgsc_user'])) {
    $_POST['rgsc_user'] = 'subscriber';
  }

  $value = isset($_POST['rgsc_user']) ? $_POST['rgsc_user'] : '';

  $a = update_post_meta($post_id, 'rgsc_user', $value);
}
add_action('save_post', 'rgsc_save_meta');

function rgsc_restrict_content($content)
{
  global $post;
  $required_role = get_post_meta($post->ID, 'rgsc_user', true);
  switch ($required_role) {
  case 'everyone':
    return $content; 
  case 'subscriber':
    if (current_user_can('read')) {
      return $content;
    }
  case 'contributor':
    if (current_user_can('edit_posts')) {
      return $content;
    }
  case 'author':
    if (current_user_can('delete_published_posts')) {
      return $content;
    }
  case 'editor':
    if (current_user_can('moderate_comments')) {
      return $content;
    }
  case 'administrator':
    if (current_user_can('list_users')) {
      return $content;
    }
  default:
    return "Sorry, You don't have access to this content";
  }
}
add_filter('the_content', 'rgsc_restrict_content');
