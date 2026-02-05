<?php

    session_start();

    if((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
    {
        if ((isset($_SESSION['status'])) && $_SESSION['status']=='nauczyciel')
        {
                header('Location: ../teacherProfile.php');
                exit();
        }
    }
    else
    {
        header('Location: ../../index.php');
        exit();
    }

    
    if (!isset($_SESSION['time']))
	{
        $_SESSION['time'] = time();
	}

    $diff = time() - $_SESSION['time'];
    $diff = $_SESSION['test']['ilosc_czasu']*60-1 - $diff;

    $hours = intdiv($diff, 60*1000);
    $minutes = (int)$diff/(60);
    $seconds = $diff%60;

    $show = date('H:i:s', mktime($hours, $minutes, $seconds));

    if($diff == 1)
    {
        echo '<span id="timer">'.$show.'</span>';
        if (!isset($_SESSION['timeOver']))
        {
            $_SESSION['timeOver'] = 1;
        }
    }
    else
    {
        echo '<span id="timer">'.$show.'</span>';
    }


?>
