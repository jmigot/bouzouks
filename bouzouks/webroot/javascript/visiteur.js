$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Inscription                             */
	/*--------------------------------------------------*/
	$('#visiteur-inscription a.charte').click(function()
	{
		// On affiche/cache la charte
		$('#visiteur-inscription #charte_div').slideToggle('slow');

		// On empêche le lien de la charte d'être cliqué
		return false;
	});
});


