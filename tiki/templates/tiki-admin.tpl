{popup_init src="lib/overlib.js"}
<h2><a class="pagetitle" href="tiki-admin.php">{tr}Administration{/tr}</a>
{if $feature_help}
<a title='help' href='http://tikiwiki.org/tiki-index.php?page=TikiAdminSettings' target="help"><img
border='0' src='img/icons/help.gif' alt='help' /></a>
{/if}
</h2>
{* The rest determines which page to include using "page" GET parameter. Default : list-sections
Add a value in first check when you create a new admin page. *}
{if in_array($smarty.get.page, array("features", "general", "login", "wiki", "gal", "fgal", "cms", 
"polls", "polls", "blogs", "forums", "faqs", "trackers", "webmail", "rss", "directory", "userfiles", "maps"))}
  {assign var="include" value=$smarty.get.page}
{else}
  {assign var="include" value="list-sections"}
{/if}
{if $include != "list-sections"}
  {include file="tiki-admin-include-anchors.tpl"}
{/if}
{include file="tiki-admin-include-$include.tpl"}