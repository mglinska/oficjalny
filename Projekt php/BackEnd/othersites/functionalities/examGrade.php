<?php

    session_start();

    if((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
    {
        if ((isset($_SESSION['status'])) && $_SESSION['status']=='nauczyciel')
        {
            header('Location: ../teacherProfile.php');
            exit();
        }
        else if(!isset($_SESSION['checkingExam']) && $_SESSION['checkingExam'] == false)
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

    $maxPoints = 0;

    for ($i = 0; $i < $_SESSION['ile_pytan']; $i++)
    {
        $maxPoints += $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
    }


    $myPoints = 0;


    for ($i = 0; $i < $_SESSION['ile_pytan']; $i++)
    {
        if($_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['typ']=='jednokrotny')
        {
            if($_SESSION['myAnswers'][$i] == -1)
            {
                continue;
            }

            if($_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$i]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$i]][$_SESSION['myAnswers'][$i]]]['poprawna'])
            {
                $myPoints += $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
            }
            else if($_SESSION['test']['sposob_oceniania_pytan'][0] == 2)
            {
                $myPoints -= $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
            }
        }
        else
        {

            if($_SESSION['myAnswers'][$i] == -1)
            {
                continue;
            }

            $correctAnswers = 0;
            $correctChecked = 0;
            $notCorrectChecked = 0;
    
            for($j = 0; $j < 4; $j++)
            {
                if($_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$i]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$i]][$j]]['poprawna']) 
                {
                    $correctAnswers++;
                }
            }
    
            for($j = 0; $j < count($_SESSION['myAnswers'][$i]); $j++)
            {
                if($_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$i]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$i]][$_SESSION['myAnswers'][$i][$j]]]['poprawna']) 
                {
                    $correctChecked++;
                }
                else
                {
                    $notCorrectChecked++;
                }
            }
    
            if($correctAnswers==$correctChecked && $notCorrectChecked == 0)
            {
                $myPoints += $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
            }
            else if($notCorrectChecked > 0 && $correctChecked == 0)
            {
                if($_SESSION['test']['sposob_oceniania_pytan'][3] == 2)
                {
                    $myPoints -= $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
                }
            }
            else if($notCorrectChecked == 0 && $correctAnswers > $correctChecked)
            {
                if($_SESSION['test']['sposob_oceniania_pytan'][1] == 1)
                {
                    $gainedPoints = $correctChecked/$correctAnswers*$_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
                    $myPoints += $gainedPoints;
                }
                else if($_SESSION['test']['sposob_oceniania_pytan'][1] == 3)
                {
                    $myPoints -= $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
                }
            }
            else
            {
                if($_SESSION['test']['sposob_oceniania_pytan'][2] == 2)
                {
                    $gainedPoints = ($correctChecked-$notCorrectChecked)/$_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
                    $myPoints += $gainedPoints;
                }
                else if($_SESSION['test']['sposob_oceniania_pytan'][2] == 3)
                {
                    $gainedPoints = ($correctChecked-$notCorrectChecked)/$_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
                    if($gainedPoints > 0)
                    {
                        $myPoints += $gainedPoints;
                    }
                }
                else if($_SESSION['test']['sposob_oceniania_pytan'][2] == 4)
                {
                    $myPoints -= $_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$i]]['maksymalna_ilosc_punktow'];
                }
            }
            
        }
    }

    $percentage = ($myPoints/$maxPoints)*100;
    
    $progi = explode(",", $_SESSION['test']['prog']);
    
    $progi = array_chunk($progi, 2);
    
    $grade = 0;
    
    for($i = 0; $i < 5; $i++)
    {
        if($percentage >= $progi[$i][0] && $percentage <= $progi[$i][1])
        {
            $grade = $i+1;
            break;
        }
    }

    $passed = 0;

    if($grade >= 3)
    {
        $passed = 1; 
    }
    
    $date1 = date_create_from_format('Y-m-d H:i:s', $_SESSION['data_rozpoczecia']);
    $date2 = date_create_from_format('Y-m-d H:i:s', $_SESSION['data_zakonczenia']);
    $sTime = date_diff($date2, $date1);
    
    $solvingTime = $sTime->format("%H:%I:%S");


    require_once "connect.php";
    $polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($polaczenie->connect_errno != 0)
    {
        echo "Error: ".$polaczenie->connect_errno;
    }
    else
    {
        if(!($rezultat = @$polaczenie->query(
            sprintf("INSERT INTO proba(id_student, id_test, ocena, zaliczony, data_rozpoczecia, data_zakonczenia, czas_rozwiazania) VALUES ('%s','%s','%s','%s','%s','%s','%s')",
            mysqli_real_escape_string($polaczenie,$_SESSION['id_student']),
            mysqli_real_escape_string($polaczenie,$_SESSION['test']['id_test']),
            mysqli_real_escape_string($polaczenie,$grade),
            mysqli_real_escape_string($polaczenie,$passed),
            mysqli_real_escape_string($polaczenie,$_SESSION['data_rozpoczecia']),
            mysqli_real_escape_string($polaczenie,$_SESSION['data_zakonczenia']),
            mysqli_real_escape_string($polaczenie,date("H:i:s", strtotime($solvingTime)))))))
        {
            $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas wysłania wyniku do bazy danych!</div>';
            header('Location: ../studentProfile.php');
            $polaczenie->close();
            exit();
        }

        unset($_SESSION['blad']);

        header('Location: grades.php');

        $polaczenie->close();
    }

    unset($_SESSION['test']);
    unset($_SESSION['pytania']);
    unset($_SESSION['odpowiedzi']);
    unset($_SESSION['kolejnosc_pytan']);
    unset($_SESSION['kolejnosc_odpowiedzi']);
    unset($_SESSION['pytanieCount']);
    unset($_SESSION['takingExam']);
    unset($_SESSION['checkingExam']);
    unset($_SESSION['myAnswers']);
    unset($_SESSION['data_rozpoczecia']);
    unset($_SESSION['data_zakonczenia']);
    unset($_SESSION['ile_pytan']);
    unset($_SESSION['time']);
    unset($_SESSION['timeOver']);


?>