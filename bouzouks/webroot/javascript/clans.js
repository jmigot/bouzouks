$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Rechercher                              */
	/*--------------------------------------------------*/
	$('#clans-lister .button').click(function()
	{
		$(this).parent().next('div').slideToggle();
	});

	/*--------------------------------------------------*/
	/*          Gérer                                   */
	/*--------------------------------------------------*/
	$('#clans-gerer input[type=submit][name=leguer], #clans-gerer input[type=submit][name=supprimer], #clans-gerer input[type=submit][name=quitter]').click(function()
	{
		return confirm('Tu es vraiment sûr ?');
	});

	// Afficher/Cacher le détail des actions
	$('#clans-gerer div[id*=action_image_]').click(function()
	{
		var action_id = $(this).attr('id').split('_')[2];

		// Le détail est déjà affiché, on le cache
		if ($('#clans-gerer div[id=action_details_'+action_id+']').is(':visible'))
			$('#clans-gerer div[id=action_details_'+action_id+']').slideUp();
		
		// Sinon on affiche le détail et on cache l'ancien
		else
		{
			$('#clans-gerer div[id=action_details_'+action_id+']').slideDown(500);

			$('#clans-gerer div[id*=action_details_]').each(function()
			{
				if ($(this).attr('id').split('_')[2] != action_id)
					$(this).slideUp(350);
			});
		}		
	});

	// Confirmations avant d'envoyer certains formulaires
	$('#clans-gerer .actions form input[type=submit]').click(function()
	{
		return confirm('Tu es sûr de vouloir lancer cette action avec ces paramètres ?');		
	});

	$('#clans-gerer .enchere form input[type=submit]').click(function()
	{
		return confirm('Tu es sûr ?');		
	});

	// Ajouter un allié sur une action
	$('#clans-gerer .actions .details input[type=button][value="Ajouter"]').click(function()
	{
		// On récupère clan_id et clan_nom
		var action_id = $(this).attr('class').split('_')[1];
		var clan_type = $('#clans-gerer .actions .details input[name=clan_type][type=hidden]').val();
		var clan_id = $('#clans-gerer .actions .details select[name=clan_allie_id][class=action_'+action_id+']').val();
		var clan_nom = $('#clans-gerer .actions .details input[name=clan_allie_nom][class=action_'+action_id+']').val();

		$.post(site_url+'webservices_clans/ajouter_allie_action', {
			csrf_token: csrf_token,
			action_id: action_id,
			clan_type: clan_type,
			clan_id: clan_id,
			clan_nom: clan_nom
		}, function(reponse)
		{
			if (reponse.alert != undefined)
				alert(reponse.alert);
		});
	});

	$('#clans-gerer .actions input[type=button][class=previsualiser]').click(function()
	{
		// On récupère les infos
		var tmp = $(this).attr('id').split('_');
		var id = tmp[1]+'_'+tmp[2];
		var texte = $('textarea[name='+id+']').val();

		$.post(site_url+'webservices/previsualisation_texte', {
			csrf_token: csrf_token,
			texte: texte,
		}, function(reponse)
		{
			$('#clans-gerer .previsualiser_'+id+' .texte').html(reponse.html);
			$('#clans-gerer .previsualiser_'+id).slideDown();
			$('html,body').animate({scrollTop: $('#clans-gerer .previsualiser_'+id).offset().top}, 'slow');
		});
	});
});
