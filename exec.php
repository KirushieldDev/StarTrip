<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departurePlanet = trim($_POST['departurePlanetId'] ?? '');
    $arrivalPlanet = trim($_POST['arrivalPlanetId'] ?? '');
    $legion = trim($_POST['legion'] ?? '');

    //exec('java -jar target/java-1.0-SNAPSHOT.jar ' . $legion);

    $exePath = 'C:\\Users\\alexi\\BUT2\\StarTrip\\a-etoile.exe';
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
        </form>
        <script>document.getElementById('redirectForm').submit();</script>";
}


