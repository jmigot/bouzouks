$(document).ready(function()
{
	// Tout cocher
	$('#historique-index .tout_cocher').click(function()
	{
		$('#historique-index [type=checkbox]').attr("checked", true);
	});

	// Tout décocher
	$('#historique-index .tout_decocher').click(function()
	{
		$('#historique-index [type=checkbox]').attr("checked", false);
	});
});



