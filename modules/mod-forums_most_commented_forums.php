<?php

include_once ('lib/rankings/ranklib.php');

$ranking = $ranklib->forums_ranking_most_commented_forum($module_rows);
$smarty->assign('modForumsMostCommentedForums', $ranking["data"]);
$smarty->assign('nonums', isset($module_params["nonums"]) ? $module_params["nonums"] : 'n');

?>