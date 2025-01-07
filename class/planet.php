<?php
/**
 * This file contains the class Planet
 * and its methods
 */

class planet
{
    // Creating a list to save all the instances of this class
    public static array $planetsList = [];

    // The attributes
    private int $id;
    private string $name;
    private ?string $image;
    private ?string $coord;
    private float $x;
    private float $y;
    private ?string $subGridCoord;
    private ?float $subGridX;
    private ?float $subGridY;
    private ?string $sunName;
    private string $region;
    private string $sector;
    private int $suns;
    private int $moons;
    private int $position;
    private float $distance;
    private float $lengthDay;
    private float $lengthYear;
    private float $diameter;
    private float $gravity;

    // The constructor

    /**
     * @param $planetData
     */
    public function __construct($planetData)
    {
        $this->id = $planetData->Id;
        $this->name = $planetData->Name;
        $this->image = $planetData->Image ?? null;
        $this->coord = $planetData->Coord ?? null;
        $this->x = $planetData->X;
        $this->y = $planetData->Y;
        $this->subGridCoord = $planetData->SubGridCoord ?? null;
        $this->subGridX = $planetData->SubGridX ?? null;
        $this->subGridY = $planetData->SubGridY ?? null;
        $this->sunName = $planetData->SunName ?? null;
        $this->region = $planetData->Region;
        $this->sector = $planetData->Sector;
        $this->suns = $planetData->Suns;
        $this->moons = $planetData->Moons;
        $this->position = $planetData->Position;
        $this->distance = $planetData->Distance;
        $this->lengthDay = $planetData->LengthDay;
        $this->lengthYear = $planetData->LengthYear;
        $this->diameter = $planetData->Diameter;
        $this->gravity = $planetData->Gravity;
    }

    // The method to insert in the database
    public function insert($cnx): void
    {
        $this->image = str_replace(' ', '_', $this->image);
        // The sql request
        $sql = "INSERT INTO planet
                (name, image, coord, x, y, sub_grid_coord, sub_grid_x, sub_grid_y, sun_name, region, sector, suns, moons, position, distance, length_day, length_year, diameter, gravity) 
                VALUES (:name, :image, :coord, :x, :y, :subGridCoord, :subGridX, :subGridY, :sunName, :region, :sector, :suns, :moons, :position, :distance, :lengthDay, :lengthYear, :diameter, :gravity)
                ON DUPLICATE KEY UPDATE 
                image = VALUES(image), coord = VALUES(coord), x = VALUES(x), y = VALUES(y), sub_grid_coord = VALUES(sub_grid_coord),
                sub_grid_x = VALUES(sub_grid_x), sub_grid_y = VALUES(sub_grid_y), sun_name = VALUES(sun_name), region = VALUES(region), 
                sector = VALUES(sector), suns = VALUES(suns), moons = VALUES(moons), position = VALUES(position), distance = VALUES(distance), 
                length_day = VALUES(length_day), length_year = VALUES(length_year), diameter = VALUES(diameter), gravity = VALUES(gravity)";

        // Prepare the request
        $stmt = $cnx->prepare($sql);

        // Bind the parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':coord', $this->coord);
        $stmt->bindParam(':x', $this->x);
        $stmt->bindParam(':y', $this->y);
        $stmt->bindParam(':subGridCoord', $this->subGridCoord);
        $stmt->bindParam(':subGridX', $this->subGridX);
        $stmt->bindParam(':subGridY', $this->subGridY);
        $stmt->bindParam(':sunName', $this->sunName);
        $stmt->bindParam(':region', $this->region);
        $stmt->bindParam(':sector', $this->sector);
        $stmt->bindParam(':suns', $this->suns);
        $stmt->bindParam(':moons', $this->moons);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':distance', $this->distance);
        $stmt->bindParam(':lengthDay', $this->lengthDay);
        $stmt->bindParam(':lengthYear', $this->lengthYear);
        $stmt->bindParam(':diameter', $this->diameter);
        $stmt->bindParam(':gravity', $this->gravity);

        // Execute the request
        $stmt->execute();
    }

    public static function getPlanetById($id, $cnx) {
        $stmt = $cnx->prepare("SELECT * FROM planet WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function calculatePosition($x, $subGridX, $y, $subGridY) {
        $positionX = ($x + $subGridX) * 6;
        $positionY = ($y + $subGridY) * 6;
        return [$positionX, $positionY];
    }

    public static function deleteAllPlanets($cnx): void
    {
        try {
            $sql = "DELETE FROM planet";
            $stmt = $cnx->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public static function insertTrip($cnx, $day, $trip, $departurePlanetId): void
    {
        $sql = "INSERT INTO trip (day_of_week, departure_time, destination_planet_id, ship_id, planet_id)
                VALUES (:day_of_week, :departure_time, :destination_planet_id, :ship_id, :planet_id)
                ON DUPLICATE KEY UPDATE
                    day_of_week = VALUES(day_of_week), 
                    departure_time = VALUES(departure_time), 
                    destination_planet_id = VALUES(destination_planet_id), 
                    ship_id = VALUES(ship_id), 
                    planet_id = VALUES(planet_id)";

        $stmt = $cnx->prepare($sql);

        $stmt->bindParam(":day_of_week", $day);
        $stmt->bindParam(":departure_time", $trip->departure_time[0]);
        $stmt->bindParam(":destination_planet_id", $trip->destination_planet_id[0]);
        $stmt->bindParam(":ship_id", $trip->ship_id[0]);
        $stmt->bindParam(":planet_id", $departurePlanetId);

        $stmt->execute();
    }

    public static function deleteAllTrips($cnx): void
    {
        try {
            $sql = "DELETE FROM trip";
            $stmt = $cnx->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public static function getHtmlImage(int $planetId): string
    {
        // Récupérer les données de la planète via l'ID
        global $cnx; // Assurez-vous que vous avez bien la connexion à la base de données
        $stmt = $cnx->prepare("SELECT image, name FROM planet WHERE id = :id");
        $stmt->bindParam(':id', $planetId);
        $stmt->execute();
        $planet = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($planet && !empty($planet['name'])) {
            // Si la planète existe, générer l'URL de l'image
            $imageName = str_replace(' ', '_', $planet['image']);
            $imageMd5 = md5($imageName);
            $url = "https://static.wikia.nocookie.net/starwars/images/" . $imageMd5[0] . "/" . substr($imageMd5, 0, 2) . "/" . $imageName;

            return "<img src='$url' alt='Image of {$planet['name']}' class='planet-img'/>";
        }

        return "No image available"; // Si la planète n'existe pas
    }


    public function display(): void
    {
        $image = $this->getHtmlImage();

        echo "
        <div class='planet-details'>
            <h2>" . htmlspecialchars($this->name) . "</h2>
            $image
            <ul>
                <li><strong>Region:</strong> " . htmlspecialchars($this->region) . "</li>
                <li><strong>Sector:</strong> " . htmlspecialchars($this->sector) . "</li>
                <li><strong>Coordinates:</strong> " . htmlspecialchars($this->coord) . " (" . htmlspecialchars($this->x) . ", " . htmlspecialchars($this->y) . ")</li>
                <li><strong>Sun Name:</strong> " . htmlspecialchars($this->sunName ?? 'Unknown') . "</li>
                <li><strong>Suns:</strong> " . htmlspecialchars($this->suns) . "</li>
                <li><strong>Moons:</strong> " . htmlspecialchars($this->moons) . "</li>
                <li><strong>Orbital Position:</strong> " . htmlspecialchars($this->position) . "</li>
                <li><strong>Distance from Core:</strong> " . htmlspecialchars($this->distance) . " light years</li>
                <li><strong>Day Length:</strong> " . htmlspecialchars($this->lengthDay) . " hours</li>
                <li><strong>Year Length:</strong> " . htmlspecialchars($this->lengthYear) . " days</li>
                <li><strong>Diameter:</strong> " . htmlspecialchars($this->diameter) . " km</li>
                <li><strong>Gravity:</strong> " . htmlspecialchars($this->gravity) . "</li>
            </ul>
        </div>";
    }

    public static function displayAll($cnx): void
    {
        // Display all the planets from the list of planets
        foreach (self::$planetsList as $planet) {
            $planet->display();
        }
    }
}