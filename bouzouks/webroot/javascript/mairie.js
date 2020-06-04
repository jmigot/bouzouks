$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Gérer                                   */
	/*--------------------------------------------------*/
	$('#mairie-gerer #montant_don_mendiants').bind('keyup', function()
	{
		var nb_mendiants = $('#nb_mendiants').text();
		var montant      = $(this).val();
		var resultat     = nb_mendiants * montant;

		if (resultat > 0 && nb_mendiants < 5)
		{
			$('#resultat_don_mendiants').text('Il faut 5 mendiants minimum').fadeTo('fast', 1);
		}

		else if (resultat > 0)
		{
			$('#resultat_don_mendiants').text(nb_mendiants+' mendiants x '+montant+' struls = '+resultat+' struls').fadeTo('fast', 1);
		}

		else
		{
			$('#resultat_don_mendiants').html('&nbsp;').fadeTo('fast', 0);
		}
	});

	$('#mairie-gerer #montant_don_bouzouks').bind('keyup', function()
	{
		var nb_bouzouks = $('#nb_bouzouks').text();
		var montant     = $(this).val();
		var resultat    = nb_bouzouks * montant;

		if (resultat > 0)
		{
			$('#resultat_don_bouzouks').text(nb_bouzouks+' bouzouks x '+montant+' struls = '+resultat+' struls').fadeTo('fast', 1);
		}

		else
		{
			$('#resultat_don_bouzouks').html('&nbsp;').fadeTo('fast', 0);
		}
	});

	$('#mairie-gerer #montant_don_tranche, #mairie-gerer #min_struls_don_tranche, #mairie-gerer #max_struls_don_tranche, #mairie-gerer #par_fortune, #mairie-gerer label[for=par_fortune]').bind('keyup click', function()
	{
		var nb_bouzouks = 0;
		var min_struls  = $('#min_struls_don_tranche').val();
		var max_struls  = $('#max_struls_don_tranche').val();
		var par_fortune = $('input[name="par_fortune"]').is(':checked');
		var montant     = $('#montant_don_tranche').val();

		if (min_struls == '' || max_struls == '' || montant == '' || montant <= 0)
		{
			$('#resultat_don_tranche').html('&nbsp;').fadeTo('fast', 0);
			return;
		}

		// Données invalides, pas de requête
		if (min_struls < 0 || max_struls < 5 || min_struls > max_struls - 5)
		{
			$('#resultat_don_tranche').html("5 struls d'écart minimum").fadeTo('fast', 1);
			return;
		}

		// Requête pour connaître le nombre de bouzouks concernés et faire le calcul
		$.post(site_url+'webservices/nb_bouzouks_tranche_don', { csrf_token: csrf_token, min_struls: min_struls, max_struls: max_struls, par_fortune: par_fortune }, function(retour)
		{
			if (retour != '')
			{
				var min_struls_verif  = $('#min_struls_don_tranche').val();
				var max_struls_verif  = $('#max_struls_don_tranche').val();
				var montant_verif     = $('#montant_don_tranche').val();

				if (min_struls_verif != min_struls || max_struls_verif != max_struls || montant_verif != montant)
					return;

				nb_bouzouks = parseInt(retour.nb_bouzouks);
				var resultat = nb_bouzouks * montant;
				$('#resultat_don_tranche').html(nb_bouzouks+' bouzouks x '+montant+' struls = '+resultat+' struls').fadeTo('fast', 1);
			}
		});
	});

	$('#mairie-gerer div[class*="donations"] form').submit(function() {
		return confirm('Tu es bien certain de vouloir faire ce don ?');
	});
});


