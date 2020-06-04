$(document).ready(function()
{
	/* BBCode */
	function remplace_bbcode(element_id, balise_debut, balise_fin)
	{
		var textarea     = $('textarea[name='+element_id+']');
		var top          = textarea.scrollTop();
		var taille       = textarea.val().length;
		var debut        = textarea[0].selectionStart;
		var fin          = textarea[0].selectionEnd;
		var selection    = textarea.val().substring(debut, fin);
		var remplacement = balise_debut + selection + balise_fin;
			
		// On remplace le bbcode
		textarea.val(textarea.val().substring(0, debut) + remplacement + textarea.val().substring(fin, taille));
			
		// On re-scroll là où on était
		textarea.scrollTop(top);
	}
		
	$('input[class*=bbcode_]').click(function()
	{
		var texte = $('span[id='+$(this).attr('class')+']').text();

		// Couleur
		if ($(this).attr('class') == 'bbcode_couleur')
		{
			remplace_bbcode('req_message', '[color='+$('select[class=bbcode_couleur]').val()+']', '[/color]');
		}

		// Image
		else if ($(this).attr('class') == 'bbcode_image')
		{
			var url = prompt("URL de l'image");

			// URL non renseignée
			if (url == '' || url == null)
			{
				if (url == '')
					alert("Bah il faut donner une URL sinon ça marche pô...");
				return;
			}
			
			remplace_bbcode('req_message', '[img]'+url, '[/img]');
		}

		// Lien
		else if ($(this).attr('class') == 'bbcode_lien')
		{
			var url = prompt("URL du lien");

			// Url non renseignée
			if (url == '' || url == null)
			{
				if (url == '')
					alert("Bah il faut donner une URL sinon ça marche pô...");
				return;
			}

			var texte = prompt("Texte à afficher pour cliquer sur le lien");

			if (texte == '' || texte == null)
				texte = url;
			
			remplace_bbcode('req_message', '[url='+url+']'+texte, '[/url]');
		}

		// Tout le reste
		else
		{
			var tmp = texte.split('|');
			remplace_bbcode('req_message', tmp[0], tmp[1]);
		}
	});

	$('img[class*=bbcode_]').click(function()
	{
		var texte = $(this).attr('alt');
		remplace_bbcode('req_message', texte, '');
	});

	$('img[class*=bbcode_]').mouseover(function()
	{
		$(this).css({'cursor' : 'pointer'});
	});


	/* "J'aime" et "Je n'aime plus" */
	function maj_nb_likes(post_id, value)
	{
		var nb_likes = $('div[id=p'+String(post_id)+'] span[class*=like]');
		var nb = parseInt(nb_likes.text()) + value;
		nb_likes.text(String(nb));

		if (nb == 0)
			$('div[id=p'+String(post_id)+'] span[class*=like]').hide();

		else
			$('div[id=p'+String(post_id)+'] span[class*=like]').show();
	}

	$('.postfootright li[class=postlike] a').live('click', function(event)
	{
		var post_id = parseInt($(this).attr('href').substr(1));
		var _parent = $(this).closest('li');
		var _this = $(this);

		$.post(site_url+'webservices/tobozon_like', { csrf_token: csrf_token, post_id: post_id }, function(reponse)
		{
			if (parseInt(reponse.result) == 0)
				alert(reponse.message);

			else
			{
				if (reponse.message != '')
					alert(reponse.message);
				
				_this.text("Je n'aime plus");
				_parent.attr('class', 'postdislike');
				maj_nb_likes(post_id, 1);
			}
		});

		return false;
	});

	$('.postfootright li[class=postdislike] a').live('click', function(event)
	{
		var post_id = parseInt($(this).attr('href').substr(1));
		var _parent = $(this).closest('li');
		var _this = $(this);

		$.post(site_url+'webservices/tobozon_like_plus', { csrf_token: csrf_token, post_id: post_id }, function(reponse)
		{
			if (parseInt(reponse.result) == 0)
				alert(reponse.message);

			else
			{
				_this.text("J'aime");
				_parent.attr('class', 'postlike');
				maj_nb_likes(post_id, -1);
			}
		});

		return false;
	});

	$('div[class*=blockpost] span[class=like]').live('click', function(event)
	{
		var post_id = $(this).closest('div').attr('id').substr(1);

		$.post(site_url+'webservices/tobozon_like_bouzouks', { csrf_token: csrf_token, post_id: post_id }, function(reponse)
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

	$('#liste_likers input[type=button]').live('click', function(event)
	{
		event.preventDefault();
		$('#liste_likers').remove();
	});
});
