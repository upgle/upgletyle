function completeInsertUpgletyle(ret_obj, response_tags) {
	alert(ret_obj['message']);
	location.href=current_url.setQuery('act','dispUpgletyleAdminList');
}

function completeInsertGrant(ret_obj) {
	var error = ret_obj['error'];
	var message = ret_obj['message'];
	var page = ret_obj['page'];
	var module_srl = ret_obj['module_srl'];
	alert(message);
}

function completeInsertConfig(ret_obj, response_tags) {
	alert(ret_obj['message']);
	location.reload();
}

function completeDeleteUpgletyle(ret_obj) {
	alert(ret_obj['message']);
	location.href=current_url.setQuery('act','dispUpgletyleAdminList').setQuery('module_srl','');
}


function completeSwitchUpgletyle(ret_obj) {
	alert(ret_obj['message']);
	location.href=current_url.setQuery('act','dispUpgletyleAdminList').setQuery('module_srl','');
}

function completeInsertBlogApiService(ret_obj, response_tags) {
	alert(ret_obj['message']);
	location.href=current_url.setQuery('act','dispUpgletyleAdminBlogApiConfig').setQuery('textyle_blogapi_services_srl','');
}

function deleteBlogApiService(srl) {
    var params = new Array();
    params['textyle_blogapi_services_srl'] = srl;
    exec_xml('upgletyle', 'procUpgletyleAdminDeleteBlogApiServices', params, completeReload);
}



function toggleAccessType(target) {
	switch(target) {
		case 'local' :
				xGetElementById('upgletyleFo').domain.value = '';
				xGetElementById('accessLocal').style.display = 'block';
				xGetElementById('accessDomain').style.display = 'none';
				xGetElementById('accessVid').style.display = 'none';
			break;
		case 'domain' :
				xGetElementById('upgletyleFo').domain.value = '';
				xGetElementById('accessLocal').style.display = 'none';
				xGetElementById('accessDomain').style.display = 'block';
				xGetElementById('accessVid').style.display = 'none';
			break;
		case 'vid' :
				xGetElementById('upgletyleFo').vid.value = '';
				xGetElementById('accessLocal').style.display = 'none';
				xGetElementById('accessDomain').style.display = 'none';
				xGetElementById('accessVid').style.display = 'block';
			break;
	}
}

function completeReload() {
    location.reload();
}

function doApplySubChecked(obj, id) {
    jQuery('div.menu_box_'+id).find('input[type=checkbox]').each(function() { this.checked = obj.checked; });

}


function exportUpgletyle(site_srl,export_type){
    var params = new Array();
    params['site_srl'] = site_srl;
    params['export_type'] = export_type;
    exec_xml('upgletyle', 'procUpgletyleAdminExport', params, completeReload);
}

function deleteExportUpgletyle(site_srl){
    var params = new Array();
    params['site_srl'] = site_srl;
    exec_xml('upgletyle', 'procUpgletyleAdminDeleteExportUpgletyle', params, completeReload);
}
