/**
 * loadTasksSubTree asincronous load ajax the tasks and workorders (pass id to search and binary structure of branch),
 * change the [+] or [-] image (with same more or less div id) of tree and anime (for show or hide)
 * the div with id "tree_div[id_father]_task_[div_id]"
 *
 * div_id int use in js and ajax php
 * branches_json json string with a boolean array of branches
 * id_father int use in js and ajax php, its useful when you have a two subtrees with same agent for diferent each one
 */
function loadTasksSubTree(id_project, div_id, branches_json, id_father, sql_search) {
	
	// Content div
	var div = $('#tree_div'+id_father+'_task_'+div_id);
	// Tree image
	var image = $('#tree_image'+id_father+'_task_'+div_id);
	// Content div visibility
	var hiddenDiv = div.attr('hiddenDiv');
	// Content div load status
	var loadDiv = div.attr('loadDiv');
	// Level position
	var pos = parseInt(image.attr('pos_tree'));
	
	//If has no data
	if (loadDiv == -1)
		return;
	
	if (loadDiv == 0) {
		
		//Put an spinner to simulate loading process
		div.html("<img style='padding-top:10px;padding-bottom:10px;padding-left:20px;' src=images/spinner.gif>");
		div.attr('loadDiv', 2);
		
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "page=operation/projects/task&print_subtree=1&id_project=" + id_project
			+ "&id_item=" + div_id + "&branches_json=" + branches_json + "&sql_search=" + sql_search,
			success: function(msg) {
				if (msg.length != 0) {
					
					div.html(msg);
					div.attr('hiddendiv',1);
					div.attr('loadDiv', 1);
					
				} else {
					
					var icon_path = 'images/tree';
					
					switch (pos) {
						case 0:
							image.attr('src',icon_path+'/first_leaf.png');
							break;
						case 1:
							image.attr('src',icon_path+'/no_branch.png');
							break;
						case 2:
							image.attr('src',icon_path+'/leaf.png');
							break;
						case 3:
							image.attr('src',icon_path+'/last_leaf.png');
							break;
					}
					
					div.html("");
					div.attr('hiddendiv', 1);
					div.attr('loadDiv', -1);
				}
				
			}
		});
	}
	else {

		var icon_path = 'images/tree';
		
		if (hiddenDiv == 0) {
			
			div.slideUp()
			div.attr('hiddenDiv',1);
			
			//change image of tree [-] to [+]
			switch (pos) {
				case 0:
					image.attr('src',icon_path+'/first_closed.png');
					break;
				case 1:
					image.attr('src',icon_path+'/one_closed.png');
					break;
				case 2:
					image.attr('src',icon_path+'/closed.png');
					break;
				case 3:
					image.attr('src',icon_path+'/last_closed.png');
					break;
			}
		}
		else {
			//change image of tree [+] to [-]
			switch (pos) {
				case 0:
					image.attr('src',icon_path+'/first_expanded.png');
					break;
				case 1:
					image.attr('src',icon_path+'/one_expanded.png');
					break;
				case 2:
					image.attr('src',icon_path+'/expanded.png');
					break;
				case 3:
					image.attr('src',icon_path+'/last_expanded.png');
					break;
			}

			div.attr('hiddenDiv',0);
			div.slideDown();
		}
	}
}
