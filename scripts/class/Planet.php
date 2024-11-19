<?php
namespace class;

class Planet {
public $Id;
public $Name;
public $Image;
public $Coord;
public $X;
public $Y;
public $SubGridCoord;
public $SubGridX;
public $SubGridY;
public $Region;
public $Sector;
public $Suns;
public $Moons;
public $Position;
public $Distance;
public $LengthDay;
public $LengthYear;
public $Diameter;
public $Gravity;

public function __construct(array $data) {
$this->Id = $data['Id'] ?? null;
$this->Name = $data['Name'] ?? null;
$this->Image = $data['Image'] ?? null; // Assurez-vous d'avoir une valeur par défaut
$this->Coord = $data['Coord'] ?? null;
$this->X = $data['X'] ?? null;
$this->Y = $data['Y'] ?? null;
$this->SubGridCoord = $data['SubGridCoord'] ?? null;
$this->SubGridX = $data['SubGridX'] ?? null;
$this->SubGridY = $data['SubGridY'] ?? null;
$this->Region = $data['Region'] ?? null;
$this->Sector = $data['Sector'] ?? null;
$this->Suns = $data['Suns'] ?? null;
$this->Moons = $data['Moons'] ?? null;
$this->Position = $data['Position'] ?? null;
$this->Distance = $data['Distance'] ?? null;
$this->LengthDay = $data['LengthDay'] ?? null;
$this->LengthYear = $data['LengthYear'] ?? null;
$this->Diameter = $data['Diameter'] ?? null;
$this->Gravity = $data['Gravity'] ?? null; // Assurez-vous d'avoir une valeur par défaut
}
}
