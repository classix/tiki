<?php
// Initialization
require_once('tiki-setup.php');


if($feature_quizzes != 'y') {
  $smarty->assign('msg',tra("This feature is disabled"));
  $smarty->display('error.tpl');
  die;  
}

if(!isset($_REQUEST["questionId"])) {
  $smarty->assign('msg',tra("No question indicated"));
  $smarty->display('error.tpl');
  die;
}
$smarty->assign('questionId',$_REQUEST["questionId"]);
$quiz_info=$tikilib->get_quiz_question($_REQUEST["questionId"]);
$smarty->assign('question_info',$quiz_info);
$_REQUEST["quizId"]=$quiz_info["quizId"];

$smarty->assign('individual','n');
if($userlib->object_has_one_permission($_REQUEST["quizId"],'quiz')) {
  $smarty->assign('individual','y');
  if($tiki_p_admin != 'y') {
    $perms = $userlib->get_permissions(0,-1,'permName_desc','','quizzes');
    foreach($perms["data"] as $perm) {
      $permName=$perm["permName"];
      if($userlib->object_has_permission($user,$_REQUEST["quizId"],'quiz',$permName)) {
        $$permName = 'y';
        $smarty->assign("$permName",'y');
      } else {
        $$permName = 'n';
        $smarty->assign("$permName",'n');
      }
    }
  }
}


if($tiki_p_admin_quizzes != 'y') {
    $smarty->assign('msg',tra("You dont have permission to use this feature"));
    $smarty->display('error.tpl');
    die;
}



if(!isset($_REQUEST["optionId"])) {
  $_REQUEST["optionId"] = 0;
}
$smarty->assign('optionId',$_REQUEST["optionId"]);

if($_REQUEST["optionId"]) {
  $info = $tikilib->get_quiz_question_option($_REQUEST["optionId"]);
} else {
  $info = Array();
  $info["optionText"]='';
  $info["points"]='';
}
$smarty->assign('optionText',$info["optionText"]);
$smarty->assign('points',$info["points"]);

if(isset($_REQUEST["remove"])) {
  $tikilib->remove_quiz_question_option($_REQUEST["remove"]);
}

if(isset($_REQUEST["save"])) {
  $tikilib->replace_question_option($_REQUEST["optionId"],$_REQUEST["optionText"],$_REQUEST["points"],$_REQUEST["questionId"]);
  $smarty->assign('optionText','');
  $smarty->assign('optionId',0);
}

if(!isset($_REQUEST["sort_mode"])) {
  $sort_mode = 'optionText_asc'; 
} else {
  $sort_mode = $_REQUEST["sort_mode"];
} 

if(!isset($_REQUEST["offset"])) {
  $offset = 0;
} else {
  $offset = $_REQUEST["offset"]; 
}
$smarty->assign_by_ref('offset',$offset);

if(isset($_REQUEST["find"])) {
  $find = $_REQUEST["find"];  
} else {
  $find = ''; 
}
$smarty->assign('find',$find);

$smarty->assign_by_ref('sort_mode',$sort_mode);
$channels = $tikilib->list_quiz_question_options($_REQUEST["questionId"],$offset,$maxRecords,$sort_mode,$find);

$cant_pages = ceil($channels["cant"] / $maxRecords);
$smarty->assign_by_ref('cant_pages',$cant_pages);
$smarty->assign('actual_page',1+($offset/$maxRecords));
if($channels["cant"] > ($offset+$maxRecords)) {
  $smarty->assign('next_offset',$offset + $maxRecords);
} else {
  $smarty->assign('next_offset',-1); 
}
// If offset is > 0 then prev_offset
if($offset>0) {
  $smarty->assign('prev_offset',$offset - $maxRecords);  
} else {
  $smarty->assign('prev_offset',-1); 
}

$smarty->assign_by_ref('channels',$channels["data"]);


// Display the template
$smarty->assign('mid','tiki-edit_question_options.tpl');
$smarty->display('tiki.tpl');
?>