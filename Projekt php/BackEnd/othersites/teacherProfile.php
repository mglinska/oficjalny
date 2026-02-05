<?php

	session_start();

    if((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
    {
        if ((isset($_SESSION['status'])) && $_SESSION['status']=='student')
        {
                header('Location: studentProfile.php');
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
            <div class="col-xl-10 mx-xl-3 teacher-panel">
                <div class="text-center mt-xl-5 mt-3">
                    <h1 id="teacher-title" class="h1">Jesteś zalogowany jako nauczyciel</h1>
                </div>
                <div class="container mx-auto mb-5 mt-3 teacher-form-box">
                    <div class="row justify-content-center py-5">
                        <div class="col-lg col-md-6 col-sm-7 pb-3">
                            <form action="teacherNewTestSettings.php" method="post">
                                <div class="teacher-btns" onclick="this.parentNode.submit()">Dodaj test<br /><i
                                        class="bi bi-plus-circle panel-icons"></i> </div>
                            </form>
                        </div>
                        <div class="col-lg col-md-6  col-sm-7 pb-3">
                            <form action="teacherArchivePanel.php" method="post">
                                <div class="teacher-btns" onclick="this.parentNode.submit()">Baza<br /><i
                                        class="bi bi-archive panel-icons"></i> </div>
                            </form>
                        </div>
                        <div class="col-lg col-md-6  col-sm-7 pb-3">
                            <form action="functionalities/logout.php" method="post">
                                <div class="teacher-btns" onclick="this.parentNode.submit()">Wyloguj się<br /><i
                                        class="bi bi-box-arrow-right panel-icons"></i> </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
    include_once('functionalities/headfoot/foot.php');
 ?>