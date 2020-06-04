$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Index                                   */
	/*--------------------------------------------------*/
	$("#maison-index .stats form").submit(function()
	{
		// On demande confirmation avant de supprimer des objets
		return confirm('Veux-tu vraiment supprimer ceci ?');
	});

	$("#maison-index .confirmer form").submit(function()
	{
		// On demande confirmation avant de consommer/vendre
		return confirm('Tu es sûr ?');
	});
});

