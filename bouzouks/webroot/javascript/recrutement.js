$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Lister                                  */
	/*--------------------------------------------------*/
	$('#recrutement-lister  a[href=#]').click(function() {
		if ($(this).html() == 'Cacher')
		{
			$('.texte_'+$(this).attr('class')).slideToggle('fast');
			$(this).html('Afficher');
		}

		else
		{
			$('.texte_'+$(this).attr('class')).slideToggle('fast');
			$(this).html('Cacher');
		}

		return false;
	});

	$("#recrutement-lister #case_supprimer_tous").click(function()
	{
		// On coche/décoche toutes les cases des messages en fonction de la case "Tous"
		$('#recrutement-lister [type="checkbox"][name="annonces_ids[]"]').attr("checked", this.checked);
	});
});

