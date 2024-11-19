// Initialiser la carte avec une projection simple (cartes locales)
var map = L.map('map', {
    crs: L.CRS.Simple,  // Projection simple, sans latitude/longitude
    minZoom: 0,  // Zoom minimum
    maxZoom: 10  // Zoom maximum
});

// Définir les limites de la carte selon la taille de vos données
var bounds = [[0, 0], [130, 130]];  // Ajustez en fonction de vos données
map.fitBounds(bounds);

// Fond noir (ajustez si nécessaire)
L.rectangle(bounds, { color: "#000000", weight: 1, fillOpacity: 1, fillColor: "#000000" }).addTo(map);

var regionColors = {
    'Outer Rim Territories': 'blue',
    'Core': 'red',
    'Extragalactic': 'purple',
    'Mid Rim Territories': 'green',
    'Inner Rim Territories': 'orange',
    'Expansion Region': 'yellow',
    'Deep Core': 'cyan',
    'Wild Space': 'brown',
    'Colonies': 'pink',
    'Hutt Space': 'gold',
    'Tingel Arm': 'teal',
    'Talcene Sector': 'lightblue',
    'The Centrality': 'darkgreen'
};

var defaultColor = 'gray';

// Boucler à travers chaque planète pour ajouter des cercles
planets.forEach(function(planet) {
    var region = planet.Region;
    var name = planet.Name;
    var X = parseFloat(planet.X);  // Assurer que X est un nombre à virgule flottante
    var Y = parseFloat(planet.Y);  // Assurer que Y est un nombre à virgule flottante
    var SubGridX = parseFloat(planet.SubGridX || 0);  // Assurer que SubGridX est un nombre à virgule flottante
    var SubGridY = parseFloat(planet.SubGridY || 0);  // Assurer que SubGridY est un nombre à virgule flottante
    var color = regionColors[region] || defaultColor;
    // Vérifier les coordonnées dans la console
    console.log(name + " : X = " + X + ", Y = " + Y + ", SubGridX = " + SubGridX + ", SubGridY = " + SubGridY);

    // Calculer les positions en X et Y avec un facteur d'échelle
    var x = (X + SubGridX) * 6;
    var y = (Y + SubGridY) * 6;

    console.log(`Planet: ${name}, Coordinates: X=${x}, Y=${y}`);

    // Vérifier si les coordonnées calculées sont valides
    if (!isNaN(x) && !isNaN(y)) {
        // Créer un cercle autour de la planète
        var circle = L.circle([y, x], {
            radius: 0.1,  // Le rayon du cercle
            color: color,  // Couleur du cercle
            fillColor: color,  // Couleur de remplissage du cercle
            fillOpacity: 0.5  // Opacité du remplissage
        }).bindPopup("<b>" + name + "</b>").addTo(map);
    } else {
        console.error("Coordonnées invalides pour la planète " + name);
    }
});
