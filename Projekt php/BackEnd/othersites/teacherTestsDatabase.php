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
        $sql = "SELECT * FROM test WHERE id_nauczyciel=$id_nauczyciel";
        $result = mysqli_query($conn, $sql);
        $testy = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $statusy = [];
        for($i = 0; $i <= count($testy) - 1; $i++)
        {
            $teraz = date('Y-m-d H:i:s');
            $aktywny_od = $testy[$i]['aktywny_od'];
            $aktywny_do = $testy[$i]['aktywny_do'];
            if(($teraz >= $aktywny_od) && ($teraz <= $aktywny_do))
            {
                $status = "aktywny";
            }
            else
            {
                $status = "nieaktywny";
            }
            $statusy[] = $status;
        }

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
                                    <th class="col" scope="col">Kod</th>
                                    <th class="col" scope="col">Nazwa</th>
                                    <th class="col" scope="col">Aktywny od</th>
                                    <th class="col" scope="col">Aktywny do</th>
                                    <th class="col" scope="col">Ilość czasu [min]</th>
                                    <th class="col" scope="col">Status</th>
                                    <th class="col" scope="col">Maksymalna ilość prób</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 0; $i <= count($testy) - 1; $i++): ?>
                                    <tr>
                                        <th scope="row" class="threshold-pool-table-element"><?php echo $testy[$i]['id_test']; ?></th>
                                        <td class="threshold-pool-table-element"><?php echo $testy[$i]['nazwa']; ?></td>
                                        <td class="threshold-pool-table-element"><?php echo $testy[$i]['aktywny_od']; ?></td>
                                        <td class="threshold-pool-table-element"><?php echo $testy[$i]['aktywny_do']; ?></td>
                                        <td class="threshold-pool-table-element"><?php echo $testy[$i]['ilosc_czasu']; ?></td>
                                        <td class="threshold-pool-table-element"><?php echo $statusy[$i]; ?></td>
                                        <td class="threshold-pool-table-element"><?php echo $testy[$i]['maksymalna_ilosc_prob']; ?></td>
                                    </tr>
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