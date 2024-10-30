<?php
/* Plugin Name: Buddypress User Registration Auto Group
 * Plugin URI: http://sadrul.info
 * Description: This plugin create a new Group when a new user sign up. You can fix group name, group description and group status from admin panel.
 * Version: 1.0
 * Author: sadrul
 * Author URI: http://sadrul.info
 *
 */


define('DEFAULT_GROUP', __('My Group', 'burag'));
define('DEFAULT_GROUP_DESC', __('My Group', 'burag'));
define('DEFAULT_GROUP_STATUS', 'public');

register_activation_hook( __FILE__,'burag_activate');
register_deactivation_hook( __FILE__,'burag_deactivate');
add_action( 'plugins_loaded', 'burag_plugins_pages' );

function burag_activate(){}
function burag_deactivate(){}

function burag_plugins_pages(){
    load_plugin_textdomain( 'burag', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
    add_action('admin_menu', 'burag_create_subpage');
}
  
function burag_create_subpage(){
	add_submenu_page( 'bp-general-settings', __('Auto Group Setting', 'burag'), __('Auto Group Setting', 'burag'), 10,'auto-group-setting', 'burag_auto_group_setting' );
}  

function burag_auto_group_setting(){
	$auto_group_name    = empty($_POST['auto_group_name'])  ? get_option('auto_group_name')   : sanitize_text_field($_POST['auto_group_name']);
	$auto_group_slug    = empty($_POST['auto_group_slug'])  ? get_option('auto_group_slug')   : sanitize_text_field($_POST['auto_group_slug']);
	$auto_group_desc    = empty($_POST['auto_group_desc'])  ? get_option('auto_group_desc')   : sanitize_text_field($_POST['auto_group_desc']);
	$auto_group_status  = empty($_POST['auto_group_status'])? get_option('auto_group_status') : sanitize_text_field($_POST['auto_group_status']);
	burag_theme_setting_save();
	?>


    <div class="wrap">
        <h2><?php _e('Auto Group Setting', 'burag'); ?></h2><?php _e('(When new users sign up, a new group will be created with the following credentials. The new user will be the "Group Admin" of this group.)', 'burag'); ?>

        <form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" name="auto_group_form">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Auto Group Name', 'burag'); ?></th>
                    <td><input type="text" id="auto_group_name" name="auto_group_name" value="<?php if(!empty($auto_group_name)){echo $auto_group_name;} else {echo DEFAULT_GROUP;} ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Auto Group Description', 'burag'); ?></th>
                    <td><textarea id="auto_group_desc" name="auto_group_desc"><?php if(!empty($auto_group_desc)){echo $auto_group_desc;} else {echo DEFAULT_GROUP_DESC;} ?></textarea></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Auto Group Status', 'burag'); ?></th>
                    <td>
                        <select id="auto_group_status" name="auto_group_status">
                            <option value="public" <?php if($auto_group_status == 'public') echo 'selected'; ?> > <?php _e('public', 'burag'); ?> </option>
                            <option value="private" <?php if($auto_group_status == 'private') echo 'selected'; ?> > <?php _e('private', 'burag'); ?> </option>
                        </select>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>

	
<?php	
}

function burag_theme_setting_save(){
	
$auto_group_name    = get_option('auto_group_name');
$auto_group_slug    = get_option('auto_group_slug');
$auto_group_desc    = get_option('auto_group_desc');
$auto_group_status  = get_option('auto_group_status');

	if(!empty($_POST['submit'])) {			
		empty($auto_group_name)  ? add_option('auto_group_name', sanitize_text_field($_POST['auto_group_name']))     : update_option('auto_group_name', sanitize_text_field($_POST['auto_group_name']));
		empty($auto_group_desc)  ? add_option('auto_group_desc', sanitize_text_field($_POST['auto_group_desc']))     : update_option('auto_group_desc', sanitize_text_field($_POST['auto_group_desc']));
		empty($auto_group_status)? add_option('auto_group_status', sanitize_text_field($_POST['auto_group_status'])) : update_option('auto_group_status', sanitize_text_field($_POST['auto_group_status']));
	}
}


/*-----------------------------------------------
   Group create when new user sign up
------------------------------------------------ */

//Automatically add new users to a group
function burag_automatic_group_membership( $user_id ) {

		$auto_group_name          = get_option('auto_group_name');
		$auto_group_desc          = get_option('auto_group_desc');
		$auto_group_status        = get_option('auto_group_status');
		empty($auto_group_name)   ? $set_group_name   = DEFAULT_GROUP        : $set_group_name   = $auto_group_name;
		empty($auto_group_name)   ? $set_group_slug   = DEFAULT_GROUP        : $set_group_slug   = $auto_group_name;
		empty($auto_group_desc)   ? $set_group_desc   = DEFAULT_GROUP_DESC   : $set_group_desc   = $auto_group_desc;
		empty($auto_group_status) ? $set_group_status = DEFAULT_GROUP_STATUS : $set_group_status = $auto_group_status;
		
		/******** Prepare Group Slug *********/
		$set_group_slug = strtolower($set_group_slug);
	    $set_group_slug = str_replace(' ', '_', $set_group_slug);
	    $set_group_slug = $set_group_slug.'_'.$user_id; 
		
		
		if( !$user_id ) return false;

        // create group
        $new_group = new BP_Groups_Group;
        
        $new_group->creator_id = $user_id;
        $new_group->name = $set_group_name;
        $new_group->slug = $set_group_slug;
        $new_group->description = $set_group_desc;
        $new_group->news = __('whatever', 'burag');
        $new_group->status = $set_group_status;
        $new_group->is_invitation_only = 1;
        $new_group->enable_wire = 1;
        $new_group->enable_forum = 1;
        $new_group->enable_photos = 1;
        $new_group->photos_admin_only = 1;
        $new_group->date_created = current_time('mysql');
        $new_group->total_member_count = 1;
        //$new_group->avatar_thumb = 'some kind of path';
        //$new_group->avatar_full = 'some kind of path';
 
        $new_group -> save();
        
        $group_id = $new_group ->id;
 	
	    groups_update_groupmeta( $group_id, 'total_member_count', 1 );
	    groups_update_groupmeta( $group_id, 'last_activity', time() );
	    groups_update_groupmeta( $group_id, 'invite_status', 'members' );
	    //groups_update_groupmeta( $group_id, 'theme', 'buddypress' );
	    //groups_update_groupmeta( $group_id, 'stylesheet', 'buddypress' );

		// auto membership
		groups_accept_invite( $user_id, $group_id);
		
		global $wpdb, $bp;
		$sql = "UPDATE {$bp->groups->table_name_members} SET is_admin = 1, user_title = 'Group Admin' WHERE user_id  = {$user_id} AND group_id = {$group_id} ";
	    $wpdb->query($sql);
		
	
}
add_action( 'bp_core_activated_user', 'burag_automatic_group_membership' );

?>