<?php

	session_start();

    if((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany'] == true))
    {
        if ((isset($_SESSION['status'])) && $_SESSION['status']=='nauczyciel')
        {
            header('Location: ../teacherProfile.php');
            exit();
        }
        else if(!isset($_SESSION['takingExam']) && $_SESSION['takingExam'] == false)
        {
            header('Location: ../studentProfile.php');
            exit();
        }
    }
    else
    {
        header('Location: ../../index.php');
        exit();
    }
    
    if (isset($_SESSION['pytanieCount']))
    {
        if($_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]]['typ']=='jednokrotny')
        {
            if(isset($_POST['flexRadioDefault'])) 
            {
                if(isset($_SESSION['myAnswers'][$_SESSION['pytanieCount']]))
                {
                    $_SESSION['myAnswers'][$_SESSION['pytanieCount']] = $_POST['flexRadioDefault'];
                }
            }
        }
        else
        {
            $_SESSION['myAnswers'][$_SESSION['pytanieCount']] = $_POST['answer'];

            if(empty($_SESSION['myAnswers'][$_SESSION['pytanieCount']]))
            {
                $_SESSION['myAnswers'][$_SESSION['pytanieCount']] = -1;
            }
        }
    }

    if (isset($_POST['nextQuestion'])) 
    {
        $_SESSION['pytanieCount']++;
    } 
    else if (isset($_POST['previousQuestion'])) 
    {
        $_SESSION['pytanieCount']--;
    }
    else if (isset($_POST['finished']) || (isset($_SESSION['timeOver']) && $_SESSION['timeOver'])) 
    {
        if (!isset($_SESSION['checkingExam']))
        {
            $_SESSION['checkingExam'] = true;
        }

        if (!isset($_SESSION['data_zakonczenia']))
        {
            $_SESSION['data_zakonczenia'] = date('Y-m-d H:i:s');
        }

        $_SESSION['takingExam'] = false;
        header('Location: examGrade.php');
        exit();
    }

    header('Location: ../studentExamSolve.php');

?>