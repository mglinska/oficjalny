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
                                    <th class="col" scope="col">Status</th>
                                    <th class="col" scope="col">Data rozpoczęcia</th>
                                    <th class="col" scope="col">Data zakończenia</th>
                                    <th class="col" scope="col">Ocena</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                if(isset($_SESSION['proby']))
                                {
                                    foreach($_SESSION['proby'] as $proba)
                                    {
                                        echo '<tr>
                                    <th scope="row" class="threshold-pool-table-element">'.$proba[0].'</th>
                                    <td class="threshold-pool-table-element">'.$proba[1].'</td>
                                    <td class="threshold-pool-table-element">'.$proba[2].'</td>
                                    <td class="threshold-pool-table-element">'.$proba[3].'</td>
                                    <td class="threshold-pool-table-element">'.$proba[4].'</td>
                                    <td class="threshold-pool-table-element">'.$proba[5].'</td>
                                    <td class="threshold-pool-table-element">'.$proba[6].'</td>
                                    <td class="threshold-pool-table-element">'.$proba[7].'</td>
                                </tr>
                                ';
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                    if(isset($_SESSION['blad']))	echo $_SESSION['blad'];
                ?>
                <div class="mb-2">
                    <form action="functionalities/menuGoBack.php">
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