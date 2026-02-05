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

    $test_name = $test_duration = $test_begin = $test_end = $n_attempt = '';
    $threshold_5_from = $threshold_4_from = $threshold_4_to = $threshold_3_from = $threshold_3_to = $threshold_2_from = $threshold_2_to = $threshold_1_to ='';
    $errors = array('test_name' => '', 'test_duration' => '', 'test_begin' => '', 'test_end' => '', 'n_attempt' => '', 'threshold' => '');

    if(isset($_POST['submit']))
    {
        // sprawdzenie poprawnosci danych z formularza
        if(empty($_POST['test-name']))
        {
            $errors['test_name'] = 'Wpisz nazwę testu';
        }
        else
        {
            $test_name = $_POST['test-name'];
        }
        if(empty($_POST['test-duration']))
        {
            $errors['test_duration'] = 'Wpisz czas trwania testu';
        }
        else
        {
            $test_duration = $_POST['test-duration'];
        }
        if(empty($_POST['test-begin']))
        {
            $errors['test_begin'] = 'Wpisz czas aktywnosci testu';
        }
        else
        {
            $test_begin = $_POST['test-begin'];
        }
        if(empty($_POST['test-end']))
        {
            $errors['test_end'] = 'Wpisz czas aktywnosci testu';
        }
        else
        {
            $test_end = $_POST['test-end'];
        }
        if(!empty($_POST['threshold-5-from']))
        {
            $threshold_5_from= $_POST['threshold-5-from'];
        }
        if(!empty($_POST['threshold-4-from']))
        {
            $threshold_4_from= $_POST['threshold-4-from'];
        }
        if(!empty($_POST['threshold-4-to']))
        {
            $threshold_4_to= $_POST['threshold-4-to'];
        }
        if(!empty($_POST['threshold-3-from']))
        {
            $threshold_3_from= $_POST['threshold-3-from'];
        }
        if(!empty($_POST['threshold-3-to']))
        {
            $threshold_3_to= $_POST['threshold-3-to'];
        }
        if(!empty($_POST['threshold-2-from']))
        {
            $threshold_2_from= $_POST['threshold-2-from'];
        }
        if(!empty($_POST['threshold-2-to']))
        {
            $threshold_2_to= $_POST['threshold-2-to'];
        }
        if(!empty($_POST['threshold-1-to']))
        {
            $threshold_1_to= $_POST['threshold-1-to'];
        }
        if(empty($_POST['threshold-5-from']) || empty($_POST['threshold-4-from']) || empty($_POST['threshold-4-to']) || empty($_POST['threshold-3-from']) || empty($_POST['threshold-3-to']) || empty($_POST['threshold-2-from']) || empty($_POST['threshold-2-to']) || empty($_POST['threshold-1-to']))
        {
            $errors['threshold'] = 'Wpisz próg';
        }
        else if((intval($threshold_2_from) <= intval($threshold_1_to)) || (intval($threshold_2_to) < intval($threshold_2_from)) || (intval($threshold_3_from) <= intval($threshold_2_to)) || (intval($threshold_3_to) < intval($threshold_3_from)) || (intval($threshold_4_from) <= intval($threshold_3_to)) || (intval($threshold_4_to) < intval($threshold_4_from)) || (intval($threshold_5_from) <= intval($threshold_4_to)))
        {
            $errors['threshold'] = 'Niepoprawny próg';
        }
        if(empty($_POST['n-attempt']))
        {
            $errors['n_attempt'] = 'Wpisz ilość prób';
        }
        else
        {
            $n_attempt = $_POST['n-attempt'];
        }

        // jezeli nie ma bledow, zapisujemy dane w sesji i przekierowujemy do dalszego tworzenia testu
        if(!array_filter($errors))
        {
            $_SESSION['test_name'] = $test_name;
            $_SESSION['test_duration'] = intval($test_duration);
            $_SESSION['test_begin'] = $test_begin;
            $_SESSION['test_end'] = $test_end;
            $_SESSION['threshold'] = implode(',', ['0', $threshold_1_to, $threshold_2_from, $threshold_2_to, $threshold_3_from, $threshold_3_to, $threshold_4_from, $threshold_4_to, $threshold_5_from, '100']);
            $_SESSION['questions-mark'] = $_POST['single-choice-uncorrect-answer'] . $_POST['multiple-choice-unfull-answer'] . $_POST['multiple-choice-partial-answer'] . $_POST['multiple-choice-uncorrect-answer'];
            if(empty($_POST['random-questions']))
            {
                $_SESSION['random_questions'] = 0;
            }
            else
            {
                $_SESSION['random_questions'] = 1;
            }
            if(empty($_POST['random-answers']))
            {
                $_SESSION['random_answers'] = 0;
            }
            else
            {
                $_SESSION['random_answers'] = 1;
            }
            $_SESSION['n_attempt'] = intval($_POST['n-attempt']);
            $_SESSION['attempt'] = intval($_POST['attempt']);
            if(empty($_POST['go-back']))
            {
                $_SESSION['go_back'] = 0;
            }
            else
            {
                $_SESSION['go_back'] = 1;
            }
            $_SESSION['question_number'] = 1;

            header('Location: teacherNewTestQuestions.php');
        }
    }

    include_once('functionalities/headfoot/head.php');    
?>

    <div class="container mt-lg-5 pt-lg-5 mt-3 mb-5">
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

                <div class="container">
                    
                    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">

                        <!-- Nazwa testu -->
                        <div class="row justify-content-center">
                            <div class="container col-4">
                                <input type="text" class="form-control form-control-lg txt-center name"
                                    placeholder="Nazwa testu" name="test-name" value="<?php echo htmlspecialchars($test_name) ?>">
                                <div class="red-text"><?php echo $errors['test_name']; ?></div>
                            </div>
                        </div>
                        <br>    
                        
                        <div class="white-space container">

                            <!-- Ustawienia -->
                            <h4 class="row justify-content-center dark-blue-txt">
                                Ustawienia testu
                            </h4>
                            <br>

                            <div class="accordion" id="accordionPanelsStayOpenExample">

                                <!-- Czas -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                                        <button class="accordion-button grey-blue-back" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                            Czas
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                                        <div class="accordion-body">
                                            <label for="test-duration">Czas trwania testu:</label>
                                            <input type="number" id="test-duration" name="test-duration" min="1" max="1000" value="<?php echo $test_duration ?>">
                                            <label for="test-duration">minut</label>
                                            <div class="red-text"><?php echo $errors['test_duration']; ?></div><br><br>
                                            
                                            <label for="test-begin">Test aktywny od:</label>
                                            <input type="datetime-local" id="test-begin" name="test-begin" value="<?php echo $test_begin ?>">
                                            <div class="red-text"><?php echo $errors['test_begin']; ?></div><br><br>

                                            <label for="test-end">Test aktywny do:</label>
                                            <input type="datetime-local" id="test-end" name="test-end" value="<?php echo $test_end ?>">
                                            <div class="red-text"><?php echo $errors['test_end']; ?></div>
                                                
                                        </div>
                                    </div>
                                </div>

                                <!-- Prog -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                                        <button class="accordion-button grey-blue-back" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="true" aria-controls="panelsStayOpen-collapseTwo">
                                            Próg
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingTwo">
                                        <div class="accordion-body">

                                            <div class="row">
                                                <div class="col-1">Ocena</div>
                                                <div class="col-1">Od [%]</div>
                                                <div class="col-1">Do [%]</div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">5</div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-5-from" name="threshold-5-from" min="0" max="100" value="<?php echo $threshold_5_from ?>">
                                                </div>
                                                <div class="col-1">100</div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">4</div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-4-from" name="threshold-4-from" min="0" max="100" value="<?php echo $threshold_4_from ?>">
                                                </div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-4-to" name="threshold-4-to" min="0" max="100" value="<?php echo $threshold_4_to ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">3</div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-3-from" name="threshold-3-from" min="0" max="100" value="<?php echo $threshold_3_from ?>">
                                                </div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-3-to" name="threshold-3-to" min="0" max="100" value="<?php echo $threshold_3_to ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">2</div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-2-from" name="threshold-2-from" min="0" max="100" value="<?php echo $threshold_2_from ?>">
                                                </div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-2-to" name="threshold-2-to" min="0" max="100" value="<?php echo $threshold_2_to ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-1">1</div>
                                                <div class="col-1">0</div>
                                                <div class="col-1">
                                                    <input type="number" id="threshold-1-to" name="threshold-1-to" min="0" max="100" value="<?php echo $threshold_1_to ?>">
                                                </div>
                                            </div>

                                            <div class="red-text"><?php echo $errors['threshold']; ?></div>
                                            
                                        </div>
                                    </div>
                                </div>

                                <!-- Sposob oceniania pytan -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                                        <button class="accordion-button grey-blue-back" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="true" aria-controls="panelsStayOpen-collapseThree">
                                            Sposób oceniania pytań
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingThree">
                                        <div class="accordion-body">
                                            <div class="row justify-content-center grey-back">Pytania jednokrotnego wyboru</div>
                                            <div class="row">
                                                <label for="single-choice-uncorrect-answer">Niepoprawna odpowiedź:</label>
                                                <div class="col-xs-4">
                                                    <select name="single-choice-uncorrect-answer" id="single-choice-uncorrect-answer">
                                                        <option value="1">0 punktów</option>
                                                        <option value="2">ujemne punkty</option>
                                                    </select>
                                                </div>
                                            </div><br>

                                            <div class="row justify-content-center grey-back">Pytania wielokrotnego wyboru</div>
                                            <div class="row">
                                                <label for="multiple-choice-unfull-answer">Niepełna odpowiedź:</label>
                                                <div class="col-xs-4">
                                                    <select name="multiple-choice-unfull-answer" id="multiple-choice-unfull-answer">
                                                        <option value="1">cząstkowe punkty</option>
                                                        <option value="2">0 punktów</option>
                                                        <option value="3">ujemne punkty</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label for="multiple-choice-partial-answer">Częściowo poprawna odpowiedź:</label>
                                                <div class="col-xs-4">
                                                    <select name="multiple-choice-partial-answer" id="multiple-choice-partial-answer">
                                                        <option value="1">0 punktów</option>
                                                        <option value="2">bilans</option>
                                                        <option value="3">bilans nieujemny</option>
                                                        <option value="4">ujemne punkty</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label for="multiple-choice-uncorrect-answer">Niepoprawna odpowiedź:</label>
                                                <div class="col-xs-4">
                                                    <select name="multiple-choice-uncorrect-answer" id="multiple-choice-uncorrect-answer">
                                                        <option value="1">0 punktów</option>
                                                        <option value="2">ujemne punkty</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolejnosc pytan -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingFour">
                                        <button class="accordion-button grey-blue-back" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="true" aria-controls="panelsStayOpen-collapseFour">
                                            Kolejność pytań
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingFour">
                                        <div class="accordion-body">
                                            <input type="checkbox" id="random-questions" name="random-questions">
                                            <label for="random-questions"> Losowa kolejność pytań</label><br>

                                            <input type="checkbox" id="random-answers" name="random-answers">
                                            <label for="random-answers"> Losowa kolejność odpowiedzi</label><br>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ograniczenia -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="panelsStayOpen-headingFive">
                                        <button class="accordion-button grey-blue-back" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFive" aria-expanded="true" aria-controls="panelsStayOpen-collapseFive">
                                            Ograniczenia
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapseFive" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingFive">
                                        <div class="accordion-body">
                                            Ilość prób:
                                            <input type="number" id="n-attempt" name="n-attempt" min="1" max="1000" value="<?php echo $n_attempt ?>">
                                            <div class="red-text"><?php echo $errors['n_attempt']; ?></div><br>

                                            Sposób oceny prób:
                                            <select name="attempt" id="attempt">
                                                <option value="1">Najlepsza próba</option>
                                                <option value="2">Średnia z prób</option>
                                                <option value="3">Ostatnia próba</option>
                                            </select><br>

                                            <input type="checkbox" name="go-back" id="go-back">
                                            <label for="go-back">Możliwość powrotu do poprzednich pytań podczas testu</label>
                                        </div>
                                    </div>
                                </div>
                            
                            </div>
                        </div>
                    
                        <!-- Zatwierdz -->
                        <div class="text-center">
                            <button type="submit" class="submit-btn" name="submit">Zatwierdź</button>
                        </div>

                    </form>
                </div>

                <div class="mb-2">
                    <form action="teacherProfile.php">
                        <div class="text-center m-4">
                            <button type="submit" name="go_back" id="go-back" class="btn btn-primary">Powrót</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

<?php
    include_once('functionalities/headfoot/foot.php');
 ?>