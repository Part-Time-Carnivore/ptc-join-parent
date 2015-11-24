<?php
/**
 * Plugin Name: PTC Join Parent
 * Description: Enables users to join related groups on registration.
 * Version: 1.0
 * Author: Pete Davis
 */

//** When a user joins a group, this function will automatically join that user to the group's parent group by forcing accept invite. **//
add_action( 'groups_join_group', 'ptc_bp_gh_join_parent', 10, 2);
function ptc_bp_gh_join_parent($group_id, $user_id) {
    global $bp;
	$group = new BP_Groups_Hierarchy($group_id);
	$parent_id = $group->parent_id;
	if ($parent_id) {
		groups_accept_invite($user_id, $parent_id);
	}
}

//** When a user accepts an invitation to a group, this function will automatically join that user to the group's parent group. This function also ensures that the user joins all parent groups to the top of the tree when they join a group. **//
add_action( 'groups_accept_invite', 'ptc_bp_gh_accept_invite_parent', 10, 2);
function ptc_bp_gh_accept_invite_parent( $user_id, $group_id ) {
    global $bp;
	$group = new BP_Groups_Hierarchy($group_id);
	$parent_id = $group->parent_id;
	if ($parent_id) {
		groups_accept_invite($user_id, $parent_id);
	}
}

//** When a group admin adds or changes the parent this function will join all of the users of the group to the parent by forcing accept invite. **//
//** NOTE: This does not work when a dite admin changes the group parent on the group organise page. **//
add_action( 'bp_group_hierarchy_after_save', 'ptc_bp_gh_add_members_to_parent',80 );
function ptc_bp_gh_add_members_to_parent() { 
	global $wpdb, $bp;
    $group_id = $bp->groups->current_group->id;
	if ($group_id) {
		$parent_id = $wpdb->get_var($wpdb->prepare("SELECT DISTINCT g.parent_id FROM {$bp->groups->table_name} g WHERE g.id=%d", $group_id));
		while ($parent_id) {
			$mysql = "SELECT DISTINCT user_id FROM wp_bp_groups_members WHERE group_id=$group_id";
			$results = $wpdb->get_results($mysql);
					foreach ($results as $member) {
						   groups_accept_invite($member->user_id, $parent_id);
			}
			// get the next parent id up the hierarchy
			$parent_id = $wpdb->get_var($wpdb->prepare("SELECT DISTINCT g.parent_id FROM {$bp->groups->table_name} g WHERE g.id=%d", $parent_id));
		}
	}
}