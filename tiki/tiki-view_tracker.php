<?php

// $Header: /cvsroot/tikiwiki/tiki/tiki-view_tracker.php,v 1.47 2004-02-12 13:37:18 mose Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once('tiki-setup.php');

include_once('lib/trackers/trackerlib.php');
include_once('lib/notifications/notificationlib.php');

if ($feature_trackers != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": feature_trackers");
	$smarty->display("error.tpl");
	die;
}

$_REQUEST["itemId"] = 0;
$smarty->assign('itemId', $_REQUEST["itemId"]);

if (!isset($_REQUEST["trackerId"])) {
	$smarty->assign('msg', tra("No tracker indicated"));
	$smarty->display("error.tpl");
	die;
}

$smarty->assign('trackerId', $_REQUEST["trackerId"]);
$smarty->assign('individual', 'n');

if ($userlib->object_has_one_permission($_REQUEST["trackerId"], 'tracker')) {
	$smarty->assign('individual', 'y');
	if ($tiki_p_admin != 'y') {
		$perms = $userlib->get_permissions(0, -1, 'permName_desc', '', 'trackers');
		foreach ($perms["data"] as $perm) {
			$permName = $perm["permName"];
			if ($userlib->object_has_permission($user, $_REQUEST["trackerId"], 'tracker', $permName)) {
				$$permName = 'y';
				$smarty->assign("$permName", 'y');
			} else {
				$$permName = 'n';
				$smarty->assign("$permName", 'n');
			}
		}
	}
}

if ($tiki_p_view_trackers != 'y') {
	$smarty->assign('msg', tra("You dont have permission to use this feature"));
	$smarty->display("error.tpl");
	die;
}

if (isset($_REQUEST['vals']) and is_array($_REQUEST['vals'])) {
	$defaultvalues = $_REQUEST['vals'];
	setcookie("activeTabs".urlencode(substr($_SERVER["REQUEST_URI"],1)),"tab2");
} else {
	$defaultvalues = array();
}
$smarty->assign('defaultvalues', $defaultvalues);

$tracker_info = $trklib->get_tracker($_REQUEST["trackerId"]);
$tracker_info = array_merge($tracker_info,$trklib->get_tracker_options($_REQUEST["trackerId"]));
$smarty->assign('tracker_info', $tracker_info);

$field_types = $trklib->field_types();
$smarty->assign('field_types', $field_types);

$status_types = array();
$status_raw = $trklib->status_types();

if (isset($_REQUEST['status'])) {
	$sts = preg_split('//', $_REQUEST['status'], -1, PREG_SPLIT_NO_EMPTY);
} elseif (isset($tracker_info["defaultStatus"])) {
	$sts = preg_split('//', $tracker_info["defaultStatus"], -1, PREG_SPLIT_NO_EMPTY);
} else {
	$sts = array('o');
}

foreach ($status_raw as $let=>$sta) {
	if (isset($$sta['perm']) and $$sta['perm'] == 'y') {
		if (in_array($let,$sts)) {
			$sta['class'] = 'statuson';
			$sta['statuslink'] = str_replace($let,'',implode('',$sts));
		} else {
			$sta['class'] = 'statusoff';
			$sta['statuslink'] = implode('',$sts).$let;
		}
		$status_types["$let"] = $sta;
	}
}

$smarty->assign('status_types', $status_types);

$fields = $trklib->list_tracker_fields($_REQUEST["trackerId"], 0, -1, 'position_asc', '');
$ins_fields = $fields;

$mainfield = '';
$mainfieldId = 0;
$orderkey = false;
$usecategs = false;
$ins_categs = array();
$listfields = array();
$textarea_options = false;

for ($i = 0; $i < count($fields["data"]); $i++) {
	$fid = $fields["data"][$i]["fieldId"];
	
	$ins_id = 'ins_' . $fid;
	$fields["data"][$i]["ins_id"] = $ins_id;
	$fields["data"][$i]["id"] = $fid;
	
	$filter_id = 'filter_' . $fid;
	$fields["data"][$i]["filter_id"] = $filter_id;
	
	if (!isset($mainfield) and $fields["data"][$i]['isMain'] == 'y') {
		$mainfield = $fields["data"][$i]["name"];
		$mainfieldId = $fid;
	}

	if (isset($tracker_info['defaultOrderKey']) and $tracker_info['defaultOrderKey'] == $fields["data"][$i]['name']) {
		$orderkey = true;
	}
	if ($fields["data"][$i]['isTblVisible'] == 'y' and ($fields["data"][$i]['isPublic'] == 'y' or $tiki_p_admin_trackers == 'y')) {
		$listfields[$fid]['type'] = $fields["data"][$i]["type"];
		$listfields[$fid]['name'] = $fields["data"][$i]["name"];
		$listfields[$fid]['options'] = $fields["data"][$i]["options"];
		$listfields[$fid]['isMain'] = $fields["data"][$i]["isMain"];
	}
	
	if ($fields["data"][$i]["type"] == 'f') {
		$fields["data"][$i]["value"] = '';
		$ins_fields["data"][$i]["value"] = '';
		if (isset($_REQUEST["$ins_id" . "Day"])) {
			$ins_fields["data"][$i]["value"] = mktime($_REQUEST["$ins_id" . "Hour"], $_REQUEST["$ins_id" . "Minute"],
			0, $_REQUEST["$ins_id" . "Month"], $_REQUEST["$ins_id" . "Day"], $_REQUEST["$ins_id" . "Year"]);
		} else {
			$ins_fields["data"][$i]["value"] = date("U");
		}
	
	} elseif ($fields["data"][$i]["type"] == 'e') {
		include_once('lib/categories/categlib.php');
		$k = $fields["data"][$i]["options"];
		$fields["data"][$i]["$k"] = $categlib->get_child_categories($k);
		$categId = "ins_cat_$k";
		if (isset($_REQUEST[$categId]) and is_array($_REQUEST[$categId])) {
			$ins_categs = array_merge($ins_categs,$_REQUEST[$categId]);
		}
		$ins_fields["data"][$i]["value"] = '';

	} elseif ($fields["data"][$i]["type"] == 'u') {
		if (isset($_REQUEST["$ins_id"])) {
			$ins_fields["data"][$i]["value"] = $_REQUEST["$ins_id"];
		} else {
			if ($fields["data"][$i]["options"] == 1 and $user) {
				$ins_fields["data"][$i]["value"] = $user;
			} else {
				$ins_fields["data"][$i]["value"] = '';
			}
		}
		if (isset($_REQUEST["$filter_id"])) {
			$fields["data"][$i]["value"] = $_REQUEST["$filter_id"];
		} elseif ($user) {
			$fields["data"][$i]["value"] = $user;
		} else {
			$fields["data"][$i]["value"] = '';
		}

	} elseif ($fields["data"][$i]["type"] == 'g') {
		if ( $fields["data"][$i]["options"] == 1 and $group) {
			$ins_fields["data"][$i]["value"] = $group;
		} else {
			$ins_fields["data"][$i]["value"] = '';
		}
		if (isset($_REQUEST["$filter_id"])) {
			$fields["data"][$i]["value"] = $_REQUEST["$filter_id"];
		} elseif ($group) {
			$fields["data"][$i]["value"] = $group;
		} else {
			$fields["data"][$i]["value"] = '';
		}

	} elseif ($fields["data"][$i]["type"] == 'c') {
		if (isset($_REQUEST["$ins_id"]) && $_REQUEST["$ins_id"] == 'on') {
			$ins_fields["data"][$i]["value"] = 'y';
		} else {
			$ins_fields["data"][$i]["value"] = 'n';
		}
		if (isset($_REQUEST["$filter_id"])) {
			$fields["data"][$i]["value"] = $_REQUEST["$filter_id"];
		} else {
			$fields["data"][$i]["value"] = '';
		}

	} else {
		if (isset($_REQUEST["$ins_id"])) {
			$ins_fields["data"][$i]["value"] = $_REQUEST["$ins_id"];
		} else {
			$ins_fields["data"][$i]["value"] = '';
		}
		if (isset($_REQUEST["$filter_id"])) {
			$fields["data"][$i]["value"] = $_REQUEST["$filter_id"];
		} else {
			$fields["data"][$i]["value"] = '';
		}
		if ($fields["data"][$i]["type"] == 'a' and $fields["data"][$i]["options_array"][0])	{
			$textarea_options = true;
		} elseif ($fields["data"][$i]["type"] == 'i')	{
			if (isset($_FILES["$ins_id"]) && is_uploaded_file($_FILES["$ins_id"]['tmp_name'])) {
				if (!empty($gal_match_regex)) {
					if (!preg_match("/$gal_match_regex/", $_FILES["$ins_id"]['name'], $reqs)) {
						$smarty->assign('msg', tra('Invalid imagename (using filters for filenames)'));
						$smarty->display("error.tpl");
						die;
					}
				}
				if (!empty($gal_nmatch_regex)) {
					if (preg_match("/$gal_nmatch_regex/", $_FILES["$ins_id"]['name'], $reqs)) {
						$smarty->assign('msg', tra('Invalid imagename (using filters for filenames)'));
						$smarty->display("error.tpl");
						die;
					}
				}
				$type = $_FILES["$ins_id"]['type'];
				$size = $_FILES["$ins_id"]['size'];
				$filename = $_FILES["$ins_id"]['name'];
				$ins_fields["data"][$i]["value"] = $_FILES["$ins_id"]['name'];
				$ins_fields["data"][$i]["file_type"] = $_FILES["$ins_id"]['type'];
				$ins_fields["data"][$i]["file_size"] = $_FILES["$ins_id"]['size'];
			}
		}
	}
}
if (!isset($mainfield) and isset($fields["data"][0]["fieldId"]) and isset($fields["data"][0]["value"])) {
	$mainfield = $fields["data"][0]["value"];
	$mainfieldId = $fields["data"][0]["fieldId"];
}
if ($textarea_options) {
	include_once ('lib/quicktags/quicktagslib.php');
	$quicktags = $quicktagslib->list_quicktags(0,-1,'taglabel_desc','');
	$smarty->assign_by_ref('quicktags', $quicktags["data"]);
}

if (isset($tracker_infos['author']) and $user == $tracker_infos['author']) {
	$tiki_p_modify_tracker_items = 'y';
}

if ($tiki_p_admin_trackers == 'y') {
	if (isset($_REQUEST["remove"])) {
		check_ticket('view-trackers');
		$trklib->remove_tracker_item($_REQUEST["remove"]);
	}
}

$smarty->assign('mail_msg', '');
$smarty->assign('email_mon', '');

if ($user) {
	if (isset($_REQUEST["monitor"])) {
		check_ticket('view-trackers');
		$user_email = $tikilib->get_user_email($user);
		$emails = $notificationlib->get_mail_events('tracker_modified', $_REQUEST["trackerId"]);
		if (in_array($user_email, $emails)) {
			$notificationlib->remove_mail_event('tracker_modified', $_REQUEST["trackerId"], $user_email);
			$mail_msg = tra('Your email address has been removed from the list of addresses monitoring this tracker');
		} else {
			$notificationlib->add_mail_event('tracker_modified', $_REQUEST["trackerId"], $user_email);
			$mail_msg = tra('Your email address has been added to the list of addresses monitoring this tracker');
		}
		$smarty->assign('mail_msg', $mail_msg);
	}
	$user_email = $tikilib->get_user_email($user);
	$emails = $notificationlib->get_mail_events('tracker_modified', $_REQUEST["trackerId"]);
	if (in_array($user_email, $emails)) {
		$smarty->assign('email_mon', tra('Cancel monitoring'));
	} else {
		$smarty->assign('email_mon', tra('Monitor'));
	}
}

if (isset($_REQUEST["save"])) {
	if ($tiki_p_create_tracker_items == 'y') {
		check_ticket('view-trackers');
		if (!isset($tracker_info['newItemStatus'])) $tracker_info['newItemStatus'] = 'o';
		$trklib->replace_item($_REQUEST["trackerId"], $_REQUEST["itemId"], $ins_fields, $tracker_info['newItemStatus']);
		setcookie("activeTabs".urlencode(substr($_SERVER["REQUEST_URI"],1)),"tab1");
		$smarty->assign('itemId', '');
		
		if (count($ins_categs)) {
			$cat_type = "tracker ".$_REQUEST["trackerId"];
			$cat_objid = $_REQUEST["itemId"];
			$cat_desc = "";
			$cat_name = $mainfield;
			$cat_href = "tiki-view_tracker_item.php?trackerId=".$_REQUEST["trackerId"]."&amp;itemId=".$_REQUEST["itemId"];
			$categlib->uncategorize_object($cat_type, $cat_objid);
			foreach ($ins_categs as $cats) {
				$catObjectId = $categlib->is_categorized($cat_type, $cat_objid);
				if (!$catObjectId) {
					$catObjectId = $categlib->add_categorized_object($cat_type, $cat_objid, $cat_desc, $cat_name, $cat_href);
				}
				$categlib->categorize($catObjectId, $cats);
			}
		}
	}
}

$smarty->assign_by_ref('fields', $fields["data"]);

if (!isset($_REQUEST["sort_mode"])) {
	if ($orderkey) {
		$sort_mode = 'f_'.$tracker_info['defaultOrderKey'];
		if (isset($tracker_info['defaultOrderDir'])) {
			$sort_mode.= "_".$tracker_info['defaultOrderDir'];
		} else {
			$sort_mode.= "_asc";
		}
	} else {
		$sort_mode = '';
	}
} else {
	$sort_mode = $_REQUEST["sort_mode"];
}
$smarty->assign_by_ref('sort_mode', $sort_mode);

if (!isset($_REQUEST["offset"])) {
	$offset = 0;
} else {
	$offset = $_REQUEST["offset"];
}

$smarty->assign_by_ref('offset', $offset);

if (isset($_REQUEST["initial"])) {
	$initial = $_REQUEST["initial"];
} else {
	$initial = '';
}
$smarty->assign('initial', $initial);
$smarty->assign('initials', split(' ','a b c d e f g h i j k l m n o p q r s t u v w x y z'));

if (isset($_REQUEST["filterfield"])) {
	$filterfield = $_REQUEST["filterfield"];
} else {
	$filterfield = '';
}
$smarty->assign('filterfield', $filterfield);

if (isset($_REQUEST["filtervalue"])) {
	$filtervalue = $_REQUEST["filtervalue"];
} else {
	$filtervalue = '';
}
$smarty->assign('filtervalue', $filtervalue);


if (!isset($_REQUEST["status"]))
	$_REQUEST["status"] = 'o';

$smarty->assign('status', $_REQUEST["status"]);

$items = $trklib->list_items($_REQUEST["trackerId"], $offset, $maxRecords, $sort_mode, $listfields, $filterfield, $filtervalue, $_REQUEST["status"],$initial);
//var_dump($items);die();

$cant_pages = ceil($items["cant"] / $maxRecords);
$smarty->assign_by_ref('cant_pages', $cant_pages);
$smarty->assign('actual_page', 1 + ($offset / $maxRecords));

if ($items["cant"] > ($offset + $maxRecords)) {
	$smarty->assign('next_offset', $offset + $maxRecords);
} else {
	$smarty->assign('next_offset', -1);
}

// If offset is > 0 then prev_offset
if ($offset > 0) {
	$smarty->assign('prev_offset', $offset - $maxRecords);
} else {
	$smarty->assign('prev_offset', -1);
}

$smarty->assign_by_ref('items', $items["data"]);


$users = $userlib->list_all_users();
$groups = $userlib->list_all_groups();
$smarty->assign('users', $users);
$smarty->assign('groups', $groups);

$section = 'trackers';
include_once('tiki-section_options.php');

$smarty->assign('uses_tabs', 'y');
if ($feature_jscalendar) {
	$smarty->assign('uses_jscalendar', 'y');
}
$smarty->assign('show_filters', 'n');
foreach ($fields['data'] as $it) {
	if ($it['isSearchable'] == 'y' and $it['isTblVisible'] == 'y'){
		$smarty->assign('show_filters', 'y');
		break;
	}
}

ask_ticket('view-trackers');

// Display the template
$smarty->assign('mid', 'tiki-view_tracker.tpl');
$smarty->display("tiki.tpl");

?>
