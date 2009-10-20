<?php

function prefs_feature_list() {
	return array(
		'feature_wiki' => array(
			'name' => tra('Wiki'),
			'description' => tra('Collaboratively authored documents with history of changes.'),
			'type' => 'flag',
			'help' => 'Wiki',
		),
		'feature_blogs' => array(
			'name' => tra('Blog'),
			'description' => tra('Online diaries or journals.'),
			'type' => 'flag',
			'help' => 'Blogs',
		),
		'feature_galleries' => array(
			'name' => tra('Image Gallery'),
			'description' => tra('Collections of graphic images for viewing or downloading (photo album)'),
			'type' => 'flag',
			'help' => 'Image+Gallery',
		),
		'feature_machine_translation' => array(
			'name' => tra('Machine Translation (by Google Translate)'),
			'description' => tra('Uses Google Translate to translate the content of wiki pages to other languages.'),
			'help' => 'Translating+Content',
			'warning' => tra('Experimental. This feature is still under development.'),
			'type' => 'flag',
		),	
		'feature_trackers' => array(
			'name' => tra('Trackers'),
			'description' => tra('Database & form generator'),
			'help' => 'Trackers',
			'type' => 'flag',
		),
		'feature_forums' => array(
			'name' => tra('Forums'),
			'description' => tra('Online discussions on a variety of topics. Threaded or flat.'),
			'help' => 'Forums',
			'type' => 'flag',
		),
		'feature_file_galleries' => array(
			'name' => tra('File Gallery'),
			'description' => tra('Computer files, videos or software for downloading. With check-in & check-out (lock)'),
			'help' => 'File+Gallery',
			'type' => 'flag',
		),
		'feature_articles' => array(
			'name' => tra('Articles'),
			'description' => tra('Articles can be used for date-specific news and announcements. You can configure articles to automatically publish and expire at specific times or to require that submissions be approved before becoming "live."'),
			'help' => 'Article',
			'type' => 'flag',
		),
		'feature_polls' => array(
			'name' => tra('Polls'),
			'description' => tra('Brief list of votable options; appears in module (left or right column)'),
			'help' => 'Poll',
			'type' => 'flag',
		),
		'feature_newsletters' => array(
			'name' => tra('Newletters'),
			'description' => tra('Content mailed to registered users.'),
			'help' => 'Newsletters',
			'type' => 'flag',
		),
		'feature_calendar' => array(
			'name' => tra('Calendar'),
			'description' => tra('Events calendar with public, private and group channels.'),
			'help' => 'Calendar',
			'type' => 'flag',
		),
		'feature_banners' => array(
			'name' => tra('Banners'),
			'description' => tra('Insert, track, and manage advertising banners.'),
			'help' => 'Banners',
			'type' => 'flag',
		),
		'feature_categories' => array(
			'name' => tra('Category'),
			'description' => tra('Global category system. Items of different types (wiki pages, articles, tracker items, etc) can be added to one or many categories. Categories can have permissions.'),
			'help' => 'Category',
			'type' => 'flag',
		),
		'feature_score' => array(
			'name' => tra('Score'),
			'description' => tra('Score is a game to motivate participants to increase their contribution by comparing to other users.'),
			'help' => 'Score',
			'type' => 'flag',
		),
		'feature_search' => array(
			'name' => tra('Search'),
			'description' => tra('Enables searching for content on the website.'),
			'help' => 'Search',
			'type' => 'flag',
		),
		'feature_freetags' => array(
			'name' => tra('Freetags'),
			'description' => tra('Allows to set tags on pages and various objects within the website and generate tag cloud navigation patterns.'),
			'help' => 'Tags',
			'type' => 'flag',
		),
		'feature_actionlog' => array(
			'name' => tra('Action Log'),
			'description' => tra('Allows to keep track of what users are doing and produce reports on a per-user or per-category basis.'),
			'help' => 'Action+Log',
			'type' => 'flag',
		),
		'feature_contribution' => array(
			'name' => tra('Contribution'),
			'description' => tra('Allows users to specify the type of contribution they are making while editing objects. The contributions are then displayed as color-coded in history and other reports.'),
			'help' => 'Contribution',
			'type' => 'flag',
		),
		'feature_multilingual' => array(
			'name' => tra('Multilingual'),
			'description' => tra('Enables internationalization features and multilingual support for then entire site.'),
			'help' => 'Internationalization',
			'type' => 'flag',
		),
		'feature_faqs' => array(
			'name' => tra('FAQ'),
			'description' => tra('Frequently asked questions and answers'),
			'help' => 'FAQ',
			'type' => 'flag',
		),
		'feature_surveys' => array(
			'name' => tra('Surveys'),
			'description' => tra('Questionnaire with multiple choice or open ended question'),
			'help' => 'Surveys',
			'type' => 'flag',
		),
		'feature_directory' => array(
			'name' => tra('Directory'),
			'description' => tra('User-submitted Web links'),
			'help' => 'Directory',
			'type' => 'flag',
		),
		'feature_quizzes' => array(
			'name' => tra('Quizzes '),
			'description' => tra('Timed questionnaire with recorded scores.'),
			'help' => 'Quizzes',
			'type' => 'flag',
		),
		'feature_featuredLinks' => array(
			'name' => tra('Featured links'),
			'description' => tra('Simple menu system which can optionally add an external web page in an iframe'),
			'help' => 'Featured+links',
			'type' => 'flag',
		),
		'feature_copyright' => array(
			'name' => tra('Copyright'),
			'description' => tra('The Copyright Management System (or ©MS) is a way of licensing your content'),
			'help' => 'Copyright',
			'type' => 'flag',
		),
		'feature_multimedia' => array(
			'name' => tra('Multimedia'),
			'description' => tra('The applet is designed to read MP3 or FLV file'),
			'help' => 'Multimedia',
			'type' => 'flag',
			'warning' => tra('Experimental. This feature is not actively maintained.'),
		),
		'feature_shoutbox' => array(
			'name' => tra('Shoutbox'),
			'description' => tra('Quick comment (graffiti) box. Like a group chat, but not in real time.'),
			'help' => 'Shoutbox',
			'type' => 'flag',
		),
		'feature_maps' => array(
			'name' => tra('Maps'),
			'description' => tra('Navigable, interactive maps with user-selectable layers'),
			'help' => 'Maps',
			'warning' => tra('Requires mapserver'),
			'type' => 'flag',
		),
		'feature_gmap' => array(
			'name' => tra('Google Maps'),
			'description' => tra('Interactive use of Google Maps'),
			'help' => 'GMap',
			'type' => 'flag',
		),
		'feature_live_support' => array(
			'name' => tra('Live support system'),
			'description' => tra('One-on-one chatting with customer'),
			'help' => 'Live+Support',
			'type' => 'flag',
		),
		'feature_tell_a_friend' => array(
			'name' => tra('Tell a Friend'),
			'description' => tra('Add a link "Email this page" in all the pages'),
			'help' => 'Tell+a+Friend',
			'type' => 'flag',
		),
		'feature_html_pages' => array(
			'name' => tra('HTML pages'),
			'description' => tra('Static and dynamic HTML content'),
			'help' => 'HTML+Pages',
			'warning' => tra('HTML can be used in wiki pages. This is a separate feature.'),
			'type' => 'flag',
		),
		'feature_contact' => array(
			'name' => tra('Contact Us'),
			'description' => tra('Basic form from visitor to admin'),
			'help' => 'Contact+us',
			'type' => 'flag',
		),
		'feature_minichat' => array(
			'name' => tra('Minichat'),
			'description' => tra('Real-time group text chatting'),
			'help' => 'Minichat',
			'type' => 'flag',
		),
		'feature_comments_moderation' => array(
			'name' => tra('Comments Moderation '),
			'description' => tra('An admin must validate a comment before it is visible'),
			'help' => 'Comments',
			'type' => 'flag',
		),
		'feature_comments_locking' => array(
			'name' => tra('Comments Locking'),
			'description' => tra('Comments can be closed (no new comments)'),
			'help' => 'Comments',
			'type' => 'flag',
		),
		'feature_comments_post_as_anonymous' => array(
			'name' => tra('Allow to post comments as Anonymous'),
			'description' => tra('Permit anonymous visitors to add a comment without needing to create an account'),
			'help' => 'Comments',
			'type' => 'flag',
		),
		'feature_wiki_description' => array(
			'name' => tra('Display page description'),
			'description' => tra('Display the page description below the heading when viewing the page.'),
			'type' => 'flag',
		),
		'feature_page_title' => array(
			'name' => tra('Display page title'),
			'description' => tra('Display the page name at the top of each page. If not enabled, the content must be structured to contain a header.'),
			'type' => 'flag',
		),
		'feature_wiki_pageid' => array(
			'name' => tra('Display page ID'),
			'description' => tra('Display the internal page ID when viewing the page.'),
			'type' => 'flag',
		),
		'feature_wiki_icache' => array(
			'name' => tra('Individual wiki cache'),
			'description' => tra('Allow users to change the duration of the cache on a per-page basis.'),
			'type' => 'flag',
		),
		'feature_jscalendar' => array(
			'name' => tra('JS Calendar'),
			'description' => tra('JavaScript popup date selector.'),
			'help' => 'JS+Calendar',
			'type' => 'flag',
		),
		'feature_phplayers' => array(
			'name' => tra('PHPLayers'),
			'description' => tra('PhpLayers Dynamic menus.'),
			'help' => 'http://themes.tikiwiki.org/PhpLayersMenu',
			'type' => 'flag',
		),
		'feature_htmlpurifier_output' => array(
			'name' => tra('Ouput Should be HTMLPurified'),
			'description' => tra('This enable HTPMPurifier on outputs to filter remaining security problems like XSS.'),
			'help' => 'http://doc.tikiwiki.org/HTMLPurifier',
			'warning' => tra('Experimental. This feature is still under development.'),
			'type' => 'flag',
			'default' => 'n',
		),
		'feature_fullscreen' => array(
			'name' => tra('Full Screen'),
			'description' => tra('Allow users to activate fullscreen mode.'),
			'help' => 'Fullscreen',
			'type' => 'flag',
		),
		'feature_cssmenus' => array(
			'name' => tra('Css Menus'),
			'description' => tra('Css Menus (suckerfish).'),
			'help' => 'Menus',
			'type' => 'flag',
		),
		'feature_shadowbox' => array(
			'name' => tra('Shadowbox'),
			'description' => tra('Shadowbox'),
			'help' => 'Shadowbox',
			'type' => 'flag',
		),
		'feature_quick_object_perms' => array(
			'name' => tra('Quick Permission Assignment'),
			'description' => tra('Quickperms allow to define classes of privileges and grant them to roles on objects.'),
			'help' => 'Quickperms',
			'type' => 'flag',
		),
		'feature_purifier' => array(
			'name' => tra('HTML Purifier'),
			'description' => tra('HTML Purifier'),
			'help' => 'HTML Purifier',
			'type' => 'flag',
		),
		'feature_ajax' => array(
			'name' => tra('Ajax'),
			'description' => tra('Ajax'),
			'help' => 'Ajax',
			'type' => 'flag',
		),
		'feature_mobile' => array(
			'name' => tra('Mobile'),
			'description' => tra('Mobile'),
			'help' => 'http://mobile.tikiwiki.org',
			'type' => 'flag',
		),
		'feature_morcego' => array(
			'name' => tra('Morcego 3D browser'),
			'description' => tra('Morcego 3D browser'),
			'help' => 'Wiki+3D',
			'type' => 'flag',
		),
		'feature_webmail' => array(
			'name' => tra('Webmail'),
			'description' => tra('Webmail'),
			'help' => 'Webmail',
			'type' => 'flag',
		),
		'feature_intertiki' => array(
			'name' => tra('Intertiki'),
			'description' => tra('Intertiki'),
			'help' => 'Intertiki',
			'type' => 'flag',
		),
		'feature_mailin' => array(
			'name' => tra('Mail-in'),
			'description' => tra('Mail-in'),
			'help' => 'Mail-in',
			'type' => 'flag',
		),
		'feature_wiki_mindmap' => array(
			'name' => tra('Mindmap'),
			'description' => tra('Mindmap'),
			'help' => 'MindMap',
			'type' => 'flag',
		),
		'feature_print_indexed' => array(
			'name' => tra('Print Indexed'),
			'description' => tra('Print Indexed'),
			'help' => 'Print+Indexed',
			'type' => 'flag',
		),
		'feature_sefurl' => array(
			'name' => tra('Search engine friendly url'),
			'description' => tra('Search engine friendly url'),
			'help' => 'Rewrite+Rules',
			'type' => 'flag',
		),
		'feature_sheet' => array(
			'name' => tra('SpreadSheet'),
			'description' => tra('SpreadSheet'),
			'help' => 'SpreadSheet',
			'type' => 'flag',
		),
		'feature_wysiwyg' => array(
			'name' => tra('Wysiwyg editor'),
			'description' => tra('Wysiwyg editor'),
			'help' => 'Wysiwyg',
			'type' => 'flag',
		),
		'feature_ajax_autosave' => array(
			'name' => tra('Ajax auto-save'),
			'description' => tra('Ajax auto-save'),
			'help' => 'Lost+Edit+Protection',
			'type' => 'flag',
		),
		'feature_wiki_save_draft' => array(
			'name' => tra('Save draft'),
			'warning' => tra('Requires AJAX (experimental)'),
			'dependencies' => array(
				'feature_ajax',
			),
			'type' => 'flag',
		),	
		'feature_workspaces' => array(
			'name' => tra('Workspaces'),
			'description' => tra('Workspace'),
			'help' => 'Workspace',
			'type' => 'flag',
		),
		'feature_kaltura' => array(
			'name' => tra('Kaltura'),
			'description' => tra('Kaltura'),
			'help' => 'Kaltura',
			'type' => 'flag',
		),
		'feature_friends' => array(
			'name' => tra('Friendship Network'),
			'description' => tra('Users can identify other users as their friends'),
			'warning' => tra('Neglected feature'),
			'help' => 'Friendship',
			'type' => 'flag',
		),	
		'feature_banning' => array(
			'name' => tra('Banning system'),
			'description' => tra('Banning system'),
			'help' => 'Banning',
			'type' => 'flag',
		),
		'feature_stats' => array(
			'name' => tra('Stats'),
			'description' => tra('Stats'),
			'help' => 'Stats',
			'type' => 'flag',
		),
		'feature_action_calendar' => array(
			'name' => tra('Action calendar'),
			'description' => tra('Action calendar'),
			'help' => 'Action+Calendar',
			'type' => 'flag',
		),
		'feature_referer_stats' => array(
			'name' => tra('Referer Stats'),
			'description' => tra('Referer Stats'),
			'help' => 'Stats',
			'type' => 'flag',
		),
		'feature_redirect_on_error' => array(
			'name' => tra('Redirect On Error'),
			'description' => tra('Redirect to a similar wiki page if an exact match is not found.'),
			'help' => 'Redirect+On+Error',
			'type' => 'flag',
		),
		'feature_comm' => array(
			'name' => tra('Communications (send/receive objects)'),
			'description' => tra('Communications (send/receive objects)'),
			'help' => 'Communication+Center',
			'type' => 'flag',
		),
		'feature_custom_home' => array(
			'name' => tra('Custom Home'),
			'description' => tra('Custom Home'),
			'help' => 'Custom+Home',
			'type' => 'flag',
		),
		'feature_mytiki' => array(
			'name' => tra("Display 'MyTiki' in the application menu"),
			'description' => tra("Display 'MyTiki' in the application menu"),
			'help' => 'MyTiki',
			'type' => 'flag',
		),
		'feature_minical' => array(
			'name' => tra('Mini Calendar'),
			'description' => tra('Mini Calendar'),
			'help' => 'Calendar',
			'type' => 'flag',
		),
		'feature_userPreferences' => array(
			'name' => tra('User Preferences Screen'),
			'description' => tra('User Preferences Screen'),
			'help' => 'User+Preferences',
			'type' => 'flag',
		),
		'feature_notepad' => array(
			'name' => tra('User Notepad'),
			'description' => tra('User Notepad'),
			'help' => 'Notepad',
			'type' => 'flag',
		),
		'feature_user_bookmarks' => array(
			'name' => tra('User Bookmarks'),
			'description' => tra('User Bookmarks'),
			'help' => 'Bookmarks',
			'type' => 'flag',
		),
		'feature_contacts' => array(
			'name' => tra('User Contacts'),
			'description' => tra('User Contacts'),
			'help' => 'Contacts',
			'type' => 'flag',
		),
		'feature_user_watches' => array(
			'name' => tra('User Watches'),
			'description' => tra('User Watches'),
			'help' => 'User+Watches',
			'type' => 'flag',
		),
		'feature_group_watches' => array(
			'name' => tra('Group Watches'),
			'description' => tra('Group Watches'),
			'help' => 'Group+Watches',
			'type' => 'flag',
		),
		'feature_daily_report_watches' => array(
			'name' => tra('Daily Reports for User Watches'),
			'description' => tra('Daily Reports for User Watches'),
			'help' => 'Daily+Reports',
			'type' => 'flag',
		),
		'feature_user_watches_translations' => array(
			'name' => tra('User Watches Translations'),
			'description' => tra('User Watches Translations'),
			'help' => 'User+Watches',
			'type' => 'flag',
		),
		'feature_usermenu' => array(
			'name' => tra('User Menu'),
			'description' => tra('User Menu'),
			'help' => 'User+Menu',
			'type' => 'flag',
		),
		'feature_tasks' => array(
			'name' => tra('User Tasks'),
			'description' => tra('User Tasks'),
			'help' => 'Task',
			'type' => 'flag',
		),
		'feature_messages' => array(
			'name' => tra('User Messages'),
			'description' => tra('User Messages'),
			'help' => 'Inter-user+Messages',
			'type' => 'flag',
		),
		'feature_userfiles' => array(
			'name' => tra('User Files'),
			'description' => tra('User Files'),
			'help' => 'User+Files',
			'type' => 'flag',
		),
		'feature_userlevels' => array(
			'name' => tra('User Levels'),
			'description' => tra('User Levels'),
			'help' => 'User+Levels',
			'type' => 'flag',
		),
		'feature_groupalert' => array(
			'name' => tra('Group Alert'),
			'description' => tra('Group Alert'),
			'help' => 'Group+Alert',
			'type' => 'flag',
		),
		'feature_integrator' => array(
			'name' => tra('Integrator'),
			'description' => tra('Integrator'),
			'help' => 'Integrator',
			'type' => 'flag',
		),
		'feature_xmlrpc' => array(
			'name' => tra('XMLRPC API'),
			'description' => tra('XMLRPC API'),
			'help' => 'Xmlrpc',
			'type' => 'flag',
		),
		'feature_debug_console' => array(
			'name' => tra('Debugger Console'),
			'description' => tra('Debugger Console'),
			'help' => 'Debugger+Console',
			'type' => 'flag',
		),
		'feature_tikitests' => array(
			'name' => tra('TikiTests'),
			'description' => tra('TikiTests'),
			'help' => 'TikiTests',
			'type' => 'flag',
		),
		'feature_use_minified_scripts' => array(
			'name' => tra('Use Minified Scripts'),
			'description' => tra('Use Minified Scripts'),
			'help' => 'MinifiedScripts',
			'type' => 'flag',
		),
		'feature_version_checks' => array(
			'name' => tra('Check for updates automatically'),
			'description' => tra('TikiWiki will check for updates when you access the main Administration page'),
			'type' => 'flag',
		),
		'feature_pear_date' => array(
			'name' => tra('Use PEAR::Date library'),
			'description' => tra('Use PEAR::Date library'),
			'type' => 'flag',
		),
		'feature_ticketlib' => array(
			'name' => tra('Require confirmation if possible CSRF detected'),
			'description' => tra('Require confirmation if possible CSRF detected'),
			'type' => 'flag',
		),
		'feature_ticketlib2' => array(
			'name' => tra('Protect against CSRF with a ticket'),
			'description' => tra('Protect against CSRF with a ticket'),
			'type' => 'flag',
		),
		'feature_detect_language' => array(
			'name' => tra('Detect browser language'),
			'description' => tra('Lookup the user\'s preferred language through browser preferences.'),
			'type' => 'flag',
		),
		'feature_best_language' => array(
			'name' => tra('Show pages in user\'s preferred language'),
			'description' => tra('When accessing a page which has an equivalent in the user\'s preferred language, favor the translated page.'),
			'type' => 'flag',
			'dependencies' => array(
				'feature_userPreferences',
			),
		),
		'feature_sync_language' => array(
			'name' => tra('Changing the page language also changes the site language'),
			'type' => 'flag',
		),
		'feature_translation' => array(
			'name' => tra('Translation assistant'),
			'description' => tra('Track translation operations between pages.'),
			'help' => 'Translating+Tiki+content',
			'type' => 'flag',
		),
		'feature_urgent_translation' => array(
			'name' => tra('Urgent translation notifications'),
			'description' => tra('Allow to flag changes as urgent, leading translations to be marked with a notice visible to all users.'),
			'type' => 'flag',
		),
		'feature_translation_incomplete_notice' => array(
			'name' => tra('Incomplete translation notice'),
			'description' => tra('When a page is translated to a new language, a notice will be automatically be inserted into the page to indicate that the translation is not yet complete.'),
			'type' => 'flag',
		),
		'feature_multilingual_structures' => array(
			'name' => tra('Multilingual structures'),
			'description' => tra('Structures to lookup equivalent pages in other languages. May cause performance problems on larger structures.'),
			'type' => 'flag',
			'dependencies' => array(
				'feature_wiki_structure',
			),
		),
		'feature_multilingual_one_page' => array(
			'name' => tra('Display all languages in a single page'),
			'description' => tra('List all languages as a language option in the page language drop list to see all languages at once.'),
			'type' => 'flag',
		),
		'feature_obzip' => array(
			'name' => tra('GZip output'),
			'description' => tra('GZip output'),
			'help' => 'Compression',
			'type' => 'flag',
		),
		'feature_help' => array(
			'name' => tra('Help System'),
			'description' => tra('Help System'),
			'help' => 'Documentation',
			'type' => 'flag',
		),
		'feature_display_my_to_others' => array(
			'name' => tra("Show user's contribution on the user information page"),
			'description' => tra("Show user's contribution on the user information page"),
			'help' => 'User+Preferences',
			'type' => 'flag',
		),
		'feature_babelfish' => array(
			'name' => tra('Translation URLs'),
			'description' => tra('Show clickable URLs to translate the page to another language using Babel Fish website.'),
			'type' => 'flag',
		),
		'feature_babelfish_logo' => array(
			'name' => tra('Translation icons'),
			'description' => tra('Show clickable icons to translate the page to another language using Babelfish website.'),
			'type' => 'flag',
		),
		'feature_smileys' => array(
			'name' => tra('Smileys'),
			'description' => tra('Also known as emoticons'),
			'help' => 'Smileys',
			'type' => 'flag',
		),
		'feature_dynamic_content' => array(
			'name' => tra('Dynamic Content System'),
			'description' => tra('Bloc of content which can be reused and programmed (timed)'),
			'help' => 'Dynamic+Content',
			'type' => 'flag',
		),
		'feature_filegals_manager' => array(
			'name' => tra('Use File Galleries to store pictures'),
			'type' => 'flag',
		),
		'feature_wiki_ext_icon' => array(
			'name' => tra('External link icon'),
			'type' => 'flag',
		),
		'feature_semantic' => array(
			'name' => tra('Semantic links'),
			'description' => tra('Going beyond Backlinks, allows to define some semantic relationships between wiki pages'),
			'help' => 'Semantic',
			'type' => 'flag',
			'dependencies' => array(
				'feature_backlinks',
			),
		),
		'feature_webservices' => array(
			'name' => tra('Web Services'),
			'description' => tra('Can consume webservices in JSON or YAML'),
			'help' => 'WebServices',
			'type' => 'flag',
		),
		'feature_menusfolderstyle' => array(
			'name' => tra('Display menus as folders'),
			'type' => 'flag',
		),
		'feature_breadcrumbs' => array(
			'name' => tra('Breadcrumbs'),
			'description' => tra('Uses Google Translate to translate the content of wiki pages to other languages.'),
			'help' => 'Breadcrumbs',
			'warning' => tra('Neglected feature'),
			'type' => 'flag',
		),	
		'feature_antibot' => array(
			'name' => tra('Anonymous editors must enter anti-bot code (CAPTCHA)'),
			'help' => 'Spam+protection',
			'type' => 'flag',
		),	
		'feature_wiki_protect_email' => array(
			'name' => tra('Protect email against spam'),
			'help' => 'Spam+protection',
			'type' => 'flag',
		),	
		'feature_sitead' => array(
			'name' => tra('Activate'),
			'type' => 'flag',
		),	
		'feature_poll_anonymous' => array(
			'name' => tra('Anonymous voting'),
			'type' => 'flag',
		),	
		'feature_poll_revote' => array(
			'name' => tra('Allow re-voting'),
			'type' => 'flag',
		),	
		'feature_poll_comments' => array(
			'name' => tra('Comments'),
			'type' => 'flag',
		),	
		'feature_faq_comments' => array(
			'name' => tra('Comments'),
			'type' => 'flag',
		),	
		'feature_sefurl' => array(
			'name' => tra('Search engine friendly url'),
			'help' => 'Rewrite+Rules',
			'type' => 'flag',
		),	
		'feature_sefurl_filter' => array(
			'name' => tra('Search engine friendly url Postfilter'),
			'help' => 'Rewrite+Rules',
			'type' => 'flag',
		),	
		'feature_sefurl_title_article' => array(
			'name' => tra('Display article title in the sefurl'),
			'type' => 'flag',
		),	
		'feature_sefurl_title_blog' => array(
			'name' => tra('Display blog title in the sefurl'),
			'type' => 'flag',
		),	
		'feature_modulecontrols' => array(
			'name' => tra('Show module controls'),
			'help' => 'Module+Control',
			'type' => 'flag',
		),	
		'feature_perspective' => array(
			'name' => tra('Perspectives'),
			'description' => tra('Permits to override preferences.'),
			'help' => 'Perspectives',
			'type' => 'flag',
		),
		'feature_wiki_replace' => array(
			'name' => tra('Search and replace'),
			'description' => tra('Permits find and replace of content in the edit box'),
			'help' => 'Regex+search+and+replace',
			'type' => 'flag',
		),
		'feature_submissions' => array(
			'name' => tra('Submissions'),
			'help' => 'Articles',
			'type' => 'flag',
		),
		'feature_cms_rankings' => array(
			'name' => tra('Rankings'),
			'type' => 'flag',
		),
		'feature_article_comments' => array(
			'name' => tra('Comments'),
			'type' => 'flag',
		),
		'feature_cms_templates' => array(
			'name' => tra('Content templates'),
			'type' => 'flag',
			'help' => 'Content+Template',
		),
		'feature_cms_print' => array(
			'name' => tra('Print'),
			'type' => 'flag',
		),
		'feature_cms_emails' => array(
			'name' => tra('Specify notification emails when creating articles'),
			'type' => 'flag',
		),
		'feature_categorypath' => array(
			'name' => tra('Category path'),
			'type' => 'flag',
		),
		'feature_categoryobjects' => array(
			'name' => tra('Show category objects'),
			'type' => 'flag',
		),
		'feature_category_use_phplayers' => array(
			'name' => tra('Use PHPLayers for category browser'),
			'type' => 'flag',
			'dependencies' => array(
				'feature_phplayers',
			),
		),
		'feature_search_show_forbidden_cat' => array(
			'name' => tra('Ignore category viewing restrictions'),
			'type' => 'flag',
			'help' => 'WYSIWYCA+Search',
		),
		'feature_category_reinforce' => array(
			'name' => tra("Permission to all (not just any) of an object's categories is required for access"),
			'type' => 'flag',
		),
		'feature_wiki_screencasts' => array(
			'name' => tra('Screencasts'),
			'description' => tra('Allow to upload screencasts from wiki edit. Screencasts can be uploaded locally or on a WebDAV share.'),
			'type' => 'flag',
		),
		'feature_wiki_screencasts_base' => array(
			'name' => tra('Screencasts upload location'),
			'description' => tra('Local path or webdav path to the file upload location.'),
			'type' => 'text',
			'filter' => 'url',
		),
		'feature_wiki_screencasts_httpbase' => array(
			'name' => tra('Screencasts HTTP prefix'),
			'description' => tra('Prefix to use for the files when generating a link to it.'),
			'type' => 'text',
			'filter' => 'url',
		),
		'feature_wiki_screencasts_upload_type' => array(
			'name' => tra('Screencast upload type'),
			'description' => tra('Mode used to upload files. WebDav is used to upload to remote servers.'),
			'type' => 'list',
			'options' => array(
				'local' => tra('Local'),
				'webdav' => tra('Webdav'),
			),
		),
		'feature_wiki_screencasts_user' => array(
			'name' => tra('Screencasts authentication user'),
			'description' => tra('When using webdav to upload files, used as the username of the authentication credentials.'),
			'type' => 'text',
		),
		'feature_wiki_screencasts_pass' => array(
			'name' => tra('Screencasts authentication password'),
			'description' => tra('When using webdav to upload files, used as the password of the authentication credentials.'),
			'type' => 'text',
		),
		'feature_wiki_screencasts_max_size' => array(
			'name' => tra('Screencasts max file size'),
			'hint' => tra('Value provided in bytes'),
			'description' => tra('Maximum file size used for screencasts.'),
			'type' => 'text',
			'filter' => 'digits',
		),
	);
}
