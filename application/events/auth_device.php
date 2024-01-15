<?php

class Auth_Device_Event implements Event_Interface
{

    public function run()
    {
        Registry::set("time", time());


        return true;
    }

}

?>