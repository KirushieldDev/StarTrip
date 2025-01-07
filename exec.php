<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcul en cours...</title>
    <style>
        /* CSS pour centrer le GIF */
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: white;
        }
        #loader img {
            width: 30vw; /* Taille du GIF */
            height: auto;
        }
    </style>
</head>
<body>
<div id="loader">
    <img src="assets/loading.gif" alt="Chargement en cours..." />
</div>
</body>
</html>



<?php
sleep(2);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: src/index.php");
    exit();
} else {
    $departurePlanet = trim($_POST['departurePlanetId'] ?? '');
    $arrivalPlanet = trim($_POST['arrivalPlanetId'] ?? '');
    $legion = trim($_POST['legion'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');
    $timePreference = $_POST['timePreference'] ?? null;
    $selectedTime = $_POST['selectedTime'] ?? null;

    $InProd = false;
    if($InProd){
        exec("java -Dfile.encoding=UTF-8 -jar java/target/java-1.0-SNAPSHOT.jar " . $legion . " " . $capacity . " 2>&1", $output, $returnCode);
    }else{
        exec('java -jar java/target/java-1.0-SNAPSHOT.jar ' . $legion . ' ' . $capacity);
    }

    //Mettre votre chemin vers a-etoile.exe
    $exePath = 'C:\Users\alexi\BUT2\StarTrip\a-etoile.exe';
    $output = [];
    $returnVar = 0;
    exec("$exePath $departurePlanet $arrivalPlanet", $output, $returnVar);

    echo "
        <form id='redirectForm' action='src/result.php' method='POST'>
            <input type='hidden' name='departurePlanetId' value='$departurePlanet'>
            <input type='hidden' name='arrivalPlanetId' value='$arrivalPlanet'>
            <input type='hidden' name='departurePlanet' value='" . htmlspecialchars($departurePlanet) . "'>
            <input type='hidden' name='arrivalPlanet' value='" . htmlspecialchars($arrivalPlanet) . "'>
            <input type='hidden' name='legion'  value='" . htmlspecialchars($legion) . "'>
            <input type='hidden' name='capacity'  value='" . htmlspecialchars($capacity) . "'>
            <input type='hidden' name='timePreference'  value='" . htmlspecialchars($timePreference) . "'>
            <input type='hidden' name='selectedTime'  value='" . htmlspecialchars($selectedTime) . "'>
        </form>
        <script>document.getElementById('redirectForm').submit();</script>";
}
exit;


