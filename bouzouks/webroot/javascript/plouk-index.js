$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Index                                   */
	/*--------------------------------------------------*/
	// Clic sur "Suivre" pour suivre en tant que spectateur
	$('form[class=suivre]').submit(function()
	{
		var tmp = $(this).attr('id').split('_');
		var partie_id = tmp[1];

		// Si la partie nécessite un mot de passe
		if ($('input[id=prive_'+partie_id+']').val() == 1)
		{
			// On demande le mot de passe
			var mot_de_passe = prompt('Mot de passe : ');

			if (mot_de_passe == null)
				return false;
			
			// On l'insère dans le champ
			$('input[id=mot_de_passe_'+partie_id+']').val(mot_de_passe);
		}
	});

	var Connectes = function()
	{
		var _this = this;
		
		this.connectes_id     = '#connectes';
		this.pause_rafraichir = 3500;
		
		this.rafraichir = function()
		{
			$.post(site_url+'webservices_plouk/connectes_plouk', { csrf_token: csrf_token }, function(reponse)
			{
				// On rafraîchit la liste des connectés
				$(_this.connectes_id).empty();

				var connectes = '<tr>';
				
				if (reponse.connectes != '')
				{
					$.each(reponse.connectes, function(cle, joueur) {
						connectes += '<td>'+joueur+'</td>';

						if (cle > 0 && cle % 4 == 0)
							connectes += '</tr><tr>';
					});
				}

				connectes += '</tr>';
				$(connectes).appendTo(_this.connectes_id)
			});
			setTimeout(function() { _this.rafraichir(); }, _this.pause_rafraichir);
		}
	};
	
	// On démarre le rafraichissement des connectés
	var connectes = new Connectes();
	connectes.rafraichir();
});
