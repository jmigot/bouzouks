$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Index                                   */
	/*--------------------------------------------------*/
	$('#gazette-index .colonne_bas a').click(function()
	{
		// On affiche/cache l'ancien article cliqué
		var lien_id = $(this).attr('id').split('_')[1];
		$('#gazette-index .colonne_bas #ancien_article_'+lien_id).slideToggle('fast');
		return false;
	});

	/*--------------------------------------------------*/
	/*          Rédiger                                 */
	/*--------------------------------------------------*/
	function ajouter_texte(element_id, texte)
	{
		var textarea     = $('textarea[name='+element_id+']');
		var top          = textarea.scrollTop();
		var taille       = textarea.val().length;
		var debut        = textarea[0].selectionStart;
		var fin          = textarea[0].selectionEnd;
		var remplacement = texte;

		// On remplace le bbcode
		textarea.val(textarea.val().substring(0, debut) + remplacement + textarea.val().substring(fin, taille));

		// On re-scroll là où on était
		textarea.scrollTop(top);
	}
	
	$('#gazette-rediger input[class=ajouter_pseudo]').click(function()
	{
		// On rajoute le lien dans la zone de texte
		var id = $('#gazette-rediger select[name=select_ajout] option:selected').val();
		var pseudo = $('#gazette-rediger select[name=select_ajout] option:selected').text();

		if (id != '')
			ajouter_texte('texte', ' {'+pseudo+'|'+id+'} ');
	});
		
	$('#gazette-rediger form[class=mutex]').submit(function()
	{
		// On envoie la demande de mutex
		var article_id = $('#gazette-rediger input[type=hidden][name=article_id]').val();
		var action     = $('#gazette-rediger input[type=submit][name=mutex]').val() == "Verrouiller l'article" ? 'verrouiller' : 'deverrouiller';
		var url        = 'webservices_gazette/changer_mutex_article';
		
		$.post(site_url+url, { csrf_token: csrf_token, action: action, article_id: article_id }, function(reponse)
		{
			// Lorsque la requête est bien arrivée, on affiche un message
			$('#gazette-rediger form[class=mutex] p[class*=texte]').html(reponse.message);

			// On change le texte
			var texte = reponse.action == 'verrouiller' ? "Verrouiller l'article" : "Déverrouiller l'article";
			$('#gazette-rediger input[type=submit][name=mutex]').val(texte);

			if (reponse.action == 'verrouiller')
			{
				$('#gazette-rediger input[name=titre]').attr('disabled', 'disabled');
				$('#gazette-rediger textarea[name=texte]').attr('disabled', 'disabled');
				$('#gazette-rediger .article').hide('slow');
			}

			else
			{
				$('#gazette-rediger input[name=titre]').text(reponse.titre).removeAttr('disabled');
				$('#gazette-rediger textarea[name=texte]').text(reponse.texte).removeAttr('disabled');
				$('#gazette-rediger .article').show('slow');
			}
		});
		
		return false;
	});

	/*--------------------------------------------------*/
	/*          Upload d'image dans Rédiger             */
	/*--------------------------------------------------*/
	$('#gazette-rediger input[name=image]').click(function()
	{
		// On va chercher les images disponibles pour la gazette
		$.post(site_url+'webservices_gazette/images_disponibles', { csrf_token: csrf_token }, function(reponse)
		{
			var div = $(reponse.html);
			var w = $(window);
			$(div).css(
			{
				'top': Math.abs(((w.height() - div.outerHeight()) / 2) + w.scrollTop()),
				'left':Math.abs(((w.width() - div.outerWidth()) / 2) + w.scrollLeft())
			}).appendTo('body').show();
		});
	});
	
	$('#gazette-images_disponibles input[type=button]').live('click', function(event)
	{
		event.preventDefault();
		$('#gazette-images_disponibles').remove();
	});
	
	$('#gazette-images_disponibles img').live('click', function (event)
	{
		event.preventDefault();
		ajouter_texte('texte', ' [img=' + $(this).attr('alt') + '|taille=50|class=] ');
		$('#gazette-images_disponibles').remove();
	}).live('mouseenter', function(event)
	{
		event.preventDefault();
		$(this).css({ opacity: 1.0 });
	}).live('mouseleave', function(event)
	{
		event.preventDefault();
		$(this).css({ opacity: 0.85 });
	});
	
	/*--------------------------------------------------*/
	/*          Prévisualisation                        */
	/*--------------------------------------------------*/
	$("input[class='previsualiser']").click(function()
	{
		// On récupère les infos
		var titre      = $('input[name=titre]').val();
		var texte      = $('textarea[name=texte]').val();
		var article_id = $('input[type=hidden][name=article_id]').val();
		
		if (titre == '' || texte == '')
		{
			alert("Il faut remplir le titre et le texte");
			return;
		}
		
		$.post(site_url+'webservices_gazette/previsualisation_gazette', {
			csrf_token: csrf_token,
			titre: titre,
			texte: texte,
			article_id: article_id
		}, function(reponse)
		{
			if (reponse != '')
			{
				$('#popup').html(reponse.html).slideDown();
				$('html,body').animate({scrollTop: $("#popup").offset().top}, 'slow');
			
				/* Clic sur le bouton fermer */
				$('input[class=fermer_previsualisation]').click(function()
				{
					$('#popup').slideUp();
					$('html,body').animate({scrollTop: $("#popup").offset().top}, 'slow');
				});
			}
		});
	});
});
