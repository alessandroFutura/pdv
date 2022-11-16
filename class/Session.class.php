<?php

	class Session
	{
        
	    public static function isUser()
		{
			return isset($_SESSION["user_id"]) && isset($_SESSION["user_session_id"]);
		}
		
		public static function saveSessionUser($params)
		{
		    $_SESSION["user_id"] = $params->user_id;
            $_SESSION["user_session_id"] = session_id();
            $_SESSION["user_login"] = date("Y-m-d H:i:s");
		}

		public static function reset()
		{
            session_regenerate_id();
            session_destroy();
        }

        public static function check()
        {
            if(!self::isUser()){
                headerResponse((Object)[
                    "code" => 401,
                    "message" => "Não autorizado."
                ]);
            }
        }

	}

?>