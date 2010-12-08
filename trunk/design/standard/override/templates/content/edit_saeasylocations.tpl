
{def $class_identifier=$object.class_identifier}
{def $main_node=$object.current.temp_main_node}

{def $location_classes=ezini('EasyLocationsSettings', 'LocationsClasses', 'saeasylocations.ini')}

{if $location_classes|contains($class_identifier)}


{* Check if top node. *}
{section loop=$assigned_node_array}
    {section show=$Node:item.parent_node|le( 1 )}
        {set has_top_levels=true()}
    {/section}
{/section}


{def $section_title=ezini($class_identifier, 'SectionTitle', 'saeasylocations.ini')}
{if not($section_title)}
	{def $section_title=ezini('EasyLocationsSettings', 'SectionTitle', 'saeasylocations.ini')}
{/if}

{def $choose_method=ezini($class_identifier, 'LocationsChooseMethod', 'saeasylocations.ini')}
{if not($choose_method)}
	{def $choose_method=ezini('EasyLocationsSettings', 'LocationsChooseMethod', 'saeasylocations.ini')}
{/if}

{def $label=ezini($class_identifier, 'Label', 'saeasylocations.ini')}
{if not($label)}
	{def $label=ezini('EasyLocationsSettings', 'Label', 'saeasylocations.ini')}
{/if}

{def $locations_groups=array()}

{if $choose_method|compare('node_list')}
	{def $parent_nodes=ezini($class_identifier, 'ParentNodes', 'saeasylocations.ini')}	
	{if not($parent_nodes)}
		{def $parent_nodes=ezini('EasyLocationsSettings', 'ParentNodes', 'saeasylocations.ini')}
	{/if}

	{def $parent_nodes_ids=array()}
	{foreach $parent_nodes as $subtree_id => $node_list}
		{if $main_node.parent.path_array|contains($subtree_id)}
			{set $parent_nodes_ids=$parent_nodes_ids|merge($node_list|explode(';'))}
		{/if}
	{/foreach}
	
	{def $fetch_function=ezini($class_identifier, 'FetchFunction', 'saeasylocations.ini')}
	{if not($fetch_functions)}
		{def $fetch_function=ezini('EasyLocationsSettings', 'FetchFunction', 'saeasylocations.ini')}
	{/if}

	{def $max_items=ezini($class_identifier, 'MaxItems', 'saeasylocations.ini')}
	{if not($max_items)}
		{def $max_items=ezini('EasyLocationsSettings', 'MaxItems', 'saeasylocations.ini')}
	{/if}

	{if le($max_items, 0)}
		{set $max_items=100}
	{/if}
	
	{def $multi_display=ezini($class_identifier, 'MultipleParentsDisplay', 'saeasylocations.ini')}
	{if not($multi_display)}
		{def $multi_display=ezini('EasyLocationsSettings', 'MultipleParentsDisplay', 'saeasylocations.ini')}
	{/if}

	{if $multi_display|compare('list_only')}
		{def $locations_nodes=fetch(content, $fetch_function, hash(
				parent_node_id, $parent_nodes_ids,
				limit, $max_items
			))
		}
		
		{set $locations_groups=hash(
			$label, $locations_nodes
			)
		}
		
	{else}
		{def $parent_node=false()}
		{def $locations_nodes=array()}
		
		{foreach $parent_nodes_ids as $parent_node_id}
			{set $parent_node=fetch(content, node, hash(node_id, $parent_node_id))}
			{set $locations_nodes=fetch(content, $fetch_function, hash(
					parent_node_id, $parent_node_id,
					limit, $max_items
				))
			}
			
			{set $locations_groups=$locations_groups|merge(hash(
				$parent_node.name, $locations_nodes
				))
			}
		{/foreach}
	{/if}
	
{*
LocationCollisionHandling=leave
*}
{else}
	{def $ini_nodes=ezini($class_identifier, 'IniNodes', 'saeasylocations.ini')}
	{if not($ini_nodes)}
		{def $ini_nodes=ezini('EasyLocationsSettings', 'IniNodes', 'saeasylocations.ini')}
	{/if}

	{def $location_nodes_ids=array()}
	{foreach $ini_nodes as $subtree_id => $node_list}
		{if $main_node.parent.path_array|contains($subtree_id)}
			{set $location_nodes_ids=$location_nodes_ids|merge($node_list|explode(';'))}
		{/if}
	{/foreach}

	{def $locations_nodes=array()}
	{foreach $location_nodes_ids as $node_id}
		{def $locations_nodes=$locations_nodes|append( fetch(content, node, hash(node_id, $node_id)) )}
	{/foreach}
	
	{set $locations_groups=hash(
			$label, $locations_nodes
		)
	}

{/if}

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h2 class="context-title">{$section_title|i18n( 'design/admin/content/edit',, hash( '%locations', $assigned_node_array|count ) )}</h2>

{* DESIGN: Subline *}<div class="header-subline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{def $index=0 $checked=false()}

{def $http_locations_list=ezhttp('saEasyLocations_LocationsList','post')}

{foreach $locations_groups as $group_label => $group_items}

	<table class="list" cellspacing="0" name="saeasylocationslist_{$index}">
	<tr>
		<th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} alt="{'Invert selection.'|i18n( 'design/admin/content/edit' )}" title="{'Invert selection.'|i18n( 'design/admin/content/edit' )}" onclick="ezjs_toggleCheckboxes( document.editform, 'saEasyLocations_LocationsList[]' ); return false;" /></th>
		<th>{$group_label|i18n( 'design/admin/content/edit' )}</th>
	</tr>
	
	{foreach $group_items as $item sequence array( bglight, bgdark ) as $sequence}
		<tr class="{$sequence}">
	
		<td>
		<input type="hidden" name="saEasyLocations_AvailableLocationsList[]" value="{$item.node_id}">
		
		{set $checked=or( $object.parent_nodes|contains($item.node_id), $http_locations_list|contains($item.node_id) )}
		{if and($item.can_remove)}
			<input type="checkbox" name="saEasyLocations_LocationsList[]" value="{$item.node_id}" {if $checked}checked="checked"{/if} />
		{else}
			<input type="checkbox" name="saEasyLocations_LocationsList[]" value="{$item.node_id}"
			{if $checked}checked="checked"{/if}
			title="{'You do not have permission to remove this location.'|i18n( 'design/admin/content/edit' )}" disabled="disabled" />
		{/if}
		</td>
	
		<td>
			{$item.name|wash()}
		</td>
	
	
		</tr>
	{/foreach}
	
	</table>
	
	{set $index=inc($index)}

{/foreach}

{* DESIGN: Content END *}</div></div></div>


<div class="controlbar">

<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
	<div class="block">
	</div>
</div></div></div></div></div></div>

</div>


</div>

{/if}
