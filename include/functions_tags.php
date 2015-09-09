<?php 

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2007-2011 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

global $config;

//-- Constants --//

// Types
define('LEAD', 'lead');

// Table identifiers
define('TAGS_TABLE', 'ttag');
define('TAGS_TABLE_ID_COL', 'id');
define('TAGS_TABLE_NAME_COL', 'name');
define('TAGS_TABLE_COLOUR_COL', 'colour');

define('LEADS_TABLE', 'tlead_tag');
define('LEADS_TABLE_ID_COL', 'id');
define('LEADS_TABLE_TAG_ID_COL', 'tag_id');
define('LEADS_TABLE_LEAD_ID_COL', 'lead_id');

// Available tag colours
define('TAG_BLUE', 'blue');
define('TAG_GREY', 'grey');
define('TAG_GREEN', 'green');
define('TAG_YELLOW', 'yellow');
define('TAG_ORANGE', 'orange');
define('TAG_RED', 'red');

//-- Data retrieving functions --//

/** 
 * Check if the name of the tag exists.
 * 
 * @param string Tag name.
 * 
 * @return bool Wether te tag exists or not.
 */
function exists_tag_name ($name) {
	if (empty($name))
		throw new InvalidArgumentException('The name cannot be empty');
	
	$result = (bool) get_db_value(TAGS_TABLE_ID_COL, TAGS_TABLE, TAGS_TABLE_NAME_COL, $name);
	
	return $result;
}

/** 
 * Create a tag.
 * 
 * @param array Values of the tag.
 * 
 * @return mixed The id of the created item (int) of false (bool) on error.
 */
function create_tag ($values) {
	if (empty($values))
		throw new InvalidArgumentException('The values cannot be empty');
	if (empty($values[TAGS_TABLE_NAME_COL]))
		throw new InvalidArgumentException('The name cannot be empty');
	
	$result = process_sql_insert(TAGS_TABLE, $values);
	
	return $result;
}

/** 
 * Delete a tag.
 * 
 * @param int Id of the tag.
 * @param array Values of the tag.
 * 
 * @return mixed The number of the items updated (int) of false (bool) on error.
 */
function update_tag ($id, $values) {
	if (empty($id) || !is_numeric($id))
		throw new InvalidArgumentException('ID should be numeric');
	if ($id <= 0)
		throw new RangeException('ID should be a number greater than 0');
	
	$where = array(TAGS_TABLE_ID_COL => $id);
	$result = process_sql_update(TAGS_TABLE, $values, $where);
	
	return $result;
}

/** 
 * Delete a tag.
 * 
 * @param int Id of the tag.
 * 
 * @return mixed The number of the items deleted (int) of false (bool) on error.
 */
function delete_tag ($id) {
	if (empty($id) || !is_numeric($id))
		throw new InvalidArgumentException('ID should be numeric');
	if ($id <= 0)
		throw new RangeException('ID should be a number greater than 0');
	
	$where = array(TAGS_TABLE_ID_COL => $id);
	$result = process_sql_delete(TAGS_TABLE, $where);
	
	return $result;
}

/** 
 * Get the all the available tag colours.
 * 
 * @return array The list of the tag colours.
 */
function get_available_tag_colours () {
	$tag_colours = array(
			TAG_ORANGE => __('Orange'),
			TAG_BLUE => __('Blue'),
			TAG_GREY => __('Grey'),
			TAG_GREEN => __('Green'),
			TAG_YELLOW => __('Yellow'),
			TAG_RED => __('Red')
		);
	
	return $tag_colours;
}

/** 
 * Get the all the available tags.
 * 
 * @param array (optional) Filter of the tags.
 * 
 * @return array The list of the tags with all the rows.
 */
function get_available_tags ($filter = array()) {
	global $config;
	
	$id = isset($filter[TAGS_TABLE_ID_COL]) ? $filter[TAGS_TABLE_ID_COL] : 0;
	$name = isset($filter[TAGS_TABLE_NAME_COL]) ? $filter[TAGS_TABLE_NAME_COL] : '';
	$colour = isset($filter[TAGS_TABLE_COLOUR_COL]) ? $filter[TAGS_TABLE_COLOUR_COL] : '';
	
	$id_filter = '';
	if (empty($id)) {
		$id_filter = '1=1';
	}
	else if (is_array($id)) {
		$id_filter = sprintf(
				'tt.%s IN (%s)',
				TAGS_TABLE_ID_COL,
				implode(',', $id)
			);
	}
	else {
		$id_filter = sprintf(
				'tt.%s = %d',
				TAGS_TABLE_ID_COL,
				$id
			);
	}
	
	$name_filter = '';
	if (empty($name)) {
		$name_filter = '1=1';
	}
	else if (is_array($name)) {
		$name_filter = sprintf(
				'tt.%s IN (\'%s\')',
				TAGS_TABLE_NAME_COL,
				implode('\',\'', $name)
			);
	}
	else {
		$name_filter = sprintf(
				'tt.%s = \'%s\'',
				TAGS_TABLE_NAME_COL,
				$name
			);
	}
	
	$colour_filter = '';
	if (empty($colour)) {
		$colour_filter = '1=1';
	}
	else if (is_array($colour)) {
		$colour_filter = sprintf(
				'tt.%s IN (\'%s\')',
				TAGS_TABLE_COLOUR_COL,
				implode('\',\'', $colour)
			);
	}
	else {
		$colour_filter = sprintf(
				'tt.%s = \'%s\'',
				TAGS_TABLE_COLOUR_COL,
				$colour
			);
	}	
	
	$sql = sprintf('SELECT tt.*
					FROM %s tt
					WHERE %s
						AND %s
						AND %s',
					TAGS_TABLE,
					$id_filter,
					$name_filter,
					$colour_filter);
	$tags = get_db_all_rows_sql($sql);
	if (empty($tags)) $tags = array();
	
	return $tags;
}

/** 
 * Get the all the available tag indexed.
 * 
 * @param array (optional) Filter of the tags.
 * 
 * @return array The list of the tag indexed.
 */
function get_available_tags_indexed ($filter = array()) {
	global $config;
	
	$tags = get_available_tags($filter);
	$tag_ids = array_map(function ($tag) {
		return $tag[TAGS_TABLE_ID_COL];
	}, $tags);
	$tag_names = array_map(function ($tag) {
		return $tag[TAGS_TABLE_NAME_COL];
	}, $tags);
	$tags_indexed = array_combine($tag_ids, $tag_names);
	
	return $tags_indexed;
}

/** 
 * Get the all the available tag names.
 * 
 * @param array (optional) Filter of the tags.
 * 
 * @return array The list of the tag names.
 */
function get_available_tag_names ($filter = array()) {
	global $config;
	
	$tags = get_available_tags($filter);
	$tag_names = array_map(function ($tag) {
		return $tag[TAGS_TABLE_NAME_COL];
	}, $tags);
	
	return $tag_names;
}

/** 
 * Get the tags assigned to an item.
 * If the item id is empty, it will return the tags assigned to any item.
 * 
 * @param string Type of the item.
 * @param array [Optional] Items to filter the items.
 * @param array [Optional] Items to filter the tags.
 * 
 * @return array The list of the tags with all the rows.
 */
function get_tags ($item_type, $item_filter = array(), $tag_filter = array()) {
	global $config;
	
	$item_table_name = '';
	$item_table_tag_id_column = '';
	$item_table_item_id_column = '';
	
	switch ($item) {
		case LEAD:
			$item_table_name = LEADS_TABLE;
			$item_table_tag_id_column = LEADS_TABLE_TAG_ID_COL;
			$item_table_item_id_column = LEADS_TABLE_LEAD_ID_COL;
			break;
		default:
			break;
	}
	
	// Item filter
	$item_id = isset($item_filter[$item_table_item_id_column]) ? $item_filter[$item_table_item_id_column] : 0;
	if (empty($item_id)) {
		$item_id_filter = '1=1';
	}
	else if (is_array($item_id)) {
		$item_id_filter = sprintf(
				'ti.%s IN (%s)',
				$item_table_item_id_column,
				implode(',', $item_id)
			);
	}
	else {
		$item_id_filter = sprintf(
				'ti.%s = %d',
				$item_table_item_id_column,
				$item_id
			);
	}
	
	// Tag filter
	$tag_id = isset($tag_filter[TAGS_TABLE_ID_COL]) ? $tag_filter[TAGS_TABLE_ID_COL] : 0;
	$tag_name = isset($tag_filter[TAGS_TABLE_NAME_COL]) ? $tag_filter[TAGS_TABLE_NAME_COL] : '';
	$tag_colour = isset($tag_filter[TAGS_TABLE_COLOUR_COL]) ? $tag_filter[TAGS_TABLE_COLOUR_COL] : '';
	
	$tag_id_filter = '';
	if (empty($tag_id)) {
		$tag_id_filter = '1=1';
	}
	else if (is_array($tag_id)) {
		$tag_id_filter = sprintf(
				'tt.%s IN (%s)',
				TAGS_TABLE_ID_COL,
				implode(',', $tag_id)
			);
	}
	else {
		$tag_id_filter = sprintf(
				'tt.%s = %d',
				TAGS_TABLE_ID_COL,
				$tag_id
			);
	}
	
	$tag_name_filter = '';
	if (empty($tag_name)) {
		$tag_name_filter = '1=1';
	}
	else if (is_array($tag_name)) {
		$tag_name_filter = sprintf(
				'tt.%s IN (\'%s\')',
				TAGS_TABLE_NAME_COL,
				implode('\',\'', $tag_name)
			);
	}
	else {
		$tag_name_filter = sprintf(
				'tt.%s = \'%s\'',
				TAGS_TABLE_NAME_COL,
				$tag_name
			);
	}
	
	$tag_colour_filter = '';
	if (empty($tag_colour)) {
		$tag_colour_filter = '1=1';
	}
	else if (is_array($tag_colour)) {
		$tag_colour_filter = sprintf(
				'tt.%s IN (\'%s\')',
				TAGS_TABLE_COLOUR_COL,
				implode('\',\'', $tag_colour)
			);
	}
	else {
		$tag_colour_filter = sprintf(
				'tt.%s = \'%s\'',
				TAGS_TABLE_COLOUR_COL,
				$tag_colour
			);
	}
	
	$sql = sprintf('SELECT tt.*
					FROM %s tt
					INNER JOIN %s ti
						ON tt.%d = ti.%d
							AND %s
					WHERE %s
						AND %s
						AND %s',
					TAGS_TABLE,
					$item_table_name,
					TAGS_TABLE_ID_COL,
					$item_table_tag_id_column,
					$item_id_filter,
					$tag_id_filter,
					$tag_name_filter,
					$tag_colour_filter);
	$tags = get_db_all_rows_sql($sql);
	if (empty($tags)) $tags = array();
	
	return $tags;
}

/** 
 * Get the tags assigned to an item as a pair of index => name.
 * 
 * @param string Type of the item.
 * @param array [Optional] Items to filter the items.
 * @param array [Optional] Items to filter the tags.
 * 
 * @return array The list of the tag indexed.
 */
function get_tags_indexed ($item_type, $item_filter = array(), $tag_filter = array()) {
	global $config;
	
	$tags = get_tags($item_type, $filter);
	$tag_ids = array_map(function ($tag) {
		return $tag[TAGS_TABLE_ID_COL];
	}, $tags);
	$tag_names = array_map(function ($tag) {
		return $tag[TAGS_TABLE_NAME_COL];
	}, $tags);
	$tags_indexed = array_combine($tag_ids, $tag_names);
	
	return $tags_indexed;
}

/** 
 * Get the tags names assigned to an item.
 * 
 * @param string Type of the item.
 * @param array [Optional] Items to filter the items.
 * @param array [Optional] Items to filter the tags.
 * 
 * @return array The list of the tag names.
 */
function get_tag_names ($item_type, $item_filter = array(), $tag_filter = array()) {
	global $config;
	
	$tags = get_tags($item_type, $item_filter, $tag_filter);
	$tag_names = array_map(function ($tag) {
		return $tag[TAGS_TABLE_NAME_COL];
	}, $tags);
	
	return $tag_names;
}

/** 
 * Get the tags assigned to a lead.
 * 
 * @param mixed Id (int) or ids (array) of the lead/s.
 * @param array [Optional] Items to filter the tags.
 * 
 * @return array The list of the tags with all the rows.
 */
function get_lead_tags ($lead_id = false, $tag_filter = array()) {
	$lead_filter = array();
	if (empty($lead_id))
		$lead_filter = array(LEADS_TABLE_ID_COL => $lead_id);
	
	return get_tags(LEAD, $lead_filter, $tag_filter);
}

/** 
 * Get the tags assigned to a lead as a pair of index => name.
 * 
 * @param mixed Id (int) or ids (array) of the lead/s.
 * @param array [Optional] Items to filter the tags.
 * 
 * @return array The list of the tag indexed.
 */
function get_lead_tags_indexed ($lead_id = false, $tag_filter = array()) {
	$lead_filter = array();
	if (empty($lead_id))
		$lead_filter = array(LEADS_TABLE_ID_COL => $lead_id);
	
	return get_tags_indexed(LEAD, $lead_filter, $tag_filter);
}

/** 
 * Get the tags names assigned to a lead.
 * 
 * @param mixed Id (int) or ids (array) of the lead/s.
 * @param array [Optional] Items to filter the tags.
 * 
 * @return array The list of the tag names.
 */
function get_lead_tag_names ($lead_id = false, $tag_filter = array()) {
	$lead_filter = array();
	if (empty($lead_id))
		$lead_filter = array(LEADS_TABLE_ID_COL => $lead_id);
	
	return get_tag_names(LEAD, $lead_filter, $tag_filter);
}


//-- HTML elements functions --//

function html_render_tags_editor ($props) {
	// Defaults
	$tags;
	$selected_tags = array();
	$select_name = 'tags';
	$any = false;
	$label = __('Selected tags');
	$disabled = false;
	$visible = false;
	
	if (!isset($props))
		$props = array();
	
	if (isset($props['tags']))
		$tags = $props['tags'];
	else
		$tags = get_available_tags_indexed();
	
	// Selected tags
	if (isset($props['selected_tags']))
		$selected_tags = $props['selected_tags'];
	// Select name
	if (isset($props['select_name']))
		$select_name = $props['select_name'];
	// Any enabled/disabled
	if (isset($props['any']))
		$any = $props['any'];
	// Label
	if (isset($props['label']))
		$label = $props['label'];
	// Disabled
	if (isset($props['disabled']))
		$disabled = $props['disabled'];
	// Visible
	if (isset($props['visible']))
		$visible = $props['visible'];
	
	// Tags multi selector
	$select_selected_tags = html_print_select($tags, $select_name, $selected_tags, '', $any ? __('Any') : '', 0,
		true, true, true, '', $disabled, $visible ? '' : 'display:none;');
	
	// Tags simple selector
	$selected_tags_comb = array_combine($selected_tags, $selected_tags);
	$not_added_tags = array_diff_key($tags, $selected_tags_comb);
	
	$select_add_tags = '<div class="tags-select" style="'.(!$visible ? '' : 'display:none;').'">';
	//$select_add_tags .= print_label(__('Select a tag'), 'add-tags-select', 'select', true);
	$select_add_tags .= html_print_select($not_added_tags, 'add-tags-select', array(), '', __('Select'), 0,
		true, false, true, '', $disabled);
	$select_add_tags .= '</div>';
	
	// Tags view
	$view_tags_selected = '<div class="tags-view"></div>';
	
	echo '<div class="tags-editor">';
	echo 	$select_selected_tags;
	echo 	$select_add_tags;
	echo 	$view_tags_selected;
	echo '</div>';
	
?>
	<script type="text/javascript">
	(function ($) {
		
		var $selectSelectedTags = $('select[name="<?php echo $select_name; ?>"]');
		var $selectAddTags = $('select[name="add-tags-select"]');
		var $tagsView = $('div.tags-view');
		
		var addTag = function (id, name) {
			var $tag = $('<span></span>');
			$tag.html(name)
				.prop('id', 'tag-'+id)
				.addClass('tag')
				.addClass('label')
				.data('id', id)
				.data('name', name);
			$tagsView.append($tag);
			
			// Remove the label from the 'add select'
			$selectAddTags
				.children('option[value="' + id + '"]')
					.remove();
			
			// Select the item of the tags select
			$selectSelectedTags
				.children('option[value="' + id + '"]')
					.prop('selected', true);
		}
		
		var removeTag = function (id, name) {
			// Add the deleted item to the 'add select'
			$selectAddTags.append($('<option>', {
				value: id,
				text: name
			})).val(0).change();
			
			// Unselect the item of the tags select
			$selectSelectedTags
				.children('option[value="' + id + '"]')
					.prop('selected', false);
			
			// Remove the tag
			$('span#tag-'+id).remove();
		}
		
		// Handler to add a new label with the 'add select'
		$selectAddTags.change(function (event) {
			event.preventDefault();
			
			// Retrieve the label info from the 'add select'
			var id = $(this).val();
			var name = $(this).children('option[value="' + id + '"]').html();
			console.log(id, name);
			if (id != 0) {
				// Add the tag
				addTag(id, name);
			}
		});
		
		// Handler to delete a label selection
		$tagsView.on('click', 'span.tag', function (event) {
			event.preventDefault();
			
			if (typeof event.target !== 'undefined') {
				// Get the label info from the target element
				var id = $(event.target).data('id');
				var name = $(event.target).data('name');
				
				// Remove the tag
				removeTag(id, name);
			}
		});
		
		// Fill the tags view
		$selectSelectedTags
			.children('option:selected')
				.each(function(index, el) {
					addTag(el.value, el.text);
				});
		
	})(window.jQuery);
	</script>
<?php
}

function html_render_tag ($tag, $return = false) {
	$tag_view = '';
	
	if (!empty($tag) && !empty($tag[TAGS_TABLE_ID_COL]) && !empty($tag[TAGS_TABLE_NAME_COL]) && !empty($tag[TAGS_TABLE_COLOUR_COL])) {
		$tag_view = sprintf('<span title="" class="tag label %s" data-id="%s" data-name="%s" data-colour="%s">%s</span>',
			$tag[TAGS_TABLE_COLOUR_COL], $tag[TAGS_TABLE_ID_COL], $tag[TAGS_TABLE_NAME_COL],
			$tag[TAGS_TABLE_COLOUR_COL], $tag[TAGS_TABLE_NAME_COL]);
	}
	
	if ($return)
		return $tag_view;
	echo $tag_view;
}

function html_render_tags_view ($tags, $return = false) {
	$tags_view = '<div class="tags-view">';
	
	if (!empty($tags) && is_array($tags)) {
		foreach ($tags as $tag) {
			if (empty($tag[TAGS_TABLE_ID_COL]) || empty($tag[TAGS_TABLE_NAME_COL]) || empty($tag[TAGS_TABLE_COLOUR_COL]))
				continue;
			
			$tags_view .= html_render_tag($tag, true);
		}
	}
	
	$tags_view .= '</div>';
	
	if ($return)
		return $tags_view;
	echo $tags_view;
}

?>