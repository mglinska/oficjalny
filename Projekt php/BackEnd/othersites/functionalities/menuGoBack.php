<?php

	session_start();
	
	unset($_SESSION['blad']);
    unset($_SESSION['proby']);
	
	if ((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
	{
        if (isset($_SESSION['status']))
        {
            if($_SESSION['status']=='student')
            {
                header('Location: ../studentProfile.php');
                exit();
            }
            else if($_SESSION['status']=='nauczyciel')
            {
                header('Location: ../teacherProfile.php');
                exit();
            }

        }
	}
    else
    {
        header('Location: ../../index.php');
        exit();
    }

?>