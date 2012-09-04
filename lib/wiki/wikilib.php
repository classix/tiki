<?php
// (c) Copyright 2002-2012 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
	header('location: index.php');
	exit;
}

if ( !defined('PLUGINS_DIR') ) {
	define('PLUGINS_DIR', 'lib/wiki-plugins');
}


class WikiLib extends TikiLib
{

	//Special parsing for multipage articles
	function get_number_of_pages($data)
	{
		global $prefs;
		// Temporary remove <PRE></PRE> secions to protect
		// from broke <PRE> tags and leave well known <PRE>
		// behaviour (i.e. type all text inside AS IS w/o
		// any interpretation)
		$preparsed = array();

		preg_match_all("/(<[Pp][Rr][Ee]>)(.*?)(<\/[Pp][Rr][Ee]>)/s", $data, $preparse);
		$idx = 0;

		foreach (array_unique($preparse[2])as $pp) {
			$key = md5($this->genPass());

			$aux['key'] = $key;
			$aux['data'] = $pp;
			$preparsed[] = $aux;
			$data = str_replace($preparse[1][$idx] . $pp . $preparse[3][$idx], $key, $data);
			$idx = $idx + 1;
		}

		$parts = explode($prefs['wiki_page_separator'], $data);
		return count($parts);
	}

	function get_page($data, $i)
	{
		// Get slides
		global $prefs;
		$parts = explode($prefs['wiki_page_separator'], $data);
		$ret = $parts[$i - 1];

		if (substr($parts[$i - 1], 1, 5) == '<br/>')
			$ret = substr($parts[$i - 1], 6);

		if (substr($parts[$i - 1], 1, 6) == '<br />')
			$ret = substr($parts[$i - 1], 7);

		return $ret;
	}

	function get_creator($name)
	{
		return $this->getOne('select `creator` from `tiki_pages` where `pageName`=?', array($name));
	}

	/**
	 * Get the contributors for page
	 * the returned array does not contain the user $last (usually the current or last user)
	 */
	function get_contributors($page, $last='')
	{
		static $cache_page_contributors;
		if ($cache_page_contributors['page'] == $page) {
			if (empty($last)) {
				return $cache_page_contributors['contributors'];
			}
			$ret = array();
			foreach ($cache_page_contributors['contributors'] as $res) {
				if ($res['user'] != $last) {
					$ret[] = $res;
				}
			}
			return $ret;
		}

		$query = 'select `user`, MAX(`version`) as `vsn` from `tiki_history` where `pageName`=? group by `user` order by `vsn` desc';
		// jb fixed 110115 - please check intended behaviour remains
		// was: $query = "select `user` from `tiki_history` where `pageName`=? group by `user` order by MAX(`version`) desc";
		$result = $this->query($query, array($page));
		$cache_page_contributors = array();
		$cache_page_contributors['contributors'] = array();
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($res['user'] != $last) {
				$ret[] = $res['user'];
			}
			$cache_page_contributors['contributors'][] = $res['user'];
		}
		$cache_page_contributors['page'] = $page;
		return $ret;
	}

	// Returns all pages that links from here or to here, without distinction
	// This is used by wiki3d, to make the graph
	function wiki_get_neighbours($page)
	{
		$neighbours = array();
		$already = array();

		$query = "select `toPage` from `tiki_links` where `fromPage`=? and `fromPage` not like 'objectlink:%'";
		$result = $this->query($query, array($page));
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
			$neighbour = $row['toPage'];
			$neighbours[] = $neighbour;
			$already[$neighbour] = 1;
		}

		$query = "select `fromPage` from `tiki_links` where `toPage`=? and `fromPage` not like 'objectlink:%'";
		$result = $this->query($query, array($page));
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
			$neighbour = $row['fromPage'];
			if (!isset($already[$neighbour])) {
				$neighbours[] = $neighbour;
			}
		}

		return $neighbours;
	}

	// Returns a string containing all characters considered bad in page names
	function get_badchars()
	{
		return "/?#[]@$&+;=<>";
	}

	// Returns a boolean indicating whether the given page name contains "bad characters"
	// See http://dev.tiki.org/Bad+characters
	function contains_badchars($name)
	{
		$badchars = $this->get_badchars();
		$badchars = preg_quote($badchars, '/');
		return preg_match("/[$badchars]/", $name);
	}

	/**
	 * Duplicate an existing page
	 *
	 * @param string $name
	 * @param string $copyName
	 * @return bool
	 */
	function wiki_duplicate_page($name, $copyName = null)
	{
		global $tikilib;

		$info = $tikilib->get_page_info($name);

		if (!$info) {
			return false;
		}

		if (!$copyName) {
			$copyName = $name . ' (' . $tikilib->now . ')';
		}

		return $tikilib->create_page(
						$copyName,
						0,
						$info['data'],
						$tikilib->now,
						$info['comment'],
						$info['user'],
						$info['ip'],
						$info['description'],
						$info['lang'],
						$info['is_html']
		);
	}

	// This method renames a wiki page
	// If you think this is easy you are very very wrong
	function wiki_rename_page($oldName, $newName, $renameHomes = true)
	{
		global $prefs, $tikilib;
		// if page already exists, stop here
		$newName = trim($newName);
		if ($this->get_page_info($newName, false, true)) {
			// if it is a case change of same page: allow it, else stop here
			if (strcasecmp(trim($oldName), $newName) <> 0 ) throw new Exception("Page already exists", 2);
		}

		if ($this->contains_badchars($newName) && $prefs['wiki_badchar_prevent'] == 'y') {
			throw new Exception("Bad characters", 1);
		}

		$tmpName = "TmP".$newName."TmP";

		// 1st rename the page in tiki_pages, using a tmpname inbetween for
		// rename pages like ThisTestpage to ThisTestPage
		$query = 'update `tiki_pages` set `pageName`=? where `pageName`=?';
		$this->query($query, array( $tmpName, $oldName ));

		$query = 'update `tiki_pages` set `pageName`=? where `pageName`=?';
		$this->query($query, array( $newName, $tmpName ));

		// correct pageName in tiki_history, using a tmpname inbetween for
		// rename pages like ThisTestpage to ThisTestPage
		$query = 'update `tiki_history` set `pageName`=? where `pageName`=?';
		$this->query($query, array( $tmpName, $oldName ));

		$query = 'update `tiki_history` set `pageName`=? where `pageName`=?';
		$this->query($query, array( $newName, $tmpName ));

		// get pages linking to the old page
		$query = 'select `fromPage` from `tiki_links` where `toPage`=?';
		$result = $this->query($query, array( $oldName ));

		$linksToOld=array();
		while ($res = $result->fetchRow()) {
			$page = $res['fromPage'];

			$is_wiki_page = true;
			if (substr($page, 0, 11) == 'objectlink:') {
				$is_wiki_page = false;
				$objectlinkparts = explode(':', $page);
				$type = $objectlinkparts[1];
				$objectId = $objectlinkparts[2];
			}
			$linksToOld[] = $res['fromPage'];
			if ($is_wiki_page) {
				$info = $this->get_page_info($page);
				//$data=addslashes(str_replace($oldName,$newName,$info['data']));
				$data = $info['data'];
			} elseif ($type == 'forum post' || substr($type, -7) == 'comment') {
				include_once ('lib/comments/commentslib.php');
				global $dbTiki;
				$commentslib = new Comments($dbTiki);
				$comment_info = $commentslib->get_comment($objectId);
				$data = $comment_info['data'];
			}

			$quotedOldName = preg_quote($oldName, '/');
			global $semanticlib; require_once 'lib/wiki/semanticlib.php';

			foreach ( $semanticlib->getAllTokens() as $sem ) {
				$data = str_replace("($sem($oldName", "($sem($newName", $data);
			}

			if ($prefs['feature_wikiwords'] == 'y') {
				if (strstr($newName, ' '))
					$data = preg_replace("/(?<= |\n|\t|\r|\,|\;|^)$quotedOldName(?= |\n|\t|\r|\,|\;|$)/", '((' . $newName . '))', $data);
				else
					$data = preg_replace("/(?<= |\n|\t|\r|\,|\;|^)$quotedOldName(?= |\n|\t|\r|\,|\;|$)/", $newName, $data);
			}

			$data = preg_replace("/(?<=\(\()$quotedOldName(?=\)\)|\|)/i", $newName, $data);

			$quotedOldHtmlName = preg_quote(urlencode($oldName), '/');
			$htmlSearch = '/<a class="wiki" href="tiki-index\.php\?page=' . $quotedOldHtmlName . '([^"]*)"/i';
			$htmlReplace = '<a class="wiki" href="tiki-index.php?page=' . urlencode($newName) . '\\1"';
			$data = preg_replace($htmlSearch, $htmlReplace, $data);
			$htmlSearch = '/<a class="wiki" href="' . $quotedOldHtmlName . '"/i';
			$htmlReplace = '<a class="wiki" href="' . urlencode($newName) . '"';
			$data = preg_replace($htmlSearch, $htmlReplace, $data);

			$htmlWantedSearch = '/(' . $quotedOldName . ')?<a class="wiki wikinew" href="tiki-editpage\.php\?page=' . $quotedOldHtmlName . '"[^<]+<\/a>/i';
			$data = preg_replace($htmlWantedSearch, '((' . $newName . '))', $data);

			if ($is_wiki_page) {
				$query = "update `tiki_pages` set `data`=?,`page_size`=? where `pageName`=?";
				$this->query($query, array( $data,(int) strlen($data), $page));
			} elseif ($type == 'forum post' || substr($type, -7) == 'comment') {
				$query = "update `tiki_comments` set `data`=? where `threadId`=?";
				$this->query($query, array( $data, $objectId));
			}
			$this->invalidate_cache($page);
		}

		// correct toPage and fromPage in tiki_links
		// before update, manage to avoid duplicating index(es) when B is renamed to C while page(s) points to both C (not created yet) and B
		$query = 'select `fromPage` from `tiki_links` where `toPage`=?';
		$result = $this->query($query, array( $newName ));
		$linksToNew = array();

		while ($res = $result->fetchRow()) {
			$linksToNew[] = $res['fromPage'];
		}

		if ($extra = array_intersect($linksToOld, $linksToNew)) {
			$query = 'delete from `tiki_links` where `fromPage` in (' . implode(',', array_fill(0, count($extra), '?')) . ') and `toPage`=?';
			$this->query($query, array_merge($extra, array($oldName)));
		}

		$query = 'update `tiki_links` set `fromPage`=? where `fromPage`=?';
		$this->query($query, array( $newName, $oldName));

		$query = 'update `tiki_links` set `toPage`=? where `toPage`=?';
		$this->query($query, array( $newName, $oldName));

		// tiki_footnotes change pageName
		$query = 'update `tiki_page_footnotes` set `pageName`=? where `pageName`=?';
		$this->query($query, array( $newName, $oldName ));

		// in tiki_categorized_objects update objId
		$newcathref = 'tiki-index.php?page=' . urlencode($newName);
		$query = 'update `tiki_objects` set `itemId`=?,`name`=?,`href`=? where `itemId`=? and `type`=?';
		$this->query($query, array( $newName, $newName, $newcathref, $oldName, 'wiki page'));

		$this->rename_object('wiki page', $oldName, $newName);

		// update categories if new name has a category default
		$categlib = TikiLib::lib('categ');
		$categories = $categlib->get_object_categories('wiki page', $newName);
		$info = $this->get_page_info($newName);
		$categlib->update_object_categories($categories, $newName, 'wiki page', $info['description'], $newName, $newcathref);

		$query = 'update `tiki_wiki_attachments` set `page`=? where `page`=?';
		$this->query($query, array( $newName, $oldName ));

		// group home page
		if ($renameHomes) {
			$query = 'update `users_groups` set `groupHome`=? where `groupHome`=?';
			$this->query($query, array( $newName, $oldName ));
		}

		// copyright
		$query = 'update tiki_copyrights set `page`=? where `page`=?';
		$this->query($query, array( $newName, $oldName ));

		//breadcrumb
		if (isset($_SESSION['breadCrumb']) && in_array($oldName, $_SESSION['breadCrumb'])) {
			$pos = array_search($oldName, $_SESSION["breadCrumb"]);
			$_SESSION['breadCrumb'][$pos] = $newName;
		}

		global $prefs;
		global $tikilib;
		global $smarty;
		global $user;

		// first get all watches for this page ...
		if ($prefs['feature_user_watches'] == 'y') {
			$nots = $tikilib->get_event_watches('wiki_page_changed', $oldName);
		}

		// ... then update the watches table
		// user watches
		$query = "update `tiki_user_watches` set `object`=?, `title`=?, `url`=? where `object`=? and `type` = 'wiki page'";
		$this->query($query, array( $newName, $newName, 'tiki-index.php?page='.$newName, $oldName ));
		$query = "update `tiki_group_watches` set `object`=?, `title`=?, `url`=? where `object`=? and `type` = 'wiki page'";
		$this->query($query, array( $newName, $newName, 'tiki-index.php?page='.$newName, $oldName ));

		// now send notification email to all on the watchlist:
		if ($prefs['feature_user_watches'] == 'y') {
			if (!isset($_SERVER["SERVER_NAME"])) {
				$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
			}

			if (count($nots)) {
				include_once('lib/notifications/notificationemaillib.php');
				$smarty->assign('mail_site', $_SERVER['SERVER_NAME']);
				$smarty->assign('mail_oldname', $oldName);
				$smarty->assign('mail_newname', $newName);
				$smarty->assign('mail_user', $user);
				sendEmailNotification($nots, 'watch', 'user_watch_wiki_page_renamed_subject.tpl', $_SERVER['SERVER_NAME'], 'user_watch_wiki_page_renamed.tpl');
			}
		}

		require_once('lib/search/refresh-functions.php');
		refresh_index('pages', $newName);

		if ($renameHomes && $prefs['wikiHomePage'] == $oldName) {
			$tikilib->set_preference('wikiHomePage', $newName);
		}
		if ($prefs['feature_trackers'] == 'y') {
			global $trklib; include_once('lib/trackers/trackerlib.php');
			$trklib->rename_page($oldName, $newName);
		}

		return true;
	}

	function set_page_cache($page,$cache)
	{
		$query = 'update `tiki_pages` set `wiki_cache`=? where `pageName`=?';
		$this->query($query, array( $cache, $page));
	}

	// TODO: huho why that function is empty ?
	function save_notepad($user, $title, $data)
	{
	}

	// Methods to cache and handle the cached version of wiki pages
	// to prevent parsing large pages.
	function get_cache_info($page)
	{
		$query = 'select `cache`,`cache_timestamp` from `tiki_pages` where `pageName`=?';

		$result = $this->query($query, array( $page ));
		$res = $result->fetchRow();
		return $res;
	}

	function get_parse($page, &$canBeRefreshed = false, $suppress_icons = false)
	{
		global $prefs, $user, $headerlib;
		$content = '';
		$canBeRefreshed = false;

		$info = $this->get_page_info($page);
		if (empty($info)) {
			return '';
		}

		$parse_options = array(
			'is_html' => $info['is_html'],
			'language' => $info['lang'],
			'namespace' => $info['namespace'],
		);

		if ($suppress_icons || (!empty($info['lockedby']) && $info['lockedby'] != $user)) {
			$parse_options['suppress_icons'] = true;
		}

		$wiki_cache = ($prefs['feature_wiki_icache'] == 'y' && !is_null($info['wiki_cache'])) ? $info['wiki_cache'] : $prefs['wiki_cache'];

		if ($wiki_cache > 0 && empty($_REQUEST['offset']) && empty($_REQUEST['itemId']) && (empty($user) || $prefs['wiki_cache'] == 0) ) {
			$cache_info = $this->get_cache_info($page);
			if (!empty($cache_info['cache_timestamp']) && $cache_info['cache_timestamp'] + $wiki_cache >= $this->now) {
				$content = $cache_info['cache'];
				// get any cached JS and add to headerlib JS
				$jsFiles = $headerlib->getJsFromHTML($content, false, true);

				foreach ($jsFiles as $jsFile) {
					$headerlib->add_jsfile($jsFile);
				}

				$headerlib->add_js(implode("\n", $headerlib->getJsFromHTML($content)));

				// now remove all the js from the source
				$content = $headerlib->removeJsFromHtml($content);

				$canBeRefreshed = true;
			} else {
				$jsFile1 = $headerlib->getJsfiles();
				$js1 = $headerlib->getJs();

				$content = $this->parse_data($info['data'], $parse_options);

				// get any JS added to headerlib during parse_data and add to the bottom of the data to cache
				$jsFile2 = $headerlib->getJsfiles();
				$js2 = $headerlib->getJs();

				$jsFile = array_diff($jsFile2, $jsFile1);
				$js = array_diff($js2, $js1);

				$jsFile = implode("\n", $jsFile);
				$js = $headerlib->wrap_js(implode("\n", $js));

				$this->update_cache($page, $content . $jsFile . $js);
			}
		} else {
			$content = $this->parse_data($info['data'], $parse_options);
		}
		return $content;
	}

	function update_cache($page, $data)
	{
		$query = 'update `tiki_pages` set `cache`=?, `cache_timestamp`=? where `pageName`=?';
		$result = $this->query($query, array( $data, $this->now, $page ));
		return true;
	}

	function get_attachment_owner($attId)
	{
		return $this->getOne("select `user` from `tiki_wiki_attachments` where `attId`=$attId");
	}

	function remove_wiki_attachment($attId)
	{
		global $prefs;

		$path = $this->getOne("select `path` from `tiki_wiki_attachments` where `attId`=$attId");

		/* carefull a same file can be attached in different page */
		if ($path && $this->getOne("select count(*) from `tiki_wiki_attachments` where `path`='$path'") <= 1) {
			@unlink($prefs['w_use_dir'] . $path);
		}

		$query = "delete from `tiki_wiki_attachments` where `attId`='$attId'";
		$result = $this->query($query);
		if ($prefs['feature_actionlog'] == 'y') {
			global $logslib; include_once('lib/logs/logslib.php');
			$logslib->add_action('Removed', $attId, 'wiki page attachment');
		}
	}

	function wiki_attach_file($page, $name, $type, $size, $data, $comment, $user, $fhash, $date='')
	{
		$comment = strip_tags($comment);
		$now = empty($date)? $this->now: $date;
		$query = 'insert into `tiki_wiki_attachments`' .
						' (`page`,`filename`,`filesize`,`filetype`,`data`,`created`,`hits`,`user`,`comment`,`path`)' .
						' values(?,?,?,?,?,?,?,?,?,?)';
		$result = $this->query($query, array($page, $name, (int)$size, $type, $data, (int)$now, 0, $user, $comment, $fhash));

		global $prefs;
		if ($prefs['feature_score'] == 'y') {
			$this->score_event($user, 'wiki_attach_file');
		}
		if ($prefs['feature_user_watches'] = 'y') {
			include_once('lib/notifications/notificationemaillib.php');
			$query = 'select `attId` from `tiki_wiki_attachments` where `page`=? and `filename`=? and `created`=? and `user`=?';
			$attId = $this->getOne($query, array($page, $name, $now, $user));
			sendWikiEmailNotification('wiki_file_attached', $page, $user, $comment, '', $name, '', '', false, '', 0, $attId);
		}
		if ($prefs['feature_actionlog'] == 'y') {
			global $logslib; include_once('lib/logs/logslib.php');
			if (empty($attId)) {
				$query = 'select `attId` from `tiki_wiki_attachments` where `page`=? and `filename`=? and `created`=? and `user`=?';
				$attId = $this->getOne($query, array($page, $name, $now, $user));
			}
			$logslib->add_action('Created', $attId, 'wiki page attachment');
		}
	}

	function get_wiki_attach_file($page, $name, $type, $size)
	{
		$query = 'select * from `tiki_wiki_attachments` where `page`=? and `filename`=? and `filetype`=? and `filesize`=?';
		$result = $this->query($query, array($page, $name, $type, $size));
		$res = $result->fetchRow();
		return $res;
	}

	function list_wiki_attachments($page, $offset=0, $maxRecords=-1, $sort_mode='created_desc', $find='')
	{
		if ($find) {
			$mid = ' where `page`=? and (`filename` like ?)'; // why braces?
			$bindvars=array($page,'%' . $find . '%');
		} else {
			$mid = ' where `page`=? ';
			$bindvars=array($page);
		}

		if ($sort_mode !== 'created_desc') {
			$pos = strrpos($sort_mode, '_');
			if ($pos !== false && $pos > 0) {	// check the sort order is valid for attachments
				$shortsort = substr($sort_mode, 0, $pos);
			} else {
				$shortsort = $sort_mode;
			}
			if (!in_array(array('user','attId','page','filename','filesize','filetype','hits','created','comment'), $shortsort)) {
				$sort_mode = 'created_desc';
			}
		}

		$query = 'select `user`,`attId`,`page`,`filename`,`filesize`,`filetype`,`hits`,`created`,`comment`' .
						' from `tiki_wiki_attachments` '.$mid.' order by ' . $this->convertSortMode($sort_mode);
		$query_cant = "select count(*) from `tiki_wiki_attachments` $mid";
		$result = $this->query($query, $bindvars, $maxRecords, $offset);
		$cant = $this->getOne($query_cant, $bindvars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		$retval = array();
		$retval['data'] = $ret;
		$retval['cant'] = $cant;
		return $retval;
	}
	function list_all_attachements($offset=0, $maxRecords=-1, $sort_mode='created_desc', $find='')
	{
		if ($find) {
			$findesc = '%' . $find . '%';
			$mid = ' where `filename` like ?';
			$bindvars=array($findesc);
		} else {
			$mid = '';
			$bindvars=array();
		}
		$query = 'select `user`,`attId`,`page`,`filename`,`filesize`,`filetype`,`hits`,`created`,`comment`,`path` ';
		$query.= ' from `tiki_wiki_attachments` $mid order by ' . $this->convertSortMode($sort_mode);
		$query_cant = "select count(*) from `tiki_wiki_attachments` $mid";
		$result = $this->query($query, $bindvars, $maxRecords, $offset);
		$cant = $this->getOne($query_cant, $bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}
		$retval = array();
		$retval['data'] = $ret;
		$retval['cant'] = $cant;
		return $retval;
	}

	function file_to_db($path,$attId)
	{
		if (is_file($path)) {
			$fp = fopen($path, 'rb');
			$data = '';
			while (!feof($fp)) {
				$data .= fread($fp, 8192 * 16);
			}
			fclose($fp);
			$query = 'update `tiki_wiki_attachments` set `data`=?,`path`=? where `attId`=?';
			if ($this->query($query, array($data,'',(int)$attId))) {
				unlink($path);
			}
		}
	}

	function db_to_file($filename,$attId)
	{
		global $prefs;
		$file_name = md5($filename.date('U').rand());
		$fw = fopen($prefs['w_use_dir'].$file_name, 'wb');
		$data = $this->getOne('select `data` from `tiki_wiki_attachments` where `attId`=?', array((int)$attId));
		if ($data) {
			fwrite($fw, $data);
		}
		fclose($fw);
		if (is_file($prefs['w_use_dir'].$file_name)) {
			$query = 'update `tiki_wiki_attachments` set `data`=?,`path`=? where `attId`=?';
			$this->query($query, array('',$file_name,(int)$attId));
		}
	}

	function get_item_attachment($attId)
	{
		$query = 'select * from `tiki_wiki_attachments` where `attId`=?';
		$result = $this->query($query, array((int) $attId));
		if (!$result->numRows()) return false;
		$res = $result->fetchRow();
		return $res;
	}

	function get_item_attachement_data($att_info)
	{
		if ($att_info['path']) {
			return file_get_contents($att_info['filename']);
		} else {
			return $att_info['data'];
		}
	}


	// Functions for wiki page footnotes
	function get_footnote($user, $page)
	{
		$count = $this->getOne('select count(*) from `tiki_page_footnotes` where `user`=? and `pageName`=?', array($user,$page));

		if (!$count) {
			return '';
		} else {
			return $this->getOne('select `data` from `tiki_page_footnotes` where `user`=? and `pageName`=?', array($user,$page));
		}
	}

	function replace_footnote($user, $page, $data)
	{
		$querydel = 'delete from `tiki_page_footnotes` where `user`=? and `pageName`=?';
		$this->query($querydel, array($user, $page), -1, -1, false);
		$query = 'insert into `tiki_page_footnotes`(`user`,`pageName`,`data`) values(?,?,?)';
		$this->query($query, array($user,$page,$data));
	}

	function remove_footnote($user, $page)
	{
		if (empty($user)) {
			$query = 'delete from `tiki_page_footnotes` where `pageName`=?';
			$this->query($query, array($page));
		} else {
			$query = 'delete from `tiki_page_footnotes` where `user`=? and `pageName`=?';
			$this->query($query, array($user,$page));
		}
	}

	function wiki_link_structure()
	{
		$query = 'select `pageName` from `tiki_pages` order by ' . $this->convertSortMode('pageName_asc');

		$result = $this->query($query);

		while ($res = $result->fetchRow()) {
			print ($res['pageName'] . ' ');

			$page = $res['pageName'];
			$query2 = 'select `toPage` from `tiki_links` where `fromPage`=?';
			$result2 = $this->query($query2, array( $page ));
			$pages = array();

			while ($res2 = $result2->fetchRow()) {
				if (($res2['toPage'] <> $res['pageName']) && (!in_array($res2['toPage'], $pages))) {
					$pages[] = $res2['toPage'];
					print ($res2['toPage'] . ' ');
				}
			}

			print ("\n");
		}
	}

	// Removes last version of the page (from pages) if theres some
	// version in the tiki_history then the last version becomes the actual version
	function remove_last_version($page, $comment = '')
	{
		global $prefs;
		$this->invalidate_cache($page);
		$query = 'select * from `tiki_history` where `pageName`=? order by ' . $this->convertSortMode('lastModif_desc');
		$result = $this->query($query, array( $page ));

		if ($result->numRows()) {
			// We have a version
			$res = $result->fetchRow();

			global $histlib;
			if (!is_object($histlib)) {
				include_once('lib/wiki/histlib.php');
			}

			$histlib->use_version($res['pageName'], $res['version']);
			if ($prefs['feature_contribution'] == 'y') {
				global $contributionlib; include_once('lib/contribution/contributionlib.php');
				$tikilib = TikiLib::lib('tiki');
				$info = $tikilib->get_page_info($res['pageName']);

				$contributionlib->change_assigned_contributions(
								$res['historyId'],
								'history',
								$res['pageName'],
								'wiki page',
								$info['description'],
								$res['pageName'],
								'tiki-index.php?page' . urlencode($res['pageName'])
				);
			}
			$histlib->remove_version($res['pageName'], $res['version']);
		} else {
			$this->remove_all_versions($page);
		}
		global $logslib; include_once('lib/logs/logslib.php');
		$logslib->add_action('Removed last version', $page, 'wiki page', $comment);
		//get_strings tra("Removed last version");
	}

	/**
	 * Return the page names for a page alias, if any.
	 *
	 * Unfortunately there is no mechanism to prevent two
	 * different pages from sharing the same alias and that is
	 * why this method return an array of page names instead of a
	 * page name string.
	 *
	 * @param string $alias
	 * @return array page names
	 */
	function get_pages_by_alias($alias)
	{
		global $prefs, $semanticlib;
		require_once 'lib/wiki/semanticlib.php';

		$pages = array();
		
		if ($prefs['feature_wiki_pagealias'] == 'n' && empty($prefs["wiki_prefixalias_tokens"])) {
			return $pages;
		}
		
		$toPage = $alias;
		$tokens = explode(',', $prefs['wiki_pagealias_tokens']);

		$prefixes = explode(',', $prefs["wiki_prefixalias_tokens"]);
		foreach ($prefixes as $p) {
			$p = trim($p);
			if (strlen($p) > 0 && TikiLib::strtolower(substr($alias, 0, strlen($p))) == TikiLib::strtolower($p)) {
				$toPage = $p;
				$tokens = 'prefixalias';
			}
		}

		$links = $semanticlib->getLinksUsing($tokens, array( 'toPage' => $toPage ));
		
		if ( count($links) > 0 ) {
			foreach ( $links as $row ) {
				$pages[] = $row['fromPage'];
			}
		}
		
		return $pages;
	}
	
	// Like pages are pages that share a word in common with the current page
	function get_like_pages($page)
	{
		global $user, $tikilib, $prefs, $semanticlib;

		preg_match_all("/([A-Z])([a-z]+)/", $page, $words);

		// Add support to ((x)) in either strict or full modes
		preg_match_all("/(([A-Za-z]|[\x80-\xFF])+)/", $page, $words2);
		$words = array_unique(array_merge($words[0], $words2[0]));
		$exps = array();
		$bindvars=array();
		foreach ($words as $word) {
			$exps[] = ' `pageName` like ?';
			$bindvars[] = "%$word%";
		}

		$exp = implode(' or ', $exps);
		if ($exp) {
			$query = "select `pageName`, `lang` from `tiki_pages` where ($exp)";

			if ( $prefs['feature_multilingual'] == 'y' ) {
				$query .= ' ORDER BY CASE WHEN `lang` = ? THEN 0 WHEN `lang` IS NULL OR `lang` = \'\' THEN 1 ELSE 2 END';
				$bindvars[] = $prefs['language'];
			}

			$result = $this->query($query, $bindvars);
			$ret = array();

			while ($res = $result->fetchRow()) {
				if ( $prefs['wiki_likepages_samelang_only'] == 'y' && ! empty( $res['lang'] ) && $res['lang'] != $prefs['language'] ) {
					continue;
				}

				if ($tikilib->user_has_perm_on_object($user, $res['pageName'], 'wiki page', 'tiki_p_view')) {
					$ret[] = $res['pageName'];
				}
			}

			return $ret;
		} else {
			return array();
		}
	}

	function is_locked($page, $info=null)
	{
		if (!$info) {
			$query = "select `flag`, `user` from `tiki_pages` where `pageName`=?";
			$result = $this->query($query, array( $page ));
			$info = $result->fetchRow();
		}

		return ($info['flag'] == 'L')? $info['user'] : null;
	}

	function is_editable($page, $user, $info=null)
	{
		global $prefs;
		$perms = Perms::get(array( 'type' => 'wiki page', 'object' => $page ));

		if ($perms->admin_wiki) {
			return true;
		}

		if ( $prefs['wiki_creator_admin'] == 'y' && !empty($user) && $info['creator'] == $user ) {
			return true;
		}

		if ($prefs['feature_wiki_userpage'] == 'y'
			&& !empty($user)
			&& strcasecmp($prefs['feature_wiki_userpage_prefix'], substr($page, 0, strlen($prefs['feature_wiki_userpage_prefix']))) == 0
		) {
			if (strcasecmp($page, $prefs['feature_wiki_userpage_prefix'] . $user) == 0) {
				return true;
			}
		}

		if ($prefs['feature_wiki_userpage'] == 'y'
				&& strcasecmp(substr($page, 0, strlen($prefs['feature_wiki_userpage_prefix'])), $prefs['feature_wiki_userpage_prefix']) == 0
				and strcasecmp($page, $prefs['feature_wiki_userpage_prefix'] . $user) != 0
		)
			return false;

		if (!$perms->edit )
			return false;

		return ($this->is_locked($page, $info) == null || $user == $this->is_locked($page, $info))? true : false;
	}

	function lock_page($page)
	{
		global $user, $tikilib;

		$query = 'update `tiki_pages` set `flag`=?, `lockedby`=? where `pageName`=?';
		$result = $this->query($query, array( 'L', $user, $page ));

		if (!empty($user)) {
			$info = $tikilib->get_page_info($page);

			$query = 'update `tiki_pages` set `user`=?, `comment`=?, `version`=? where `pageName`=?';
			$result = $this->query($query, array($user, tra('Page locked'), $info['version'] + 1, $page));

			$query = 'insert into `tiki_history`(`pageName`, `version`, `lastModif`, `user`, `ip`, `comment`, `data`, `description`)' .
								' values(?,?,?,?,?,?,?,?)';
			$result = $this->query(
							$query,
							array(
								$page,
								(int) $info['version'] + 1,
								(int) $info['lastModif'],
								$user,
								$info['ip'],
								tra('Page locked'),
								$info['data'],
								$info['description']
							)
			);
		}

		return true;
	}

	function unlock_page($page)
	{
		global $user, $tikilib;

		$query = "update `tiki_pages` set `flag`='' where `pageName`=?";
		$result = $this->query($query, array($page));

		if (isset($user)) {
			$info = $tikilib->get_page_info($page);

			$query = "update `tiki_pages` set `user`=?, `comment`=?, `version`=? where `pageName`=?";
			$result = $this->query($query, array($user, tra('Page unlocked'), $info['version'] + 1, $page));

			$query = "insert into `tiki_history`(`pageName`, `version`, `lastModif`, `user`, `ip`, `comment`, `data`, `description`) values(?,?,?,?,?,?,?,?)";
			$result = $this->query(
							$query,
							array(
								$page,
								(int) $info['version'] + 1,
								(int) $info['lastModif'],
								$user,
								$info['ip'],
								tra('Page unlocked'),
								$info['data'],
								$info['description']
							)
			);
		}

		return true;
	}

	// Returns backlinks for a given page
	function get_backlinks($page)
	{
		global $user;
		$query = "select `fromPage` from `tiki_links` where `toPage` = ? and `fromPage` not like 'objectlink:%'";
		// backlinks do not include links from non-page objects TODO: full feature allowing this with options
		$result = $this->query($query, array( $page ));
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['fromPage'], 'wiki page', 'tiki_p_view')) {
				$aux["fromPage"] = $res["fromPage"];
				$ret[] = $aux;
			}
		}

		return $ret;
	}

	function get_parent_pages($child_page)
	{
		$parent_pages = array();
		$backlinks_info = $this->get_backlinks($child_page);
		foreach ($backlinks_info as $index => $backlink) {
			$parent_pages[] = $backlink['fromPage'];
		}
		return $parent_pages;
	}

	function list_plugins($with_help = false, $area_id = 'editwiki')
	{
		$parserlib = TikiLib::lib('parser');

		if ($with_help) {
			global $cachelib, $headerlib, $prefs;
			$headerlib->add_jsfile('tiki-jsplugin.php?language='.$prefs['language'], 'dynamic');
			if ($prefs['wikiplugin_module'] === 'y' && $prefs['wikiplugininline_module'] === 'n') {
				$headerlib->add_jsfile('tiki-jsmodule.php?language='.$prefs['language'], 'dynamic');
			}
			$cachetag = 'plugindesc' . $this->get_language() . $area_id . '_js=' . $prefs['javascript_enabled'];
			if (! $plugins = $cachelib->getSerialized($cachetag) ) {
				$list = $parserlib->plugin_get_list();

				$plugins = array();
				foreach ($list as $name) {
					$pinfo['help'] = $this->get_plugin_description($name, $enabled, $area_id);
					$pinfo['name'] = TikiLib::strtoupper($name);

					if ( $enabled )
						$plugins[] = $pinfo;
				}
				$cachelib->cacheItem($cachetag, serialize($plugins));
			}
			return $plugins;
		} else {
			// Only used by PluginManager ... what is that anyway?
			$files = array();

			if (is_dir(PLUGINS_DIR)) {
				if ($dh = opendir(PLUGINS_DIR)) {
					while (($file = readdir($dh)) !== false) {
						if (preg_match("/^wikiplugin_.*\.php$/", $file))
							array_push($files, $file);
					}
					closedir($dh);
				}
			}
			sort($files);

			return $files;
		}
	}

	//
	// Call 'wikiplugin_.*_description()' from given file
	//
	function get_plugin_description($name, &$enabled, $area_id = 'editwiki')
	{
		global $tikilib;
		$parserlib = TikiLib::lib('parser');

		if ( ( ! $info = $parserlib->plugin_info($name) ) && $parserlib->plugin_exists($name, true) ) {
			$enabled = true;

			$func_name = "wikiplugin_{$name}_help";
			if ( ! function_exists($func_name) )
				return false;

			$ret = $func_name();
			return $tikilib->parse_data($ret);
		} else {
			global $smarty;
			$enabled = true;

			$ret = $info;

			if ( isset( $ret['prefs'] ) ) {
				global $prefs;

				// If the plugin defines required preferences, they should all be to 'y'
				foreach ( $ret['prefs'] as $pref )
					if ( ! isset( $prefs[$pref] ) || $prefs[$pref] != 'y' ) {
						$enabled = false;
						return;
					}
			}

			if ( isset( $ret['documentation'] ) && ctype_alnum($ret['documentation']) ) {
				$ret['documentation'] = "http://doc.tiki.org/{$ret['documentation']}";
			}

			$smarty->assign('area_id', $area_id);
			$smarty->assign('plugin', $ret);
			$smarty->assign('plugin_name', TikiLib::strtoupper($name));
			return $smarty->fetch('tiki-plugin_help.tpl');
		}
	}

	// get all modified pages for a user (if actionlog is not clean)
	function get_user_all_pages($user, $sort_mode)
	{
		$query = "select p.`pageName`, p.`user` as lastEditor, p.`creator`, max(a.`lastModif`) as date" .
						" from `tiki_actionlog` as a, `tiki_pages` as p" .
						" where a.`object`= p.`pageName` and a.`user`= ? and (a.`action`=? or a.`action`=?)" .
						" group by p.`pageName`, p.`user`, p.`creator` order by " . $this->convertSortMode($sort_mode);

		$result = $this->query($query, array($user, 'Updated', 'Created'));
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['pageName'], 'wiki page', 'tiki_p_view')) {
				$ret[] = $res;
			}
		}
		return $ret;
	}

	function get_default_wiki_page()
	{
		global $user, $prefs;
		if ($prefs['useGroupHome'] == 'y') {
			global $user;
			global $userlib; include_once('lib/userslib.php');
			if ($groupHome = $userlib->get_user_default_homepage($user))
				return $groupHome;
			else
				return $prefs['wikiHomePage'];
		}
		return $prefs['wikiHomePage'];
	}

	function sefurl($page, $with_next='', $all_langs='')
	{
		global $prefs, $smarty, $info;
		if ( basename($_SERVER['PHP_SELF']) == 'tiki-all_languages.php' ) {
			return 'tiki-all_languages.php?page='.urlencode($page);
		}

		$script_name = 'tiki-index.php';
		if ($all_langs == 'y') {
			$script_name = 'tiki-all_languages.php';
		}

		$href = "$script_name?page=" . urlencode($page);

		if (isset($prefs['feature_wiki_use_date_links']) && $prefs['feature_wiki_use_date_links'] == 'y') {
			if (isset($_REQUEST['date'])) {
				$href .= '&date='. urlencode($_REQUEST['date']);
			} else if (isset($_REQUEST['version'])) {
				$href .= '&date='. urlencode($info['lastModif']);
			}
		}

		if ($with_next) {
			$href .= '&amp;';
		}

		if ($prefs['feature_sefurl'] == 'y') {
			include_once('tiki-sefurl.php');
			return filter_out_sefurl($href, 'wiki');
		} else {
			return $href;
		}
	}

	function url_for_operation_on_a_page($script_name, $page, $with_next)
	{
		$href = "$script_name?page=".urlencode($page);
		if ($with_next) {
			$href .= '&amp;';
		}
		return $href;
	}

	function editpage_url($page, $with_next)
	{
		return $this->url_for_operation_on_a_page('tiki-editpage.php', $page, $with_next);
	}

	function move_attachments($old, $new)
	{
		$query = 'update `tiki_wiki_attachments` set `page`=? where `page`=?';
		$this->query($query, array($new, $old));
	}

	function duplicate_page($old, $new)
	{
		$query = 'insert into `tiki_pages`' .
						' (`pageName`,`hits`,`data`,`lastModif`,`comment`,`version`,`user`,`ip`,`description`,' .
						' `creator`,`page_size`,`is_html`,`created`, `flag`,`points`,`votes`,`pageRank`,`lang`,' .
						' `lockedby`) select ?,`hits`,`data`,`lastModif`,`comment`,`version`,`user`,`ip`,' .
						' `description`,`creator`,`page_size`,`is_html`,`created`, `flag`,`points`,`votes`' .
						',`pageRank`,`lang`,`lockedby` from `tiki_pages` where `pageName`=?';
		$this->query($query, array($new, $old));
	}

	function refresh_backlinks()
	{
		global $tikilib, $prefs;
		$tikilib->query('delete from tiki_links', array());

		if ($prefs['feature_backlinks'] == 'n')
			return;

		$listpages = $tikilib->list_pageNames();

		if ($listpages['cant']) {
			foreach ($listpages['data'] as $from) {
				$info = $tikilib->get_page_info($from['pageName']);
				$pages = $tikilib->get_pages($info['data'], true);
				foreach ($pages as $to=>$types) {
					$tikilib->replace_link($from['pageName'], $to, $types);
					//echo '<br />FROM:'.$from['pageName']." TO: $to "; print_r($types);
				}
			}
		}
	}

	function get_pages_contains($searchtext, $offset = 0, $maxRecords = -1, $sort_mode = 'pageName_asc', $categFilter = array())
	{
		$jail_bind = array();
		$jail_join = '';
		$jail_where = '';

		if ($categFilter) {
			global $categlib; require_once( 'lib/categories/categlib.php' );
			$categlib->getSqlJoin($categFilter, 'wiki page', '`tiki_pages`.`pageName`', $jail_join, $jail_where, $jail_bind);
		}

		$query = "select * from `tiki_pages` $jail_join where `tiki_pages`.`data` like ? $jail_where order by " . $this->convertSortMode($sort_mode);
		$bindvars = array('%' . $searchtext . '%');
		$bindvars = array_merge($bindvars, $jail_bind);
		$results = $this->fetchAll($query, $bindvars, $maxRecords, $offset);
		$ret['data'] = $results;
		$query_cant = "select count(*) from (select count(*) from `tiki_pages` $jail_join where `data` like ? $jail_where group by `page_id`) as `temp`";
		$ret['cant'] = $this->getOne($query_cant, $bindvars);

		return $ret;
	}

	function get_without_namespace($pageName)
	{
		global $prefs;

		if ((isset($prefs['namespace_enabled']) && $prefs['namespace_enabled'] == 'y') && $prefs['namespace_separator']) {
			$pos = strrpos($pageName, $prefs['namespace_separator']);

			if (false !== $pos) {
				return substr($pageName, $pos + strlen($prefs['namespace_separator']));
			} else {
				return $pageName;
			}
		} else {
			return $pageName;
		}
	}

	function get_explicit_namespace($pageName)
	{
		$attributes = TikiLib::lib('attribute')->get_attributes('wiki page', $pageName);
		return isset($attributes['tiki.wiki.namespace']) ? $attributes['tiki.wiki.namespace'] : '';
	}

	function set_explicit_namespace($pageName, $namespace)
	{
		TikiLib::lib('attribute')->set_attribute('wiki page', $pageName, 'tiki.wiki.namespace', $namespace);
	}

	function get_namespace($pageName)
	{
		global $prefs;

		if ($pageName
					&& $prefs['namespace_enabled'] == 'y'
					&& $prefs['namespace_separator']
		) {
			$explicit = $this->get_explicit_namespace($pageName);
	
			if ($explicit) {
				return $explicit;
			}

			$pos = strrpos($pageName, $prefs['namespace_separator']);

			if (false !== $pos) {
				return substr($pageName, 0, $pos);
			}
		}

		return false;
	}

	function get_readable($pageName)
	{
		global $prefs;

		if ($pageName
					&& $prefs['namespace_enabled'] == 'y'
					&& $prefs['namespace_separator']
		) {
			return str_replace($prefs['namespace_separator'], ' / ', $pageName);
		}

		return false;
	}

	function get_namespace_parts($pageName)
	{
		global $prefs;

		if ($namespace = $this->get_namespace($pageName)) {
			return explode($prefs['namespace_separator'], $namespace);
		}

		return array();
	}
}

global $wikilib;
$wikilib = new WikiLib;


class convertToTiki9
{
	var $parserlib;
	var $argumentParser;

	function __construct()
	{
		$this->parserlib = TikiLib::lib('parser');
		$this->argumentParser = new WikiParser_PluginArgumentParser();
	}


	//<!--below methods are used for converting objects
		//<!--Start for converting pages
	function convertPages()
	{
		$infos = $this->parserlib->fetchAll(
						'SELECT data, page_id' .
						' FROM tiki_pages' .
						' LEFT JOIN tiki_db_status ON tiki_db_status.objectId = tiki_pages.page_id' .
						' WHERE tiki_db_status.tableName = "tiki_pages" IS NULL'
		);

		foreach ($infos as $info) {
			if (!empty($info['data'])) {
				$converted = $this->convertData($info['data']);

				$this->updatePlugins($converted['fingerPrintsOld'], $converted['fingerPrintsNew']);

				$this->savePage($info['page_id'], $converted['data']);
			}
		}
	}

	function savePage($id, $data)
	{
		$status = $this->checkObjectStatus($id, 'tiki_pages');

		if (empty($status)) {
			$this->parserlib->query("UPDATE tiki_pages SET data = ? WHERE page_id = ?", array($data, $id));

			$this->saveObjectStatus($id, 'tiki_pages', 'conv9.0');
		}
	}
	//end for converting pages-->


	//<!--start for converting histories
	function convertPageHistoryFromPageAndVersion($page, $version)
	{
		$infos = $this->parserlib->fetchAll(
						'SELECT data, historyId' .
						' FROM tiki_history' .
						' LEFT JOIN tiki_db_status' .
						' ON tiki_db_status.objectId = tiki_history.historyId' .
						' WHERE tiki_db_status.tableName = "tiki_history" IS NULL' .
						' AND pageName = ? AND version = ?',
						array($page, $version)
		);

		foreach ($infos as $info) {
			if (!empty($info['data'])) {
				$converted = $this->convertData($info['data']);

				//update plugins first, if it failes, no problems with the page
				$this->updatePlugins($converted['fingerPrintsOld'], $converted['fingerPrintsNew']);

				$this->savePageHistory($info['historyId'], $converted['data']);
			}
		}
	}

	function convertPageHistories()
	{
		$infos = $this->parserlib->fetchAll(
						'SELECT data, historyId' .
						' FROM tiki_history' .
						' LEFT JOIN tiki_db_status ON tiki_db_status.objectId = tiki_history.historyId' .
						' WHERE tiki_db_status.tableName = "tiki_history" IS NULL'
		);

		foreach ($infos as $info) {
			if (!empty($info['data'])) {
				$converted = $this->convertData($info['data']);

				$this->updatePlugins($converted['fingerPrintsOld'], $converted['fingerPrintsNew']);

				$this->savePageHistory($info['historyId'], $converted['data']);
			}
		}
	}

	function savePageHistory($id, $data)
	{
		$status = $this->checkObjectStatus($id, 'tiki_history');

		if (empty($status)) {
			$this->parserlib->query(
							'UPDATE tiki_history' .
							' SET data = ?' .
							' WHERE historyId = ?',
							array($data, $id)
			);

			$this->saveObjectStatus($id, 'tiki_history', 'conv9.0');
		}
	}
		//end for converting histories-->



	//<!--start for converting modules
	function convertModules()
	{
		$infos = $this->parserlib->fetchAll(
						'SELECT data, name' .
						' FROM tiki_user_modules' .
						' LEFT JOIN tiki_db_status ON tiki_db_status.objectId = tiki_user_modules.name' .
						' WHERE tiki_db_status.tableName = "tiki_user_modules" IS NULL'
		);

		foreach ($infos as $info) {
			if (!empty($info['data'])) {
				$converted = $this->convertData($info['data']);

				$this->updatePlugins($converted['fingerPrintsOld'], $converted['fingerPrintsNew']);

				$this->saveModule($info['name'], $converted['data']);
			}
		}
	}

	function saveModule($name, $data)
	{
		$status = $this->checkObjectStatus($name, 'tiki_user_modules');

		if (empty($status)) {
			$this->parserlib->query('UPDATE tiki_user_modules SET data = ? WHERE name = ?', array($data, $name));

			$this->saveObjectStatus($name, 'tiki_user_modules', 'conv9.0');
		}
	}
		//end for converting modules-->
	//end conversion of objects-->



	//<!--below methods are used in tracking status of pages
	function saveObjectStatus($objectId, $tableName, $status = 'new9.0+')
	{
		$currentStatus = $this->parserlib->getOne("SELECT status FROM tiki_db_status WHERE objectId = ? AND tableName = ?", array($objectId, $tableName));

		if (empty($currentStatus)) {
			//Insert a status record if one doesn't exist
			$this->parserlib->query(
							'INSERT INTO tiki_db_status ( objectId,	tableName,	status )' .
							' VALUES (?, ?, ?)',
							array($objectId, 	$tableName,	$status)
			);
		} else {
			//update a status record, it already exists
			$this->parserlib->query(
							'UPDATE tiki_db_status' .
							' SET status = ?' .
							' WHERE objectId = ? AND tableName = ?',
							array($status, $objectId, $tableName)
			);
		}
	}

	function checkObjectStatus($objectId, $tableName)
	{
		return $this->parserlib->getOne(
						'SELECT status' .
						' FROM tiki_db_status' .
						' WHERE objectId = ? AND tableName = ?',
						array($objectId, $tableName)
		);
	}
	//end status methods-->


	//<!--below methods are used for conversion of plugins and data
	function updatePlugins($fingerPrintsOld, $fingerPrintsNew)
	{
		//here we find the old fingerprint and replace it with the new one
		for ($i = 0; $i < count($fingerPrintsOld); $i++) {
			if (!empty($fingerPrintsOld[$i]) && $fingerPrintsOld[$i] != $fingerPrintsNew[$i]) {
				//Remove any that may conflict with the new fingerprint, not sure how to fix this yet
				$this->parserlib->query("DELETE FROM tiki_plugin_security WHERE fingerprint = ?", array($fingerPrintsNew[$i]));

				// Now update fingerprint (if it exists)
				$this->parserlib->query("UPDATE tiki_plugin_security SET fingerprint = ? WHERE fingerprint = ?", array($fingerPrintsNew[$i], $fingerPrintsOld[$i]));
			}
		}
	}

	function convertData($data)
	{
		//we store the original matches because we are about to change and update them, we need to get their fingerprint
		$oldMatches = WikiParser_PluginMatcher::match($data);

		// HTML-decode pages
		$data = htmlspecialchars_decode($data);

		// find the plugins
		$matches = WikiParser_PluginMatcher::match($data);

		$replaced = array();

		$fingerPrintsOld = array();
		foreach ($oldMatches as $match) {
			$name = $match->getName();
			$meta = $this->parserlib->plugin_info($name);

			if (!empty($meta['validate'])) {	// only check fingerprints of plugins requiring validation

				$args = $this->argumentParser->parse($match->getArguments());

				//RobertPlummer - pre 9, latest findings from v8 is that the < and > chars are THE ONLY ones converted to &lt; and &gt; everything else seems to be decoded
				$body = $match->getBody();

				// jonnyb - pre 9.0, Tiki 6 (?) fingerprints are calculated with the undecoded body
				$fingerPrint = $this->parserlib->plugin_fingerprint($name, $meta, $body, $args);

				// so check the db for previously recorded plugins
				if (!$this->parserlib->getOne('SELECT COUNT(*) FROM tiki_plugin_security WHERE fingerprint = ?', array($fingerPrint))) {
					// jb but v 7 & 8 fingerprints may be calculated differently, so check both fully decoded and partially
					$body = htmlspecialchars_decode($body);
					$fingerPrint = $this->parserlib->plugin_fingerprint($name, $meta, $body, $args);

					if (!$this->parserlib->getOne('SELECT COUNT(*) FROM tiki_plugin_security WHERE fingerprint = ?', array($fingerPrint))) {
						$body = str_replace(array('<', '>'), array('&lt;', '&gt;'), $body);
						$fingerPrint = $this->parserlib->plugin_fingerprint($name, $meta, $body, $args);

						if (!$this->parserlib->getOne('SELECT COUNT(*) FROM tiki_plugin_security WHERE fingerprint = ?', array($fingerPrint))) {
							// old fingerprint not found - what to do? Might be worth trying &quot; chars too...
							$fingerPrint = '';
						}
					}
				}
				$fingerPrintsOld[] = $fingerPrint;
			}
		}

		$fingerPrintsNew = array();
		foreach ($matches as $match) {							// each plugin
			$name = $match->getName();
			$meta = $this->parserlib->plugin_info($name);
			$argsRaw = $match->getArguments();

			//Here we detect if a plugin was double encoded and this is the second decode
			if (preg_match("/&amp;&/i", $argsRaw) || preg_match("/&quot;/i", $argsRaw) || preg_match("/&gt;/i", $argsRaw)) { //try to detect double encoding
				$argsRaw = htmlspecialchars_decode($argsRaw);				// decode entities in the plugin args (usually &quot;)
			}

			$args = $this->argumentParser->parse($argsRaw);
			$plugin = (string) $match;
			$key = '§'.md5(TikiLib::genPass()).'§';					// by replace whole plugin with a guid

			$data = str_replace($plugin, $key, $data);

			$body = $match->getBody();									// leave the bodies alone
			$key2 = '§'.md5(TikiLib::genPass()).'§';					// by replacing it with a guid
			$plugin = str_replace($body, $key2, $plugin);

			//Here we detect if a plugin was double encoded and this is the second decode
			if (preg_match("/&amp;&/i", $plugin) || preg_match("/&quot;/i", $plugin) || preg_match("/&gt;/i", $plugin)) { //try to detect double encoding
				$plugin = htmlspecialchars_decode($plugin);				// decode entities in the plugin args (usually &quot;)
			}

			$plugin = str_replace($key2, $body, $plugin);				// finally put the body back

			$replaced['key'][] = $key;
			$replaced['data'][] = $plugin;								// store the decoded-args plugin for replacement later

			if (!empty($meta['validate'])) {							// only check fingerprints of plugins requiring validation
				$fingerPrintsNew[] = $this->parserlib->plugin_fingerprint($name, $meta, $body, $args);
			}
		}

		$this->parserlib->plugins_replace($data, $replaced);					// put the plugins back into the page

		return array(
			"data"=>$data,
			"fingerPrintsOld"=>$fingerPrintsOld,
			"fingerPrintsNew"=>$fingerPrintsNew
		);
	}

	//end conversion methods-->
}
