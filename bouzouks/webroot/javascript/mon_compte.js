$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Choix personnage                        */
	/*--------------------------------------------------*/
	$('#mon_compte-index input[type="radio"][name="sexe"]').change(function()
	{
		// Si 'male' est sélectionné, on affiche le choix du perso male
		if ($(this).val() == 'male')
		{
			$('#mon_compte-index #select_femelle').hide();
			$('#mon_compte-index #select_male').change().fadeIn();
		}
		
		// Sinon on affiche les femelles
		else
		{
			$('#mon_compte-index #select_male').hide();
			$('#mon_compte-index #select_femelle').change().fadeIn();
		}
	});
	
	$('#mon_compte-index #select_male, #mon_compte-index #select_femelle').change(function()
	{
		// On change l'image du perso affiché
		var perso = $(this).val();
		$('.img-perso img').attr('src', img_url+'perso/ensemble/'+perso+'.png');
	});

	$('#mon_compte-index input[type=button][class=previsualiser]').click(function()
	{
		// On récupère les infos
		var texte = $('textarea[name=commentaire]').val();

		$.post(site_url+'webservices/previsualisation_texte', {
			csrf_token: csrf_token,
			texte: texte,
		}, function(reponse)
		{
			$('#mon_compte-index .previsualiser_commentaire .texte').html(reponse.html);
			$('#mon_compte-index .previsualiser_commentaire').slideDown();
			$('html,body').animate({scrollTop: $("#mon_compte-index .previsualiser_commentaire").offset().top}, 'slow');

			/* Clic sur le bouton fermer */
			$('input[class=fermer_previsualisation]').click(function()
			{
				$('#mon_compte-index .previsualiser_commentaire').slideUp();
				$('html,body').animate({scrollTop: $("#mon_compte-index input[type=button][class=previsualiser]").offset().top}, 'slow');
			});
		});
	});

	$('form[class=mettre_en_pause]').submit(function()
	{
		return confirm('Veux-tu vraiment mettre ton compte en pause ?');
	});
}); 
