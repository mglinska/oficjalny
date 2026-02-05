<?php

	session_start();
	
	if ((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
	{
        if (isset($_SESSION['status']))
        {
            if($_SESSION['status']=='student')
            {
                header('Location: othersites/studentProfile.php');
                exit();
            }
            else if($_SESSION['status']=='nauczyciel')
            {
                header('Location: othersites/teacherProfile.php');
                exit();
            }

        }
	}

    include_once('othersites/functionalities/headfoot/indexHead.php');

?>

    <div class="container mt-lg-5 pt-lg-5">
        <div class="text-center mt-5">
            <h1 id="login-title" class="h1">Witaj w systemie egzaminacji online!</h1>
        </div>
        <div class="container">
            <?php
	            if(isset($_SESSION['blad']))	echo $_SESSION['blad'];
            ?>
            <form action="othersites/functionalities/log.php" method="post">
                <div class="row justify-content-center">
                    <div class="col-lg-3 mb-3 mt-3">
                        <input type="text" id="login-inputs" class="form-control form-control-lg" placeholder="Login"
                            name="login">
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class=" col-lg-3 mb-3">
                        <input type="password" id="pass-inputs" class="form-control form-control-lg" placeholder="Hasło"
                            name="haslo">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" id="login-submit" class="btn btn-primary">Zaloguj się</button>
                </div>
            </form>
        </div>
    </div>

 <?php
    include_once('othersites/functionalities/headfoot/indexFoot.php');
 ?>


