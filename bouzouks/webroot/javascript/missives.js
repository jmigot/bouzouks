$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Reçues/Envoyées                         */
	/*--------------------------------------------------*/
	$("#missives #case_supprimer_tous").click(function()
	{
		// On coche/décoche toutes les cases des messages en fonction de la case "Tous"
		$('#missives [type="checkbox"][name="ids[]"]').attr("checked", this.checked);
	});

	/*--------------------------------------------------*/
	/*          Lire reçues : Prise de pouvoir (clans)  */
	/*--------------------------------------------------*/
	$("#missives-lire form[class*=note_service]").submit(function()
	{
		/* On modifie le token pour remettre le bon avant de valider */
		if (confirm('Tu es sûr de vouloir faire ça ? Réfléchis bien !'))
		{
			$('input[type=hidden][name=csrf_token]', this).val(csrf_token);
			return true;
		}

		return false;
	});

	/*--------------------------------------------------*/
	/*          Ecrire                                  */
	/*--------------------------------------------------*/
	$("#missives-ecrire .timbre select").change(function()
	{
		var timbre = $(this).val();
		$("#missives-ecrire .timbre_image img").attr('src', img_url+'missives/timbres/'+timbre);
	});
	
	/*--------------------------------------------------*/
	/*          Prévisualisation                        */
	/*--------------------------------------------------*/
	$("input[class='previsualiser']").click(function()
	{
		// On récupère les infos
		var objet            = $('input[name=objet]').val();
		var intro            = $('select[name=intro] option:selected').text();
		var message          = $('textarea[name=message]').val();
		var politesse        = $('select[name=politesse] option:selected').text();
		var timbre           = $('select[name=timbre] option:selected').val();
		var expediteur_robot = $('input[name=expediteur_robot]').val();
		var destinataire_id  = $('select[name=destinataire] option:selected').val();

		if (destinataire_id == '')
			destinataire_id = 0;
		
		if (objet == '' || message == '' || timbre == '')
		{
			alert("Il faut remplir l'objet et le message");
			return;
		}

		$.post(site_url+'webservices/previsualisation_missive', {
			csrf_token: csrf_token,
			objet: objet,
			message: "\t" + intro + "\n\n" + message + "\n\n\t" + politesse,
			timbre: timbre,
			expediteur_robot: expediteur_robot,
			destinataire_id: destinataire_id
		}, function(reponse)
		{
			$('#popup').html(reponse.html).slideDown();
			$('html,body').animate({scrollTop: $("#popup").offset().top}, 'slow');

			/* Clic sur le bouton fermer */
			$('input[class=fermer_previsualisation]').click(function()
			{
				$('#popup').slideUp();
				$('html,body').animate({scrollTop: $("#popup").offset().top}, 'slow');
			});
		});
	});
});
