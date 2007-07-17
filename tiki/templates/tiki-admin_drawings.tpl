{* $Header: /cvsroot/tikiwiki/tiki/templates/tiki-admin_drawings.tpl,v 1.27 2007-07-17 16:21:45 jyhem Exp $ *}

<h1><a class="pagetitle" href="tiki-admin_drawings.php">{tr}Admin drawings{/tr}</a>

{if $feature_help eq 'y'}
<a href="{$helpurl}Drawings" target="tikihelp" class="tikihelp" title="{tr}Help on Drawings{/tr}"><img src="pics/icons/help.png" border="0" height="16" width="16" alt='{tr}help{/tr}' /></a>
{/if}

{if $feature_view_tpl eq 'y'}
<a href="tiki-edit_templates.php?template=tiki-admin_drawings.tpl" target="tikihelp" class="tikihelp" title="{tr}View template{/tr}: {tr}admin Drawings tpl{/tr}"><img src="pics/icons/shape_square_edit.png" border="0" width="16" height="16" alt='{tr}Edit template{/tr}' /></a>
{/if}</h1>

<div class="rbox" name="tip">
<div class="rbox-title" name="tip">{tr}Tip{/tr}</div>  
<div class="rbox-data" name="tip">{tr}To create a drawing in a Wiki page, use {literal}{draw name=foo}{/literal}, where foo is the name of the drawing. After saving, click on the drawing link.{/tr}</div>
</div>
<br />

{if $preview eq 'y'}
<h2>{tr}Preview{/tr}</h2>
<div align="center">
	<a href='#' onclick="javascript:window.open('tiki-editdrawing.php?path={$path}&amp;drawing={$draw_info.name}','','menubar=no,width=252,height=25');">
		<img width='154' height='98' border='0' src='img/wiki/{$draw_info.filename_draw}' alt='{tr}Edit{/tr}' /></a>
</div>
{/if}

<h2>{tr}Available drawings{/tr}:</h2>
<form method="post" action="tiki-admin_drawings.php">
<input type="hidden" name="ver" value="{$smarty.request.ver|escape}" />
<input type="hidden" name="offset" value="{$offset|escape}" />
<input type="hidden" name="sort_mode" value="{$sort_mode|escape}" />
<table class="findtable"><tr><td class="findtable">{tr}Find{/tr}:<td class="findtable"><input type="text" name="find" value="{$find|escape}" /></td></tr></table>
</form>
<form method="post" action="tiki-admin_drawings.php">
<input type="hidden" name="ver" value="{$smarty.request.ver|escape}" />
<input type="hidden" name="offset" value="{$offset|escape}" />
<input type="hidden" name="find" value="{$find|escape}" />
<input type="hidden" name="sort_mode" value="{$sort_mode|escape}" />
<table class="normal">
<tr>
{if $smarty.request.ver}
<td  class="heading"><input type="submit" name="del" value="{tr}x{/tr} " /></td>
{/if}
<td class="heading">{tr}Name{/tr}</td>
<td  class="heading">{tr}Ver{/tr}</a></td>
<td  class="heading">{tr}Versions{/tr}</a></td>
<td  class="heading">{tr}Action{/tr}</a></td>
</tr>
{cycle values="odd,even" print=false}
{section name=user loop=$items}
<tr>
{if $smarty.request.ver}
<td class="{cycle advance=false}">
<input type="checkbox" name="draw[{$items[user].drawId}]" />
</td>
{/if}
<td class="{cycle advance=false}">
{if $smarty.request.ver}
{$items[user].name}
{else}
<a href="tiki-admin_drawings.php?ver={$items[user].name}" class="link">
{$items[user].name}</a>
{/if}
</td>
<td style="text-align:right;" class="{cycle advance=false}">
{$items[user].version}
</td>
<td style="text-align:right;" class="{cycle advance=false}">
{$items[user].versions}
</td>
<td class="{cycle}">
{if not $smarty.request.ver}
<a title="{tr}Edit{/tr}" class="link" href='#' onclick="javascript:window.open('tiki-editdrawing.php?path={$path}&amp;drawing={$items[user].name}','','menubar=no,width=252,height=25');"><img src="pics/icons/page_edit.png" border="0" width="16" height="16"  alt='{tr}Edit{/tr}' /></a> &nbsp;
{/if}
<a title="{tr}view{/tr}" href="tiki-admin_drawings.php?ver={$smarty.request.ver}&amp;previewfile={$items[user].drawId}" class="link">
<img src='pics/icons/monitor.png' border='0' width='16' height='16' alt='{tr}view{/tr}' /></a> &nbsp;
{if $smarty.request.ver}
	<a title="{tr}Delete{/tr}" href="tiki-admin_drawings.php?ver={$smarty.request.ver}&amp;remove={$items[user].drawId}" class="link" >
	<img src="pics/icons/cross.png" border="0" height="16" width="16" alt='{tr}Delete{/tr}' /></a>
{else}
	<a title="{tr}Delete{/tr}" href="tiki-admin_drawings.php?ver={$smarty.request.ver}&amp;removeall={$items[user].name}" class="link" >
	<img src="pics/icons/cross.png" border="0" height="16" width="16" alt='{tr}Delete{/tr}' /></a>
{/if}
</td>
</tr>
{sectionelse}
<tr><td colspan="4" class="odd">{tr}No records found{/tr}</td></tr>
{/section}
</table>
</form>

<div class="mini">
<div align="center">
{if $prev_offset >= 0}
[<a class="prevnext" href="tiki-admin_drawings.php?ver={$smart.request.ver}&amp;offset={$prev_offset}&amp;find={$find}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a class="prevnext" href="tiki-admin_drawings.php?ver={$smart.request.ver}&amp;offset={$next_offset}&amp;find={$find}">{tr}next{/tr}</a>]
{/if}
{if $direct_pagination eq 'y'}
<br />
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$maxRecords}
<a class="prevnext" href="tiki-admin_drawings.php?offset={$selector_offset}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}
</div>
</div>
