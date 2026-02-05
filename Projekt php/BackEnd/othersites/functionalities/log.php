<?php

	session_start();
	
	if ((!isset($_POST['login'])) || (!isset($_POST['haslo'])))
	{
		header('Location: ../../index.php');
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
		$login = $_POST['login'];
		$haslo = $_POST['haslo'];
		
		$login = htmlentities($login, ENT_QUOTES, "UTF-8");
		$haslo = htmlentities($haslo, ENT_QUOTES, "UTF-8");
	
		if ($rezultat = @$polaczenie->query(
		sprintf("SELECT * FROM student WHERE login_student='%s' AND haslo='%s'",
		mysqli_real_escape_string($polaczenie,$login),
		mysqli_real_escape_string($polaczenie,$haslo))))
		{
			$ilu_userow = $rezultat->num_rows;
			if($ilu_userow>0)
			{
				$_SESSION['zalogowany'] = true;
				
				$wiersz = $rezultat->fetch_assoc();
				$_SESSION['login_student'] = $wiersz['login_student'];
				$_SESSION['imie'] = $wiersz['imie'];
				$_SESSION['skrot'] = '<span style="text-transform:uppercase;">'.$_SESSION['login_student'][0].$_SESSION['login_student'][1].'</span>';
				$_SESSION['status'] = 'student';

				$login_student = $_SESSION['login_student'];
				$sql = "SELECT id_student FROM student WHERE login_student='$login_student'";
				$result = mysqli_query($polaczenie, $sql);
				$_SESSION['id_student'] = intval(mysqli_fetch_assoc($result)["id_student"]);
				
				unset($_SESSION['blad']);
				$rezultat->free_result();
				header('Location: ../studentProfile.php');
				
			} else {
				
				if ($rezultat = @$polaczenie->query(
				sprintf("SELECT * FROM nauczyciel WHERE login_nauczyciel='%s' AND haslo='%s'",
				mysqli_real_escape_string($polaczenie,$login),
				mysqli_real_escape_string($polaczenie,$haslo))))
				{
					$ilu_userow = $rezultat->num_rows;
					if($ilu_userow>0){

						$_SESSION['zalogowany'] = true;
				
						$wiersz = $rezultat->fetch_assoc();
						$_SESSION['login_nauczyciel'] = $wiersz['login_nauczyciel'];
						$_SESSION['imie'] = $wiersz['imie'];
						$_SESSION['skrot'] = '<span style="text-transform:uppercase;">'.$_SESSION['login_nauczyciel'][0].$_SESSION['login_nauczyciel'][1].'</span>';
						$_SESSION['status'] = 'nauczyciel';

						$login_nauczyciel = $_SESSION['login_nauczyciel'];
						$sql = "SELECT id_nauczyciel FROM nauczyciel WHERE login_nauczyciel='$login_nauczyciel'";
						$result = mysqli_query($polaczenie, $sql);
						$_SESSION['id_nauczyciel'] = intval(mysqli_fetch_assoc($result)["id_nauczyciel"]);
						
						unset($_SESSION['blad']);
						$rezultat->free_result();
						header('Location: ../teacherProfile.php');

					} else {
						$_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Nieprawidłowy login lub hasło!</div>';
						header('Location: ../../index.php');
					}
				}
				
			}
			
		}
		
		$polaczenie->close();
	}
	
?>