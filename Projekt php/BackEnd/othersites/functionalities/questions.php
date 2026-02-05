<?php

	session_start();
	
	if (!isset($_SESSION['test']))
	{
		header('Location: ../studentExamCodePanel.php');
        $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania pytań z bazy danych!</div>';
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
		if ($rezultat = @$polaczenie->query(
		sprintf("SELECT id_pytanie FROM pytanie WHERE id_test='%s'",
		mysqli_real_escape_string($polaczenie,$_SESSION['test']['id_test']))))
		{
			$ile_pytan = $rezultat->num_rows;

            $_SESSION['ile_pytan'] = $ile_pytan;

			if($ile_pytan>0)
			{		
                $pytania = array();
                
				while($wiersz = $rezultat->fetch_assoc())
                {
                    if ($rezultat2 = @$polaczenie->query(
                        sprintf("SELECT * FROM pytanie WHERE id_pytanie='%s'",
                        mysqli_real_escape_string($polaczenie,$wiersz['id_pytanie']))))
                        {
                            $pytanie = $rezultat2->num_rows;
                            if($pytanie>0)
                            {		               
                                $wiersz2 = $rezultat2->fetch_assoc();

                                array_push($pytania, $wiersz2);
                
                                $rezultat2->free_result();
                                 
                            } else {
                                $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania pytań z bazy danych!</div>';
                                header('Location: ../studentExamCodePanel.php');
								$polaczenie->close();
								exit();
                            } 
                        }
                }

                $_SESSION['pytania'] = $pytania;

				$temp = range(0, $_SESSION['ile_pytan']-1);

				if($_SESSION['test']['losowa_kolejnosc_pytan'])
				{
					shuffle($temp);
				}

				$_SESSION['kolejnosc_pytan'] = $temp;

				unset($_SESSION['blad']);
				$rezultat->free_result();
                
				header('Location: answers.php');
				
			} else {
				$_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania pytań z bazy danych!</div>';
				header('Location: ../studentExamCodePanel.php');
			}
			
		}
		
		$polaczenie->close();

    }

?>