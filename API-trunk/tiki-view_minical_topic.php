<?php
/**
 *
 *
 * @package   Tiki
 * @copyright (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project. All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license   LGPL. See license.txt for more details
 */
// $Id$

require_once ('tiki-setup.php');
include_once ('lib/minical/minicallib.php');

$access->check_feature('feature_minical', '');
$access->check_permission('tiki_p_minical');

if (!$user) die;
if (!isset($_REQUEST["topicId"])) {
	die;
}
$info = $minicallib->minical_get_topic($user, $_REQUEST["topicId"]);
$type = & $info["filetype"];
$file = & $info["filename"];
$content = & $info["data"];
header("Content-type: $type");
header("Content-Disposition: inline; filename=$file");
echo "$content";
