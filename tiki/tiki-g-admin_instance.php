<?php
require_once('tiki-setup.php');
include_once('lib/Galaxia/ProcessManager.php');

if($feature_workflow != 'y') {
  $smarty->assign('msg',tra("This feature is disabled"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}

if($tiki_p_admin_workflow != 'y') {
  $smarty->assign('msg',tra("Permission denied"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}





if(!isset($_REQUEST['iid'])) {
  $smarty->assign('msg',tra("No instance indicated"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}
$smarty->assign('iid',$_REQUEST['iid']);


// Get workitems and list the workitems with an option to edit workitems for
// this instance

if(isset($_REQUEST['save'])) {
  //status, owner
  $instanceManager->set_instance_status($_REQUEST['iid'],$_REQUEST['status']);
  $instanceManager->set_instance_owner($_REQUEST['iid'],$_REQUEST['owner']);
  //y luego acts[activityId][user] para reasignar users

  foreach(array_keys($_REQUEST['acts']) as $act) {
    $instanceManager->set_instance_user($_REQUEST['iid'],$act,$_REQUEST['acts'][$act]);
  }
  
  if($_REQUEST['sendto']) {
    $instanceManager->set_instance_destination($_REQUEST['iid'],$_REQUEST['sendto']);
  }
  //process sendto
}


// Get the instance and set instance information
$ins_info = $instanceManager->get_instance($_REQUEST['iid']);
$smarty->assign_by_ref('ins_info',$ins_info);

// Get the process from the instance and set information
$proc_info = $processManager->get_process($ins_info['pId']);
$smarty->assign_by_ref('proc_info',$proc_info);

// Process activities
$activities  =  $activityManager->list_activities($ins_info['pId'],0,-1,'flowNum_asc','','');
$smarty->assign('activities',$activities['data']);

// Users
$users = $userlib->get_users(0,-1,'login_asc', '');
$smarty->assign_by_ref('users',$users['data']);


$props = $instanceManager->get_instance_properties($_REQUEST['iid']);

if(isset($_REQUEST['unsetprop'])) {
  unset($props[$_REQUEST['unsetprop']]);
  $instanceManager->set_instance_properties($_REQUEST['iid'],$props);  
}

if(!is_array($props)) $props=Array();
$smarty->assign_by_ref('props',$props);
if(isset($_REQUEST['addprop'])) {
	$props[$_REQUEST['name']] = $_REQUEST['value'];
	$instanceManager->set_instance_properties($_REQUEST['iid'],$props);
}


if(isset($_REQUEST['saveprops'])) {
  foreach(array_keys($_REQUEST['props']) as $key) {
    $props[$key]=$_REQUEST['props'][$key];
  }
  $instanceManager->set_instance_properties($_REQUEST['iid'],$props);
}


$acts = $instanceManager->get_instance_activities($_REQUEST['iid']);
$smarty->assign_by_ref('acts',$acts);

$smarty->assign('mid','tiki-g-admin_instance.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>