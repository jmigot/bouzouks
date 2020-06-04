$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Clans                                   */
	/*--------------------------------------------------*/

	// Afficher/Cacher le détail des actions
	$('#site-faq-clans div[id*=action_image_]').click(function()
	{
		var action_id = $(this).attr('id').split('_')[2];

		// Le détail est déjà affiché, on le cache
		if ($('#site-faq-clans div[id=action_details_'+action_id+']').is(':visible'))
			$('#site-faq-clans div[id=action_details_'+action_id+']').slideUp();
		
		// Sinon on affiche le détail et on cache l'ancien
		else
		{
			$('#site-faq-clans div[id=action_details_'+action_id+']').slideDown(500);

			$('#site-faq-clans div[id*=action_details_]').each(function()
			{
				if ($(this).attr('id').split('_')[2] != action_id)
					$(this).slideUp(350);
			});
		}		
	});
});
