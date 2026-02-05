<?php 

    session_start();

    if((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true))
    {
        if ((isset($_SESSION['status'])) && $_SESSION['status']=='nauczyciel')
        {
            header('Location: teacherProfile.php');
            exit();
        }

        if (!isset($_SESSION['pytania']) && !isset($_SESSION['odpowiedzi']))
        {
            header('Location: studentExamCodePanel.php');
            $_SESSION['blad'] = '<div class="row justify-content-center" style="color:red">Wystąpił błąd podczas pobierania pytań i odpowiedzi z bazy danych!</div>';
            exit();
        }
    }
    else
    {
        header('Location: ../index.php');
        exit();
    }

    if (!isset($_SESSION['takingExam']))
	{
        $_SESSION['takingExam'] = true;
	}

    if (!isset($_SESSION['pytanieCount']))
	{
        $_SESSION['pytanieCount'] = 0;
	}

    if (!isset($_SESSION['data_rozpoczecia']))
	{
        $_SESSION['data_rozpoczecia'] = date('Y-m-d H:i:s');
	}

    if (!isset($_SESSION['myAnswers'])) 
    {
        $_SESSION['myAnswers'] = array_fill(0, $_SESSION['ile_pytan'], -1);
    }

    include_once('functionalities/headfoot/head.php');

?>

    <div class="container mt-lg-5 mt-3">
        <div class="row gx-5 justify-content-center">


            <div class="col-xl-1 side-bar">
                <figure class="figure">
                    <div class="name-box mt-2">
                        <?php
                            if(isset($_SESSION['skrot']))   echo $_SESSION['skrot']; 
                        ?>
                    </div>
                </figure>

                <script>
                    $(document).ready(function(){
                        
                        setInterval(function(){
                            var time = $("#examTimer").load("functionalities/timer.php #timer").text().split(/:/);
                            timeLeft = time[0] * 3600 + time[1] * 60 + time[2];
                            if(timeLeft <= 1)
                            {
                                document.getElementById("examForm").submit();
                            }
                        }, 1000);
                    });
                </script>

                <span id="examTimer" class="timer center">  </span>
            </div>


            <div class="col-xl-10 student-form-box">
                <div class="container mt-3 mb-2 p-3 white-box">
                    <div class="row">

                        <div class="test-title-box">
                            <b><?php echo $_SESSION['test']['nazwa']; ?></b>
                        </div>

                        <div class="test-title-number">
                            <?php
                                echo '<span>'.($_SESSION['pytanieCount']+1).'</span>';
                            ?>
                        </div>

                        <div class="container">
							<div class="row">
								<div class="col-xl-5 tresc-pytania">
                                    <?php
                                        echo '<span>'.$_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]]['tresc'].'</span>';
                                    ?>
								</div>
							</div>
                        </div>

                        <div class="container">

                            <div class="container blok-odpowiedzi">
                                <form action="functionalities/examSolving.php" method="post" id="examForm">
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <?php
                                                if($_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]]['typ']=='jednokrotny')
                                                {
                                                    if(isset($_SESSION['myAnswers'][$_SESSION['pytanieCount']]) && $_SESSION['myAnswers'][$_SESSION['pytanieCount']] == 0)
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="0" name="flexRadioDefault" id="flexRadioDefault1" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="0" name="flexRadioDefault" id="flexRadioDefault1">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexRadioDefault1">
                                                        A 
                                                        </label>';
                                                }
                                                else
                                                {
                                                    if($_SESSION['myAnswers'][$_SESSION['pytanieCount']] != -1 && in_array(0, (array)$_SESSION['myAnswers'][$_SESSION['pytanieCount']]))
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="0" id="flexCheckDefault1" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="0" id="flexCheckDefault1">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexCheckDefault1">
                                                        A
                                                        </label>';
                                                }
                                            ?>
                                        </div>
        
                                        <div class="col tresc-odp">
                                            <?php
                                                echo '<span>'.$_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][0]]['tresc'].'</span>';
                                            ?>
                                        </div>   
                                    </div>
        
        
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <?php
                                                if($_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]]['typ']=='jednokrotny')
                                                {
                                                    if(isset($_SESSION['myAnswers'][$_SESSION['pytanieCount']]) && $_SESSION['myAnswers'][$_SESSION['pytanieCount']] == 1)
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="1" name="flexRadioDefault" id="flexRadioDefault2" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="1" name="flexRadioDefault" id="flexRadioDefault2">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexRadioDefault2">
                                                        B
                                                        </label>';
                                                }
                                                else
                                                {
                                                    if($_SESSION['myAnswers'][$_SESSION['pytanieCount']] != -1 && in_array(1, (array)$_SESSION['myAnswers'][$_SESSION['pytanieCount']]))
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="1" id="flexCheckDefault2" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="1" id="flexCheckDefault2">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexCheckDefault2">
                                                        B
                                                        </label>';
                                                }
                                            ?>
                                        </div>
                                            
                                        <div class="col tresc-odp">
                                            <?php
                                                echo '<span>'.$_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][1]]['tresc'].'</span>';
                                            ?>
                                        </div>
                                    </div>
        
        
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <?php
                                                if($_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]]['typ']=='jednokrotny')
                                                {
                                                    if(isset($_SESSION['myAnswers'][$_SESSION['pytanieCount']]) && $_SESSION['myAnswers'][$_SESSION['pytanieCount']] == 2)
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="2" name="flexRadioDefault" id="flexRadioDefault3" checked>'; 
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="2" name="flexRadioDefault" id="flexRadioDefault3">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexRadioDefault3">
                                                        C
                                                        </label>';
                                                }
                                                else
                                                {
                                                    if($_SESSION['myAnswers'][$_SESSION['pytanieCount']] != -1 && in_array(2, (array)$_SESSION['myAnswers'][$_SESSION['pytanieCount']]))
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="2" id="flexCheckDefault3" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="2" id="flexCheckDefault3">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexCheckDefault3">
                                                        C
                                                        </label>';
                                                }
                                            ?>
                                        </div>
        
                                        <div class="col tresc-odp">     
                                            <?php
                                                echo '<span>'.$_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][2]]['tresc'].'</span>';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <?php
                                                if($_SESSION['pytania'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]]['typ']=='jednokrotny')
                                                {
                                                    if(isset($_SESSION['myAnswers'][$_SESSION['pytanieCount']]) && $_SESSION['myAnswers'][$_SESSION['pytanieCount']] == 3)
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="3" name="flexRadioDefault" id="flexRadioDefault4" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="radio" value="3" name="flexRadioDefault" id="flexRadioDefault4">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexRadioDefault4">
                                                        D
                                                        </label>';
                                                }
                                                else
                                                {
                                                    if($_SESSION['myAnswers'][$_SESSION['pytanieCount']] != -1 && in_array(3, (array)$_SESSION['myAnswers'][$_SESSION['pytanieCount']]))
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="3" id="flexCheckDefault4" checked>';
                                                    }
                                                    else
                                                    {
                                                        echo '<input class="form-check-input" type="checkbox" name="answer[]" value="3" id="flexCheckDefault4">';
                                                    }

                                                    echo '<label class="form-check-label" for="flexCheckDefault4">
                                                        D
                                                        </label>';
                                                }
                                            ?>
                                        </div>
        
                                        <div class="col tresc-odp">
                                            <?php
                                                echo '<span>'.$_SESSION['odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][$_SESSION['kolejnosc_odpowiedzi'][$_SESSION['kolejnosc_pytan'][$_SESSION['pytanieCount']]][3]]['tresc'].'</span>';
                                            ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div> 
                    </div> 

                    <?php

                        if($_SESSION['pytanieCount']<=0 || !$_SESSION['test']['cofanie'])
                        {
                            echo '<div class="row mt-3">
                            <div class="col-md-1">   
                                    <div class="text-center">
                                        <button type="submit" form="examForm" name="previousQuestion" id="go-back" class="btn btn-primary" disabled>
                                            <i class="bi bi-caret-left-square-fill"></i>      
                                        </button>
                                    </div>
                            </div>';
                        }
                        else
                        {
                            echo '<div class="row mt-3">
                            <div class="col-md-1">   
                                    <div class="text-center">
                                        <button type="submit" form="examForm" name="previousQuestion" id="go-back" class="btn btn-primary">
                                            <i class="bi bi-caret-left-square-fill"></i>      
                                        </button>
                                    </div>
                            </div>';
                        }
                        
                        if($_SESSION['pytanieCount']>=$_SESSION['ile_pytan']-1)
                        {
                            echo '
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col">
                                            <div class="text-center">
                                                <button type="submit" form="examForm" name="finished" id="go-back" class="btn btn-primary">Zatwierdź i zakończ</button>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-1">
                                    <div class="text-center">
                                        <button type="submit" form="examForm" name="nextQuestion" id="go-back" class="btn btn-primary" disabled>
                                            <i class="bi bi-caret-right-square-fill"></i>
                                        </button>
                                    </div>
                            </div>

                            </div>';
                        }
                        else
                        {
                            echo '
                            <div class="col-md-10"></div>
                            
                            <div class="col-md-1">
                                    <div class="text-center">
                                        <button type="submit" form="examForm" name="nextQuestion" id="go-back" class="btn btn-primary">
                                            <i class="bi bi-caret-right-square-fill"></i>
                                        </button>
                                    </div>
                            </div>

                            </div>';
                        }

                    ?>

                </div>
            </div>
        </div>
    </div>

<?php
    include_once('functionalities/headfoot/foot.php');
 ?>