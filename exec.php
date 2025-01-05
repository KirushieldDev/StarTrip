<?php
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
    //exec('java -jar target/java-1.0-SNAPSHOT.jar ' . $legion . ' ' . $capacity);

    //Mettrz votre chemin vers a-etoile.exe
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


