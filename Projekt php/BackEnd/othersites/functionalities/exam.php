<?php

	session_start();

    if (!isset($_POST['exam-code']))
	{
		header('Location: ../studentExamCodePanel.php');
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

        $exam_code = $_POST['exam-code'];
		
		$exam_code = htmlentities($exam_code, ENT_QUOTES, "UTF-8");
	
		if ($rezultat = @$polaczenie->query(
		sprintf("SELECT * FROM test WHERE id_test='%s'",
		mysqli_real_escape_string($polaczenie,$exam_code))))
		{
			$ile_testow = $rezultat->num_rows;
			if($ile_testow>0)
			{		
				$_SESSION['test'] = $rezultat->fetch_assoc();
				
				if($rezultat = @$polaczenie->query(
				sprintf("SELECT COUNT(*) as proby FROM `proba` WHERE id_test='%s' AND id_student='%s'",
				mysqli_real_escape_string($polaczenie,$_SESSION['test']['id_test']),
				mysqli_real_escape_string($polaczenie,$_SESSION['id_student']))))
				{
					$proby = $rezultat->fetch_assoc();

					if($proby['proby'] >= $_SESSION['test']['maksymalna_ilosc_prob'])
					{
						$_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Zużyto maksymalną ilość prób!</div>';
						header('Location: ../studentExamCodePanel.php');
						$polaczenie->close();
						exit();
					}
				}

				$teraz = date("Y-m-d H:i:s");

				if($teraz >= $_SESSION['test']['aktywny_do'])
                {
                    $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Za późno, test jest już nieaktywny!</div>';
					header('Location: ../studentExamCodePanel.php');
					$polaczenie->close();
					exit();
                }
                else if($teraz <= $_SESSION['test']['aktywny_od'])
                {
                    $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Test jeszcze nie jest aktywny, proszę czekąć!</div>';
					header('Location: ../studentExamCodePanel.php');
					$polaczenie->close();
					exit();
                }


				unset($_SESSION['blad']);
				$rezultat->free_result();
                
				header('Location: questions.php');
				
			} else {
				$_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Nieprawidłowy kod!</div>';
				header('Location: ../studentExamCodePanel.php');
			}
			
		}
		
		$polaczenie->close();

    }

?>