<?php
	
	class Json
	{
		public static function get($data)
		{
            header('Content-Type: application/json');
		    echo json_encode($data);
			exit;
		}
	}

?>