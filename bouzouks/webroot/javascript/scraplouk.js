$(document).ready(function()
{
	function GetUnity()
	{
		if (typeof unityObject != "undefined")
		{
			return unityObject.getObjectById("unityPlayer");
		}

		return null;
	}
	
	if (typeof unityObject != "undefined")
	{
		unityObject.embedUnity("unityPlayer", "http://www.gaddygames.com/games/bouzouks/WebPlayer.unity3d", 384, 624);
	}
});
