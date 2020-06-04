$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Gérer                                   */
	/*--------------------------------------------------*/
	$('form[class=demissionner]').submit(function()
	{
		return confirm('Veux-tu vraiment démissionner ?');
	});
});
