<?php
    session_start();

    if((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
    {
        if ((isset($_SESSION['status'])) && $_SESSION['status']=='nauczyciel')
        {
                header('Location: teacherProfile.php');
                exit();
        }
    }
    else
    {
        header('Location: ../index.php');
        exit();
    }

    require_once "connect.php";
    $polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($polaczenie->connect_errno != 0)
    {
        echo "Error: ".$polaczenie->connect_errno;
    }
    else
    {
        if ($rezultat = @$polaczenie->query(
        sprintf("SELECT * FROM proba WHERE id_student='%s'",
        mysqli_real_escape_string($polaczenie,$_SESSION['id_student']))))
        {
            $ile_prob = $rezultat->num_rows;;

            if($ile_prob > 0)
            {
                $proby = array();

                while($wiersz = $rezultat->fetch_assoc())
                {
                    if ($rezultat2 = @$polaczenie->query(
                        sprintf("SELECT * FROM test WHERE id_test='%s'",
                        mysqli_real_escape_string($polaczenie,$wiersz['id_test']))))
                        {
                            $ile_test = $rezultat2->num_rows;
                            if($ile_test > 0)
                            {		  
                                while($wiersz2 = $rezultat2->fetch_assoc())             
                                {
                                    $teraz = date('Y-m-d H:i:s');
                                    if(($teraz >= $wiersz2['aktywny_od']) && ($teraz <= $wiersz2['aktywny_do']))
                                    {
                                        $status = "aktywny";
                                    }
                                    else
                                    {
                                        $status = "nieaktywny";
                                    }
                                    $proba = array($wiersz2['id_test'],$wiersz2['nazwa'],$wiersz2['aktywny_od'],$wiersz2['aktywny_do'],$status,$wiersz['data_rozpoczecia'],$wiersz['data_zakonczenia'],$wiersz['ocena']);
                                    array_push($proby, $proba);
                                }
                
                                $rezultat2->free_result();
                                 
                            } 
                            else if($ile_test < 0)
                            {
                                $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania wyników z bazy danych!</div>';
                                header('Location: ../studentDatabase.php');
                                $polaczenie->close();
                                exit();
                            } 
                        }
                }

                array_multisort(array_column($proby, 6), SORT_DESC, $proby );
                $_SESSION['proby'] = $proby;

                unset($_SESSION['blad']);
                $rezultat->free_result();

            }
            else if($ile_prob < 0)
            {
                $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania wyników z bazy danych!</div>';
                header('Location: ../studentDatabase.php');
            } 
        }
        header('Location: ../studentDatabase.php');

        $polaczenie->close();

    }

?>
