$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Accueil                                 */
	/*--------------------------------------------------*/
	$('#joueur-accueil .points_action form input[type=text]').on('keyup', function()
	{
		var total = 0;

		// On fait le total des 3 statistiques
		$('#joueur-accueil .points_action form input[type=text]').each(function()
		{
			if ( ! isNaN(parseInt($(this).attr('value'))))
				total += parseInt($(this).attr('value'));
		});

		$('#joueur-accueil .points_action form .total').text(total.toString());

		// Si le total dépasse le maximum du joueur, on alerte
		if (total > parseInt($('#joueur-accueil .points_action form .total_maximum').text()))
			$('#joueur-accueil .points_action form .total').attr('class', 'gras rouge total');
		
		else
			$('#joueur-accueil .points_action form .total').attr('class', 'total');
	});

	$('#joueur-accueil .points_action form').submit(function()
	{
		return confirm('Tu es certain de ta distribution ?');
	});

	/*--------------------------------------------------*/
	/*          Choix personnage                        */
	/*--------------------------------------------------*/
	$('#joueur-choix_perso input[type="radio"][name="sexe"]').change(function()
	{
		// Si 'male' est sélectionné, on affiche le choix du perso male
		if ($(this).val() == 'male')
		{
			$('#joueur-choix_perso #select_femelle').hide();
			$('#joueur-choix_perso #select_male').change().fadeIn();
		}
		
		// Sinon on affiche les femelles
		else
		{
			$('#joueur-choix_perso #select_male').hide();
			$('#joueur-choix_perso #select_femelle').change().fadeIn();
		}
	});
	
	$('#joueur-choix_perso #select_male, #joueur-choix_perso #select_femelle').change(function()
	{
		// On change l'image du perso affiché
		var perso = $(this).val();
		$('.img-perso img').attr('src', img_url+'perso/ensemble/'+perso+'.png');
	});
});



