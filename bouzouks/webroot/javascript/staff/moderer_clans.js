$(document).ready(function()
{
	$('#staff-moderer_clans-index img[class*=enchere_]').click(function()
	{
		var enchere_id = $(this).attr('class').split('_')[1];
		$('#staff-moderer_clans-index tr[class*=enchere_'+enchere_id+']').slideToggle();
	});
});
