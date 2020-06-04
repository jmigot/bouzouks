$(document).ready(function()
{
	var htmlEntities = function(value)
	{
		return (value) ? $('<div />').text(value).html() : '';
	}
	
	/*--------------------------------------------------*/
	/*          Créer                                   */
	/*--------------------------------------------------*/
	$('#entreprises-creer select').change(function()
	{
		var image_objet = $('#entreprises-creer select option:selected').attr('id');
		$('#entreprises-creer .image_objet img').attr('src', img_url+image_objet);
	});

	/*--------------------------------------------------*/
	/*          Gérer                                   */
	/*--------------------------------------------------*/
	$('#entreprises-gerer .previsualiser').click(function()
	{
		$('#entreprises-gerer .tableau_affichage').slideToggle();
		return false;
	});

	$("#entreprises-gerer .communiques textarea[name='message_1'], #entreprises-gerer .communiques textarea[name='message_2'], #entreprises-gerer .previsualiser").bind("click keypress keydown keyup mouseover", function()
	{
		// On met à jour les champs textes
		$('#entreprises-gerer .tableau_affichage .'+$(this).attr('name')).html(htmlEntities($(this).val()).replace(/\n/g, '<br>'));
		
		// On met à jour les images
		// Tableau plein
		if ($('#entreprises-gerer .tableau_affichage .message_1').html() != '' && $('#entreprises-gerer .tableau_affichage .message_2').html() != '')
		{
			$('#entreprises-gerer .tableau_affichage .tableau_plein').css('background-image', 'url('+img_url+'boulot/notes_plein.png)');
		}

		// Tableau 1 seulement
		else if ($('#entreprises-gerer .tableau_affichage .message_1').html() != '')
		{
			$('#entreprises-gerer .tableau_affichage .tableau_plein').css('background-image', 'url('+img_url+'boulot/notes_1.png)');
		}

		// Tableau 2 seulement
		else if ($('#entreprises-gerer .tableau_affichage .message_2').html() != '')
		{
			$('#entreprises-gerer .tableau_affichage .tableau_plein').css('background-image', 'url('+img_url+'boulot/notes_2.png)');
		}

		// Tableau vide
		else
		{
			$('#entreprises-gerer .tableau_affichage .tableau_plein').css('background-image', 'url('+img_url+'boulot/notes_vide.png)');
		}
	});

	$('form[class=demissionner]').submit(function()
	{
		return confirm('Veux-tu vraiment démissionner ?');
	});
});

