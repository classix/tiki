{* $Header: /cvsroot/tikiwiki/tiki/templates/modules/mod-last_blog_posts.tpl,v 1.5 2003-10-09 07:34:29 cdx Exp $ *}

{if $feature_blogs eq 'y'}
<div class="box">
<div class="box-title">
{include file="modules/module-title.tpl" module_title="{tr}Last blog posts{/tr}" module_name="last_blog_posts"}
</div>
<div class="box-data">
<table  border="0" cellpadding="0" cellspacing="0">
{section name=ix loop=$modLastBlogPosts}
<tr><td  width="5%" class="module" valign="top">{$smarty.section.ix.index_next})</td><td class="module"><a class="linkmodule" href="tiki-view_blog.php?blogId={$modLastBlogPosts[ix].blogId}"><b>{$modLastBlogPosts[ix].blogTitle}:</b><br/>
{if $modLastBlogPostsTitle eq "title" and $modLastBlogPosts[ix].title}
{$modLastBlogPosts[ix].title}
{else}
{$modLastBlogPosts[ix].created|tiki_short_datetime}
{/if}
</a></td></tr>
{/section}
</table>
</div>
</div>
{/if}