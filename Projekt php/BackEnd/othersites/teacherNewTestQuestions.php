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

    // cofamy do ustawien, jesli nie zostaly wypelnione
    if(!isset($_SESSION['test_name']))
    {
        header('Location: teacherNewTestSettings.php');
    }

    $question = $answerA = $answerB = $answerC = $answerD = $max_points ='';
    $errors = array('question' => '', 'answer' => '', 'question_type' => '', 'max_points' => '', 'ABCD' => '', 'unfitting' => '');
    if(isset($_POST['submit']) || isset($_POST['next_question']))
    {
        // sprawdzamy poprawnosc formularza
        if(empty($_POST['question']))
        {
            $errors['question'] = 'Wpisz treść pytania';
        }
        else
        {
            $question = $_POST['question'];
        }
        if(empty($_POST['max_points']))
        {
            $errors['max_points'] = 'Wpisz maksymalną liczbę punktów';
        }
        else
        {
            $max_points = $_POST['max_points'];
        }
        $n_correct = 0;
        $correct = "0000";
        if(!empty($_POST["A"]))
        {
            $n_correct += 1;
            $correct[0] = "1";
        }
        if(!empty($_POST["B"]))
        {
            $n_correct += 1;
            $correct[1] = "1";
        }
        if(!empty($_POST["C"]))
        {
            $n_correct += 1;
            $correct[2] = "1";
        }
        if(!empty($_POST["D"]))
        {
            $n_correct += 1;
            $correct[3] = "1";
        }
        if(empty($_POST["A"]) && empty($_POST["B"]) && empty($_POST["C"]) && empty($_POST["D"]))
        {
            $errors['ABCD'] = 'Zaznacz poprawną odpowiedź';
        }
        else if($_POST['question_type'] == 'jednokrotny' && $n_correct != 1)
        {
            $errors['unfitting'] = 'Pytanie jednokrotnego wyboru powinno mieć jedną poprawną odpowiedź';
        }
        
        if(!empty($_POST['answerA']))
        {
            $answerA = $_POST['answerA'];
        }
        if(!empty($_POST['answerB']))
        {
            $answerB = $_POST['answerB'];
        }
        if(!empty($_POST['answerC']))
        {
            $answerC = $_POST['answerC'];
        }
        if(!empty($_POST['answerD']))
        {
            $answerD = $_POST['answerD'];
        }
        if(empty($_POST['answerA']) || empty($_POST['answerB'])|| empty($_POST['answerC'])|| empty($_POST['answerD']))
        {
            $errors['answer'] = 'Wpisz treść odpowiedzi';
        }
        if(empty($_POST['question_type']))
        {
            $errors['question_type'] = 'Wybierz rodzaj pytania';
        }

        // jezeli nie ma bledow 
        if(!array_filter($errors))
        {
            // zapis pytania do tablicy pytan
            if($_SESSION['question_number'] == 1)
            {
                unset($_SESSION['questions']);
            }
            $_SESSION['questions'][] = [$question, $_POST['question_type'], intval($max_points), $answerA, $answerB, $answerC, $answerD, $correct];

            // przygotowania do kolejnego pytania
            if(isset($_POST['next_question']))
            {
                $_SESSION['question_number'] += 1;
                $question = $answerA = $answerB = $answerC = $answerD = $max_points ='';
            }
            else if(isset($_POST['submit']))
            {
                require_once "functionalities/connect.php";
                $conn = @new mysqli($host, $db_user, $db_password, $db_name);

                if ($conn->connect_errno != 0)
                {
                    echo "Error: ".$conn->connect_errno;
                    unset($_SESSION['questions']);
                    $_SESSION['question_number'] = 1;
                    $question = $answerA = $answerB = $answerC = $answerD = $max_points ='';
                }
                else
                {
                    // zapis testu
                    $id_nauczyciel = $_SESSION['id_nauczyciel'];
                    $nazwa = $_SESSION['test_name'];
                    $data_utworzenia = date('Y-m-d H:i:s');
                    $ilosc_czasu = $_SESSION['test_duration'];
                    $prog = $_SESSION['threshold'];
                    $sposob_oceniania_pytan = $_SESSION['questions-mark'];
                    $losowa_kolejnosc_pytan = $_SESSION['random_questions'];
                    $losowa_kolejnosc_odpowiedzi = $_SESSION['random_answers'];
                    $maksymalna_ilosc_prob = $_SESSION['n_attempt'];
                    $sposob_oceniania_prob = $_SESSION['attempt'];
                    $cofanie = $_SESSION['go_back'];
                                        
                    $aktywny_od = $_SESSION['test_begin']; // 2022-08-12T17:50
                    $aktywny_od = str_replace('T', ' ', $aktywny_od); // 2022-08-12 17:50
                    $aktywny_od .= ":00"; // 2022-08-12 17:50:00

                    $aktywny_do = $_SESSION['test_end'];
                    $aktywny_do = str_replace('T', ' ', $aktywny_do); 
                    $aktywny_do .= ":00"; 

                    $sql = "INSERT INTO test(id_nauczyciel, nazwa, data_utworzenia, aktywny_od, aktywny_do, ilosc_czasu, prog, sposob_oceniania_pytan, losowa_kolejnosc_pytan, losowa_kolejnosc_odpowiedzi, maksymalna_ilosc_prob, sposob_oceniania_prob, cofanie) VALUES ($id_nauczyciel, '$nazwa', '$data_utworzenia', '$aktywny_od', '$aktywny_do', $ilosc_czasu, '$prog', '$sposob_oceniania_pytan', $losowa_kolejnosc_pytan, $losowa_kolejnosc_odpowiedzi, $maksymalna_ilosc_prob, $sposob_oceniania_prob, $cofanie)";
                    mysqli_query($conn, $sql);
                    $id_test = $conn->insert_id;
                    // zapis pytan
                    for($i = 0; $i <= count($_SESSION['questions']) - 1; $i++)
                    {
                      $question = $_SESSION['questions'][$i][0];
                      $question_type = $_SESSION['questions'][$i][1];
                      $max_points = intval($_SESSION['questions'][$i][2]);
                      $sql = "INSERT INTO pytanie(id_test, tresc, typ, maksymalna_ilosc_punktow, kolejnosc) VALUES ($id_test, '$question', '$question_type', $max_points, $i + 1)";
                      mysqli_query($conn, $sql); // dodanie pytania
                      $id_question = $conn->insert_id;
                      for($j = 0; $j <= 3; $j++)
                      {
                        $answer = $_SESSION['questions'][$i][$j + 3];
                        $correct = intval($_SESSION['questions'][$i][7][$j]);
                        $sql = "INSERT INTO odpowiedz(id_pytanie, tresc, poprawna) VALUES ($id_question, '$answer', $correct)";
                        mysqli_query($conn, $sql); // dodanie odpowiedzi
                      }
                    }

                    mysqli_close($conn);
                    header('Location: teacherProfile.php');                
                }
            }
        }

    }

    include_once('functionalities/headfoot/head.php');    

?>

    <!-- STRONA -->
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
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
                
                
                <div class="col-xl-10 teacher-form-box">
                    <div class="container mt-3 mb-2 p-3 white-box">
                        <div class="row">

                            <div class="test-title-box">
                                <b><?php echo htmlspecialchars($_SESSION['test_name']) ?></b>
                            </div>

                            <div class="test-title-number">
                                <?php echo $_SESSION['question_number']; ?>
                            </div>

                            <div class="container">
                                <div class="row">
                                    <div class="col-xl-5 tresc-pytania">
                                        <input type="text" class="form-control form-control-lg"
                                        placeholder="Treść pytania" name="question" value=<?php echo htmlspecialchars($question) ?>>
                                        <div class="red-text"><?php echo $errors['question']; ?></div>
                                    </div>
                                </div>
                            </div>

                            


                            <div class="container">

                                <div class="d-flex justify-content-center">
                                    <div class="form-check col-1 abc">
                                        <input class="form-check-input" type="radio" id="flexCheckDefault" name="question_type" value="jednokrotny">
                                        <label class="form-check-label" for="flexCheckDefault">
                                        jednokrotne
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center">
                                    <div class="form-check col-1 abc">
                                        <input class="form-check-input" type="radio" id="flexCheckDefault" name="question_type" value="wielokrotny">
                                        <label class="form-check-label" for="flexCheckDefault">
                                        wielokrotne
                                        </label>
                                    </div>
                                </div>

                                <div class="red-text"><?php echo $errors['question_type']; ?></div>

                                <div class="maxPkt">
                                    Maksymalna liczba punktów:
                                    <div class="col-1">
                                        <input type="number"  min="1" max="10" name="max_points" value=<?php echo $max_points ?>>
                                    </div>
                                    <div class="red-text"><?php echo $errors['max_points']; ?></div>
                                </div>


                                <div class="container blok-odpowiedzi">
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <input class="form-check-input" type="checkbox" id="flexCheckDefault" name="A">
                                            <label class="form-check-label" for="flexCheckDefault">
                                            A
                                            </label>
                                        </div>
        
                                        <div class="col tresc-odp">
                                            <input type="text" class="form-control form-control-lg"
                                            placeholder="Treść odpowiedzi" name="answerA" value=<?php echo htmlspecialchars($answerA) ?>>
                                        </div>   

                                        <!-- </div>
                                            <button class="btn btn-default">
                                                <img src="../images/minus.png" width="20" />
                                            </button>
                                        <div>  -->
                                    </div>
        
        
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <input class="form-check-input" type="checkbox" id="flexCheckDefault" name="B">
                                            <label class="form-check-label" for="flexCheckDefault">
                                            B
                                            </label>
                                        </div>
                                            
                                        <div class="col tresc-odp">
                                            <input type="text" class="form-control form-control-lg"
                                            placeholder="Treść odpowiedzi" name="answerB" value=<?php echo htmlspecialchars($answerB) ?>>
                                        </div>
                                    </div>
        
        
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <input class="form-check-input" type="checkbox" id="flexCheckDefault" name="C">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                C
                                            </label>
                                        </div>
        
                                        <div class="col tresc-odp">
                                            <input type="text" class="form-control form-control-lg"
                                            placeholder="Treść odpowiedzi" name="answerC" value=<?php echo htmlspecialchars($answerC) ?>>
                                        </div>
                                    </div>

                                        
                                    <div class="row">
                                        <div class="form-check col-1 abc">
                                            <input class="form-check-input" type="checkbox" id="flexCheckDefault" name="D">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                D
                                            </label>
                                        </div>
        
                                        <div class="col tresc-odp">
                                            <input type="text" class="form-control form-control-lg"
                                            placeholder="Treść odpowiedzi" name="answerD" value=<?php echo htmlspecialchars($answerD) ?>>
                                        </div>
                                    </div>

                                    <div class="red-text"><?php echo $errors['ABCD']; ?></div>
                                    <div class="red-text"><?php echo $errors['unfitting']; ?></div>
                                    <div class="red-text"><?php echo $errors['answer']; ?></div>

                                </div>
                            </div> 
                        </div> 
                    </div>
                </div>
                    <div class="col-mb-2">
                        <div class="text-center">
                            <button type="submit" id="go-back" class="btn btn-primary" name="next_question">Następne pytanie</button>
                        </div>
                    </div>
                                
                                
                    <div class="col-mb-2">
                        <div class="text-center">
                            <button type="submit" id="go-back" class="btn btn-primary" name="submit">Zatwierdź i zakończ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php
    include_once('functionalities/headfoot/foot.php');
 ?>