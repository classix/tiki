<?php

// $Id: /cvsroot/tikiwiki/tiki/tiki-upload_file.php,v 1.65.2.4 2008-03-11
// 15:17:54 nyloth Exp $

// Copyright (c) 2002-2007, Luis Argerich, Garland Foster, Eduardo Polidor, et.
// al.
// All Rights Reserved. See copyright.txt for details and a complete list of
// authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. 
// See license.txt for details.

$section = 'file_galleries';
require_once ('tiki-setup.php');
include_once ('lib/categories/categlib.php');
include_once ('lib/filegals/filegallib.php');

include('lib/filegals/max_upload_size.php');
@ini_set('max_execution_time', 0); //will not work in safe_mode is on

function print_progress($msg) {
	global $prefs;

	if ($prefs['javascript_enabled'] == 'y') {
		echo $msg;
		ob_flush();
	}
}

function print_msg($msg,$id) {
	global $prefs;

	if ($prefs['javascript_enabled'] == 'y') {
		echo "<script type='text/javascript'><!--//--><![CDATA[//><!--\n";
		echo "parent.progress('$id','".htmlentities($msg,ENT_QUOTES,"UTF-8")."')\n";
		echo "//--><!]]></script>\n";
		ob_flush();
	}
}

if ($prefs['feature_file_galleries'] != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": feature_file_galleries");
	$smarty->display("error.tpl");
	die;
}

if (!empty($_REQUEST['fileId'])) {
	if (!($fileInfo = $filegallib->get_file_info($_REQUEST['fileId']))) {
		$smarty->assign('msg', tra("Incorrect param"));
		$smarty->display('error.tpl');
		die;
	}
	if (empty($_REQUEST['galleryId'][0])) {
		$_REQUEST['galleryId'][0] = $fileInfo['galleryId'];
	} elseif ($_REQUEST['galleryId'][0] != $fileInfo['galleryId']) {
		$smarty->assign('msg', tra("Could not find the file requested"));
		$smarty->display('error.tpl');
		die;
	}
}	

if (isset($_REQUEST['galleryId'][0])) {
	$gal_info = $tikilib->get_file_gallery((int)$_REQUEST['galleryId'][0]);
	$tikilib->get_perm_object($_REQUEST["galleryId"], 'file gallery', $gal_info, true);
}

if (!empty($_REQUEST['galleryId'][0]) && empty($_REQUEST['fileId']) && $tiki_p_upload_files != 'y' && $tiki_p_admin_file_galleries != 'y') {
	$smarty->assign('errortype', 401);
	$smarty->assign('msg', tra("Permission denied you can upload files but not to this file gallery"));
	$smarty->display('error.tpl');
	die;
}
if (!empty($_REQUEST['fileId'])) {
	if (!empty($fileInfo['lockedby']) && $fileInfo['lockedby'] != $user && $tiki_p_admin_file_galleries != 'y') { // if locked must be the locker
		$smarty->assign('msg', tra(sprintf('The file is locked by %s', $fileInfo['lockedby'])));
		$smarty->display('error.tpl');
		die;
	}
	if (!((!empty($user) && ($user == $fileInfo['user'] || $user == $fileInfo['lockedby'])) || $tiki_p_edit_gallery_file == 'y')) {// must be the owner or the locker or have the perms
		$smarty->assign('errortype', 401);
		$smarty->assign('msg', tra("Permission denied you can edit this file"));
		$smarty->display('error.tpl');
		die;
	}
	if (isset($_REQUEST['lockedby']) && $fileInfo['lockedby'] != $_REQUEST['lockedby']) {
		if (empty($fileInfo['lockedby'])) {
			$smarty->assign('msg', tra(sprintf('The file has been unlocked meanwhile')));
		} else {			
			$smarty->assign('msg', tra(sprintf('The file is locked by %s', $fileInfo['lockedby'])));
		}
		$smarty->display('error.tpl');
		die;
	}
	if ($gal_info['lockable'] == 'y' && empty($fileInfo['lockedby']) && $tiki_p_admin_file_galleries != 'y') {
		$smarty->assign('msg', tra('You must lock the file before editing it'));
		$smarty->display('error.tpl');
		die;
	}
}

$foo = parse_url($_SERVER["REQUEST_URI"]);
$foo1 = str_replace("tiki-upload_file", "tiki-download_file", $foo["path"]);
$smarty->assign('url_browse', $tikilib->httpPrefix(). $foo1);
$url_browse = $tikilib->httpPrefix(). $foo1;

// create direct download path for podcasts
$podcast_url = str_replace("tiki-upload_file.php", "", $foo["path"]);
$podcast_url = $tikilib->httpPrefix().$podcast_url.$prefs['fgal_podcast_dir'];

if (!isset($_REQUEST["description"]))
$_REQUEST["description"] = '';
if (!isset($_REQUEST['author']))
$_REQUEST['author'] = '';
if (isset($_REQUEST['hit_limit']))
$_REQUEST['hit_limit'] = (int) $_REQUEST['hit_limit'];
else
$_REQUEST['hit_limit'] = 0;

$smarty->assign('show', 'n');

if (isset($_REQUEST['fileId']))
$editFileId = $_REQUEST['fileId'];
$editFile = false;
if (!empty($editFileId)) {
	if (!empty($_REQUEST['name'][0]))
		$fileInfo['name']=$_REQUEST['name'][0];
	if (!empty($_REQUEST['description'][0]))
		$fileInfo['description']=$_REQUEST['description'][0];
	if (!empty($_REQUEST['user'][0]))
		$fileInfo['user']=$_REQUEST['user'][0];
	if (!empty($_REQUEST['author'][0]))
		$fileInfo['author']=$_REQUEST['author'][0];

	$smarty->assign_by_ref('fileInfo',$fileInfo);
	$smarty->assign('editFileId',$editFileId);
	$editFile = true;
}

if (!empty($_REQUEST['galleryId'][0])) {
	//$gal_info = $tikilib->get_file_gallery((int)$_REQUEST["galleryId"]);
	$smarty->assign_by_ref('gal_info', $gal_info);
	$podCastGallery = $filegallib->isPodCastGallery((int)$_REQUEST["galleryId"][0], $gal_info);
}

// Process an upload here
if (isset($_REQUEST["upload"])) {
	check_ticket('upload-file');

	//print_progress('<script type="text/javascript" src="lib/tiki-js.js"></script>');

	$error_msg = '';
	$errors = array();
	$uploads = array();
	$batch_job = false;

	$didFileReplace = false;
	foreach($_FILES["userfile"]["error"] as $key => $error) {
		if ($prefs['javascript_enabled'] == 'y') {
			print_progress('<?xml version="1.0" encoding="UTF-8"?>');
		}
		$formId = $_REQUEST['formId'];
		$smarty->assign("FormId",$_REQUEST['formId']);
		if (empty($_REQUEST['galleryId'][$key])) continue;
		if (!isset($_REQUEST['comment'][$key]))
			$_REQUEST['comment'][$key] = '';
		// We process here file uploads
		if (!empty($_FILES["userfile"]["name"][$key])) {
			// Were there any problems with the upload?  If so, report here.
			if (!is_uploaded_file($_FILES["userfile"]["tmp_name"][$key])) {
				$errors[] = tra('Upload was not successful').': '.$tikilib->uploaded_file_error($error);
				continue;
			}
			// Check the name
			if (!empty($prefs['fgal_match_regex'])) {
				if (!preg_match('/'.$prefs['fgal_match_regex'].'/', $_FILES["userfile"]['name'][$key], $reqs)) {
					$errors[] = tra('Invalid filename (using filters for filenames)'). ': ' . $_FILES["userfile"]["name"][$key];
					continue;
				}
			}

			if (!empty($prefs['fgal_nmatch_regex'])) {
				if (preg_match('/'.$prefs['fgal_nmatch_regex'].'/', $_FILES["userfile"]["name"][$key], $reqs)) {
					$errors[] = tra('Invalid filename (using filters for filenames)'). ': ' . $_FILES["userfile"]["name"][$key];
					continue ;
				}
			}

			$name = $_FILES["userfile"]["name"][$key];

			if (isset($_REQUEST["isbatch"][$key]) && $_REQUEST["isbatch"][$key] == 'on' && strtolower(substr($name, strlen($name) - 3)) == 'zip') {
				if ($tiki_p_batch_upload_files == 'y') {
					$filegallib->process_batch_file_upload($_REQUEST["galleryId"][$key], $_FILES["userfile"]['tmp_name'][$key], $user, $_REQUEST["description"][$key]);
					$batch_job = true;
					$batch_job_galleryId = $_REQUEST["galleryId"][$key];
					print_msg(tra('Batch file processed')." $name <br/>",$formId);
					continue;
				} else {
					$errors[] = tra('No permission to upload zipped file packages');
					print_msg(tra('No permission to upload zipped file packages')."<br/>",$formId);
					continue;
				}
			}

			$file_name = $_FILES["userfile"]["name"][$key];
			$file_tmp_name = $_FILES["userfile"]["tmp_name"][$key];
			$tmp_dest = $prefs['tmpDir'] . "/" . $file_name.".tmp";
			if (!move_uploaded_file($file_tmp_name, $tmp_dest)) {
				$errors[] = tra('Errors detected');
				print_msg(tra('Errors detected'),$formId);
				continue;
			}

			$fp = fopen($tmp_dest, "rb");

			if (!$fp) {
				$errors[] = tra('Cannot read file:').' '.$tmp_dest;
				print_msg(tra('Cannot read file:').' '.$tmp_dest,$formId);
			}

			$data = '';
			$fhash = '';

			if (($prefs['fgal_use_db'] == 'n') || ($podCastGallery)) {
				$fhash = md5($name = $_FILES["userfile"]['name'][$key]);
				$fhash = md5(uniqid($fhash));

				// for podcast galleries add the extension so the
				// file can be called directly if name is known,
				if ($podCastGallery) {
					$path_parts = pathinfo($_FILES["userfile"]['name'][$key]);
					if (in_array(strtolower($path_parts["extension"]),array("m4a", "mp3", "mov", "mp4", "m4v", "pdf"))) {
						$fhash .= ".".strtolower($path_parts["extension"]);
					}
					$savedir=$prefs['fgal_podcast_dir'];
				} else {
					$savedir=$prefs['fgal_use_dir'];
				}
				@$fw = fopen($savedir . $fhash, "wb");
				if (!$fw) {
					$errors[] = tra('Cannot write to this file:').$savedir.$fhash;
					print_msg(tra('Cannot write to this file:').$savedir.$fhash,$formId);
				}
			}

			while (!feof($fp)) {
				if (($prefs['fgal_use_db'] == 'y') && (!$podCastGallery)) {
					$data .= fread($fp, 8192 * 16);
				} else {
					if (($data = fread($fp, 8192 * 16)) === false) {
						$errors[] = tra('Cannot read the file:').' '.$tmp_dest;
						print_msg(tra('Cannot read the file:').' '.$tmp_dest,$formId);
					}
					fwrite($fw, $data);
				}
			}

			fclose ($fp);
			// remove file after copying it to the right location or database
			@unlink ($tmp_dest);

			if (($prefs['fgal_use_db'] == 'n') || ($podCastGallery)) {
				fclose ($fw);
				$data = '';
			}

			$size = $_FILES["userfile"]['size'][$key];
			$name = stripslashes($_FILES["userfile"]['name'][$key]);

			$type = $_FILES["userfile"]['type'][$key];

			if (preg_match('/.flv$/',$name)) { $type="video/x-flv"; }

			if (count($errors)) {
				continue;
			}

			if (!$size) {
				$errors[] = tra('Warning: Empty file:').'  '.$name.'. '.tra('Please re-upload your file');
				print_msg(tra('Warning: Empty file:').'  '.$name.'. '.tra('Please re-upload your file'),$formId);
			}
			if (($prefs['fgal_use_db'] == 'y') && (!$podCastGallery)) {
				if (!isset($data) || strlen($data) < 1) {
					$errors[] = tra('Warning: Empty file:'). ' ' . $name.'. '.tra('Please re-upload your file');
					print_msg(tra('Warning: Empty file:'). ' ' . $name.'. '.tra('Please re-upload your file'),$formId);
				}
			}

			if (!isset($_REQUEST['name'][$key]))
				$_REQUEST['name'][$key] = $name;
			if (empty($_REQUEST['user'][$key]))
				$_REQUEST['user'][$key] = $user;

			$fileInfo['filename'] = $file_name;

			if (isset($data)) {
				if ($editFile) {
					$didFileReplace = true;
					$fileId = $filegallib->replace_file($editFileId, $_REQUEST["name"][$key], $_REQUEST["description"][$key], $name, $data, $size, $type, $_REQUEST['user'][$key], $fhash, $_REQUEST['comment'][$key], $gal_info, $didFileReplace, $_REQUEST['author'][$key], $fileInfo['lastModif'], $fileInfo['lockedby']);
					if( $prefs['fgal_limit_hits_per_file'] == 'y' ) {
						$filegallib->set_download_limit( $editFileId, $_REQUEST['hit_limit'][$key] );
					}
				} else {
					$fileId= $filegallib->insert_file($_REQUEST["galleryId"][$key], $_REQUEST["name"][$key], $_REQUEST["description"][$key], $name, $data, $size, $type, $_REQUEST['user'][$key], $fhash, '', $_REQUEST['author'][$key]);
				}

				if (!$fileId) {
					$errors[] = tra('Upload was not successful. Duplicate file content'). ': ' . $name;
					print_msg(tra('Upload was not successful. Duplicate file content'). ': ' . $name,$formId);
					if (($prefs['fgal_use_db'] == 'n') || ($podCastGallery)) {
						@unlink($savedir . $fhash);
					}
				}

				if( $prefs['fgal_limit_hits_per_file'] == 'y' ) {
					$filegallib->set_download_limit( $fileId, $_REQUEST['hit_limit'][$key] );
				}

				if (count($errors) == 0) {
					$aux['name'] = $name;
					$aux['size'] = $size;
					$aux['fileId'] = $fileId;
					if ($podCastGallery) {
						$aux['dllink'] = $podcast_url.$fhash;
					} else {
						$aux['dllink'] = $url_browse."?fileId=".$fileId;
					}
					$uploads[] = $aux;
					$cat_type = 'file';
					$cat_objid = $fileId;
					$cat_desc = substr($_REQUEST["description"][$key], 0, 200);
					$cat_name =  empty($_REQUEST['name'][$key])? $name: $_REQUEST['name'][$key];
					$cat_href = $aux['dllink'];
					include_once ('categorize.php');
					// Print progress
					if ($prefs['javascript_enabled'] == 'y') {
						$smarty->assign("name",$aux['name']);
						$smarty->assign("size",$aux['size']);
						$smarty->assign("fileId",$aux['fileId']);
						$smarty->assign("dllink",$aux['dllink']);
						$smarty->assign("nextFormId",$_REQUEST['formId']+1);
						print_progress($smarty->fetch("tiki-upload_file_progress.tpl"));
					}
				}
			}
		}
	}

	if ($editFile && !$didFileReplace) {
		$filegallib->replace_file($editFileId, $_REQUEST['name'][0], $_REQUEST['description'][0], $fileInfo['filename'], $fileInfo['data'], $fileInfo['filesize'], $fileInfo['filetype'], $fileInfo['user'], $fileInfo['path'], $_REQUEST['comment'][0], $gal_info, $didFileReplace, $_REQUEST['author'][0], $fileInfo['lastModif'], $fileInfo['lockedby']);
		$fileChangedMessage = tra('File update was successful').': '.$_REQUEST['name'];
		$smarty->assign('fileChangedMessage',$fileChangedMessage);
		$cat_type = 'file';
		$cat_objid = $editFileId;
		$cat_desc = substr($_REQUEST["description"][0], 0, 200);
		$cat_name = empty($fileInfo['name'])?$fileInfo['filename']: $fileInfo['name'];
		$cat_href = $podCastGallery?$podcast_url.$fhash: "$url_browse?fileId=".$editFileId; 
		if( $prefs['fgal_limit_hits_per_file'] == 'y' ) {
			$filegallib->set_download_limit( $editFileId, $_REQUEST['hit_limit'][0] );
		}
		include_once ('categorize.php');
	}

	$smarty->assign('errors', $errors);
	$smarty->assign('uploads', $uploads);
	
	if ($batch_job and count($errors) == 0) {
		header ("location: tiki-list_file_gallery.php?galleryId=" . $batch_job_galleryId);
		die;
	}
	if ($editFileId and count($errors) == 0) {
		header ("location: tiki-list_file_gallery.php?galleryId=" . $_REQUEST["galleryId"][$key]);
		die;
	}
}

// Get the list of galleries to display the select box in the template
if (isset($_REQUEST["galleryId"]) and is_int($_REQUEST["galleryId"])) {
	$smarty->assign('galleryId', $_REQUEST["galleryId"]);
} elseif (isset($_REQUEST["galleryId"][0])) {
	$smarty->assign('galleryId', $_REQUEST["galleryId"][0]);
} else {
	$smarty->assign('galleryId', '');
}

if (empty($_REQUEST['fileId'])) {
	if ($tiki_p_admin_file_galleries != 'y') {
		$galleries = $tikilib->list_visible_file_galleries(0, -1, 'name_asc', $user, '');
	} else {
		$galleries = $filegallib->list_file_galleries(0, -1, 'name_asc', $user, '');
	}
	$temp_max = count($galleries["data"]);
	for ($i = 0; $i < $temp_max; $i++) {
		if ($userlib->object_has_one_permission($galleries["data"][$i]["galleryId"], 'file gallery')) {
			$galleries["data"][$i]["individual"] = 'y';
			if ($userlib->object_has_permission($user, $galleries["data"][$i]["galleryId"], 'file gallery', 'tiki_p_upload_files')) {
				$galleries["data"][$i]["individual_tiki_p_upload_files"] = 'y';
			} else {
				$galleries["data"][$i]["individual_tiki_p_upload_files"] = 'n';
			}
			if ($tiki_p_admin == 'y' || $userlib->object_has_permission($user, $galleries["data"][$i]["galleryId"], 'file gallery',	'tiki_p_admin_file_galleries')) {
				$galleries["data"][$i]["individual_tiki_p_upload_files"] = 'y';
			}
		} else {
			$galleries["data"][$i]["individual"] = 'n';
		}
	}
	$smarty->assign_by_ref('galleries', $galleries["data"]);
}

if ($tiki_p_admin_file_galleries == 'y' || $tiki_p_admin == 'y') {
	$users = $tikilib->list_users(0, -1, 'login_asc');
	$smarty->assign_by_ref('users', $users['data']);
}

if( $prefs['fgal_limit_hits_per_file'] == 'y' ) {
	$smarty->assign( 'hit_limit', $filegallib->get_download_limit( $_REQUEST['fileId'] ) );
}

$cat_type = 'file';
$cat_objid = empty($_REQUEST['fileId'])? 0: $_REQUEST['fileId'];
include_once('categorize_list.php');

include_once ('tiki-section_options.php');

ask_ticket('upload-file');

// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');

// Display the template
if ($prefs['javascript_enabled'] != 'y' or !isset($_REQUEST["upload"])) {
	$smarty->assign('mid','tiki-upload_file.tpl');
	if ( isset($_REQUEST['filegals_manager']) && $_REQUEST['filegals_manager'] != '' ) {
		$smarty->assign('filegals_manager', $_REQUEST['filegals_manager']);
		$smarty->display("tiki-print.tpl");
	}  else {
		$smarty->display("tiki.tpl");
	}
}
?>
