$(document).ready(function() {
	$('#finder').elfinder({url : 'elfinder/connectors/php/connector.php', lang : 'en', docked : true});
	$('#switcher').themeswitcher();
	$("#info-accordion").accordion({ collapsible: true });
	$("#mysql-accordion").accordion({ collapsible: true });
	$("#navtabs").tabs();
});

