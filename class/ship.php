<?php
/**
    This file contains the class Ship
    and its methods
 */
class ship
{
    // The attributes
    private int $id;
    private string $name;
    private string $camp;
    private float $speed_kmh;
    private int $capacity;

    // The constructor
    /**
     * @param $shipData
     */
    public function __construct($shipData)
    {
        $this->id = $shipData['id'];
        $this->name = $shipData['name'];
        $this->camp = $shipData['camp'];
        $this->speed_kmh = $shipData['speed_kmh'];
        $this->capacity = $shipData['capacity'];
    }

    // The method to insert in the database
    public function insert($cnx): void {
        // The sql request
        $sql = "INSERT INTO ship (id, name, camp, speed_kmh, capacity)
                VALUES (:id, :name, :camp, :speed_kmh, :capacity)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    camp = VALUES(camp),
                    speed_kmh = VALUES(speed_kmh),
                    capacity = VALUES(capacity)";

        // Prepare the request
        $stmt = $cnx->prepare($sql);

        // Bind the parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':camp', $this->camp);
        $stmt->bindParam(':speed_kmh', $this->speed_kmh);
        $stmt->bindParam(':capacity', $this->capacity);

        // Execute the request
        $stmt->execute();
    }

    public static function deleteAllShips($cnx): void {
        try {
            $sql = "DELETE FROM ship;";
            $stmt = $cnx->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}