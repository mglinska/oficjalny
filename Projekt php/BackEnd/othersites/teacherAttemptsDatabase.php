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

    require_once "functionalities/connect.php";
    $conn = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($conn->connect_errno != 0)
    {
        echo "Error: ".$conn->connect_errno;
    }
    else
    {
        $id_nauczyciel = $_SESSION['id_nauczyciel'];
        $sql = "SELECT id_test FROM test WHERE id_nauczyciel=$id_nauczyciel";
        $result = mysqli_query($conn, $sql);
        $testy = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
            <div class="col-xl-10 mx-xl-3 student-exam-code-panel">
                <div class="container mt-3 mb-2 p-3 threshold-pool-table-box text-center">
                    <div class="table-responsive">
                        <table class="table table-spacing2">
                            <thead>
                                <tr class="table-head threshold-pool-table-head">
                                    <th class="col" scope="col">Login studenta</th>
                                    <th class="col" scope="col">Kod testu</th>
                                    <th class="col" scope="col">Ocena</th>
                                    <th class="col" scope="col">Zaliczony</th>
                                    <th class="col" scope="col">Data rozpoczęcia</th>
                                    <th class="col" scope="col">Data zakończenia</th>
                                    <th class="col" scope="col">Czas rozwiązania [min]</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 0; $i <= count($testy) - 1; $i++): ?>
                                    <?php $id_test = $testy[$i]['id_test']; ?>
                                    <?php $sql = "SELECT * FROM proba WHERE id_test=$id_test"; ?>
                                    <?php $result = mysqli_query($conn, $sql); ?>
                                    <?php $proby = mysqli_fetch_all($result, MYSQLI_ASSOC); ?>
                                    <?php for($j = 0; $j <= count($proby) - 1; $j++): ?>
                                        <?php $id_student = $proby[$j]['id_student']; ?>
                                        <?php $sql = "SELECT login_student FROM student WHERE id_student=$id_student"; ?>
                                        <?php $result = mysqli_query($conn, $sql); ?>
                                        <?php $login_student = mysqli_fetch_assoc($result)['login_student']; ?>
                                        <tr>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $login_student; ?></th>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $proby[$j]['id_test']; ?></th>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $proby[$j]['ocena']; ?></th>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $proby[$j]['zaliczony']; ?></th>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $proby[$j]['data_rozpoczecia']; ?></th>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $proby[$j]['data_zakonczenia']; ?></th>
                                            <th scope="row" class="threshold-pool-table-element"><?php echo $proby[$j]['czas_rozwiazania']; ?></th>
                                        </tr>
                                    <?php endfor; ?>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mb-2">
                    <form action="teacherArchivePanel.php">
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