<a class="pagetitle" href="tiki-list_blogs.php">{tr}Blogs{/tr}</a><br/><br/>
{if $tiki_p_create_blogs eq 'y'}
<a class="bloglink" href="tiki-edit_blog.php">edit blog</a>
{/if}
<br/><br/>
<div align="center">
<table class="findtable">
<tr><td class="findtable">{tr}Find{/tr}</td>
   <td class="findtable">
   <form method="get" action="tiki-list_blogs.php">
     <input type="text" name="find" value="{$find}" />
     <input type="submit" value="{tr}find{/tr}" name="search" />
     <input type="hidden" name="sort_mode" value="{$sort_mode}" />
   </form>
   </td>
</tr>
</table>
<table class="bloglist">
<tr>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'title_desc'}title_asc{else}title_desc{/if}">{tr}Title{/tr}</a></td>
<td class="bloglistheading">{tr}Description{/tr}</td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'created_desc'}created_asc{else}created_desc{/if}">{tr}Created{/tr}</a></td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'lastModif_desc'}lastModif_asc{else}lastModif_desc{/if}">{tr}Last Modified{/tr}</a></td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'user_desc'}user_asc{else}user_desc{/if}">{tr}User{/tr}</a></td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'public_desc'}public_asc{else}public_desc{/if}">{tr}Public{/tr}</a></td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'posts_desc'}posts_asc{else}posts_desc{/if}">{tr}Posts{/tr}</a></td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'hits_desc'}hits_asc{else}hits_desc{/if}">{tr}Visits{/tr}</a></td>
<td class="bloglistheading"><a class="bloglistheading" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'activity_desc'}activity_asc{else}activity_desc{/if}">{tr}Activity{/tr}</a></td>
<td class="bloglistheading">{tr}Action{/tr}</td>
</tr>
{section name=changes loop=$listpages}
<tr>
{if $smarty.section.changes.index % 2}
<td class="bloglistnameodd">&nbsp;{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_read_blog eq 'y' ) }<a class="blogname" href="tiki-view_blog.php?blogId={$listpages[changes].blogId}">{/if}{$listpages[changes].title|truncate:20:"(...)":true}{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_read_blog eq 'y' ) }</a>{/if}&nbsp;</td>
<td class="bloglistdescriptionodd">&nbsp;{$listpages[changes].description}&nbsp;</td>
<td class="bloglistcreatedodd">&nbsp;{$listpages[changes].created|tiki_short_datetime}&nbsp;</td>
<td class="bloglistlastModifodd">&nbsp;{$listpages[changes].lastModif|tiki_short_datetime}&nbsp;</td>
<td class="bloglistuserodd">&nbsp;{$listpages[changes].user}&nbsp;</td>
<td class="bloglistpublicodd">&nbsp;{$listpages[changes].public}&nbsp;</td>
<td class="bloglistpostsodd">&nbsp;{$listpages[changes].posts}&nbsp;</td>
<td class="bloglistvisitsodd">&nbsp;{$listpages[changes].hits}&nbsp;</td>
<td class="bloglistactivityodd">&nbsp;{$listpages[changes].activity}&nbsp;</td>
<td class="bloglistactionsodd">
{if $tiki_p_blog_post eq 'y'}
{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_blog_post eq 'y' ) }
{if ($user and $listpages[changes].user eq $user) or ($tiki_p_blog_admin eq 'y') or ($listpages[changes].public eq 'y')}
<a class="bloglink" href="tiki-blog_post.php?blogId={$listpages[changes].blogId}">{tr}Post{/tr}</a>
{/if}
{/if}
{/if}
{if ($user and $listpages[changes].user eq $user) or ($tiki_p_blog_admin eq 'y')}
{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_blog_create_blog eq 'y' ) }
<a class="bloglink" href="tiki-edit_blog.php?blogId={$listpages[changes].blogId}">{tr}Edit{/tr}</a>
{/if}
{/if}
{if ($user and $listpages[changes].user eq $user) or ($tiki_p_blog_admin eq 'y')}
{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_blog_create_blog eq 'y' ) }
<a class="bloglink" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;remove={$listpages[changes].blogId}">{tr}Remove{/tr}</a>
{/if}
{/if}
{if $tiki_p_admin eq 'y'}
    {if $listpages[changes].individual eq 'y'}({/if}<a class="bloglink" href="tiki-objectpermissions.php?objectName=blog%20{$listpages[changes].title}&amp;objectType=blog&amp;permType=blogs&amp;objectId={$listpages[changes].blogId}">{tr}perms{/tr}</a>{if $listpages[changes].individual eq 'y'}){/if}
{/if}
</td>
{else}
<td class="bloglistnameeven">&nbsp;{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_read_blog eq 'y' ) }<a class="blogname" href="tiki-view_blog.php?blogId={$listpages[changes].blogId}">{/if}{$listpages[changes].title|truncate:20:"(...)":true}{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_read_blog eq 'y' ) }</a>{/if}&nbsp;</td>
<td class="bloglistdescriptioneven">&nbsp;{$listpages[changes].description}&nbsp;</td>
<td class="bloglistcreatedeven">&nbsp;{$listpages[changes].created|tiki_short_datetime}&nbsp;</td>
<td class="bloglistlastModifeven">&nbsp;{$listpages[changes].lastModif|tiki_short_datetime}&nbsp;</td>
<td class="bloglistusereven">&nbsp;{$listpages[changes].user}&nbsp;</td>
<td class="bloglistpubliceven">&nbsp;{$listpages[changes].public}&nbsp;</td>
<td class="bloglistpostseven">&nbsp;{$listpages[changes].posts}&nbsp;</td>
<td class="bloglistvisitseven">&nbsp;{$listpages[changes].hits}&nbsp;</td>
<td class="bloglistactionseven">&nbsp;{$listpages[changes].activity}&nbsp;</td>
<td class="even">
{if $tiki_p_blog_post eq 'y'}
{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_blog_post eq 'y' ) }
{if ($user and $listpages[changes].user eq $user) or ($tiki_p_blog_admin eq 'y') or ($listpages[changes].public eq 'y')}
<a class="bloglink" href="tiki-blog_post.php?blogId={$listpages[changes].blogId}">{tr}Post{/tr}</a>
{/if}
{/if}
{/if}
{if ($user and $listpages[changes].user eq $user) or ($tiki_p_blog_admin eq 'y')}
{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_blog_create_blog eq 'y' ) }
<a class="bloglink" href="tiki-edit_blog.php?blogId={$listpages[changes].blogId}">{tr}Edit{/tr}</a>
{/if}
{/if}
{if ($user and $listpages[changes].user eq $user) or ($tiki_p_blog_admin eq 'y')}
{if ($tiki_p_admin eq 'y') or ($listpages[changes].individual eq 'n') or ($listpages[changes].individual_tiki_p_blog_create_blog eq 'y' ) }
<a class="bloglink" href="tiki-list_blogs.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;remove={$listpages[changes].blogId}">{tr}Remove{/tr}</a>
{/if}
{/if}
{if $tiki_p_admin eq 'y'}
    {if $listpages[changes].individual eq 'y'}({/if}<a class="bloglink" href="tiki-objectpermissions.php?objectName=blog%20{$listpages[changes].title}&amp;objectType=blog&amp;permType=blogs&amp;objectId={$listpages[changes].blogId}">{tr}perms{/tr}</a>{if $listpages[changes].individual eq 'y'}){/if}
{/if}
</td>
{/if}
</tr>
{sectionelse}
<tr><td colspan="6">
<b>{tr}No records found{/tr}</b>
</td></tr>
{/section}
</table>
<br/>
<div class="mini">
{if $prev_offset >= 0}
[<a class="blogprevnext" href="tiki-list_blogs.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a class="blogprevnext" href="tiki-list_blogs.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}next{/tr}</a>]
{/if}
{if $direct_pagination eq 'y'}
<br/>
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$maxRecords}
<a class="prevnext" href="tiki-list_blogs.php?find={$find}&amp;offset={$selector_offset}&amp;sort_mode={$sort_mode}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}
</div>
</div>
