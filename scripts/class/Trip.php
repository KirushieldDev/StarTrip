<?php
namespace class;

class Trip
{
    public $PlanetId;
    public $DestinationPlanetId;
    public $DepartureTime;
    public $ShipId;
    public $DayType;

    public function __construct($planetId, $tripData, $dayType)
    {
        $this->PlanetId = $planetId;
        $this->DestinationPlanetId = $tripData['destination_planet_id'][0]; // Supposons qu'il y ait un seul ID
        $this->DepartureTime = $tripData['departure_time'][0]; // Supposons qu'il y ait une seule heure
        $this->ShipId = $tripData['ship_id'][0]; // Supposons qu'il y ait un seul ID de vaisseau
        $this->DayType = $dayType;
    }
}
