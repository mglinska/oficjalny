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

    include_once('functionalities/headfoot/head.php');

?>

    <div class="container mt-lg-5 pt-lg-5 mt-3">
        <div class="row gx-5 justify-content-center">
            <div class="col-xl-1 side-bar">
                <figure class="figure">
                    <div class="name-box mt-2">
                        <?php
                            if(isset($_SESSION['skrot']))   echo $_SESSION['skrot']; 
                        ?>
                    </div>
                </figure>
            </div>
            <div class="col-xl-11 student-exam-code-panel">
                <div class="text-center mt-xl-5 pt-xl-5 mt-4">
                    <h4 id="student-exam-code-title" class="h4">Wprowadź kod</h4>
                </div>
                <div class="container mx-auto mb-5 mt-2">
                    <form action="functionalities/exam.php" method="post">
                        <div class="row justify-content-center">
                            <div class="col-lg-7 mb-1 mt-1">
                                <input type="text" id="exam-code-input" class="form-control form-control-lg"
                                    placeholder="Kod" name="exam-code" min="1">
                                    <?php
                                        if(isset($_SESSION['blad']))	echo $_SESSION['blad'];
                                    ?>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="submit" id="exam-code-submit" class="btn btn-primary">Zatwierdź</button>
                        </div>
                    </form>
                    <form action="functionalities/menuGoBack.php">
                        <div class="text-center m-4">
                            <button type="submit" id="go-back" class="btn btn-primary">Powrót</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
    include_once('functionalities/headfoot/foot.php');
 ?>