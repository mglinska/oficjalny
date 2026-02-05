<?php

	session_start();

    if (!isset($_SESSION['pytania']))
	{
		header('Location: ../studentExamCodePanel.php');
        $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania odpowiedzi z bazy danych!</div>';
		exit();
	}

    require_once "connect.php";

	$polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($polaczenie->connect_errno!=0)
	{
		echo "Error: ".$polaczenie->connect_errno;
	}
	else
	{
        $odpowiedzi = array();

        foreach($_SESSION['pytania'] as $wiersz)
        {
            if ($rezultat = @$polaczenie->query(
                sprintf("SELECT * FROM odpowiedz WHERE id_pytanie='%s'",
                mysqli_real_escape_string($polaczenie,$wiersz['id_pytanie']))))
                {
                    $ile_odpowiedzi = $rezultat->num_rows;
                    if($ile_odpowiedzi>0)
                    {		             
                        array_push($odpowiedzi, $rezultat->fetch_all(MYSQLI_ASSOC));

                        $rezultat->free_result();
                        
                    } else {
                        $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania odpowiedzi z bazy danych!</div>';
                        header('Location: ../studentExamCodePanel.php');
                        $polaczenie->close();
                        exit();
                    }
                    
                }
        }

        $_SESSION['odpowiedzi'] = $odpowiedzi;

        $temp = array_fill(0, $_SESSION['ile_pytan'], range(0, 3));

        if($_SESSION['test']['losowa_kolejnosc_odpowiedzi'])
        {
            for ($i = 0; $i < $_SESSION['ile_pytan']; $i++) {
                shuffle($temp[$i]);
            }
        }

        $_SESSION['kolejnosc_odpowiedzi'] = $temp;

        unset($_SESSION['blad']);
        header('Location: ../studentExamSolve.php');

		$polaczenie->close();

    }

?>