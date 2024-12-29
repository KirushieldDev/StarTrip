const map = L.map('map', {
    crs: L.CRS.Simple,
    minZoom: 2,
    maxZoom: 6,
    attributionControl: false
});

const coordinates = mapData.allPlanets.map(planet => planet.coordinates);
const minLat = Math.min(...coordinates.map(coord => coord[0]));
const maxLat = Math.max(...coordinates.map(coord => coord[0]));
const minLng = Math.min(...coordinates.map(coord => coord[1]));
const maxLng = Math.max(...coordinates.map(coord => coord[1]));

const bounds = [[minLat - 50, minLng - 50], [maxLat + 50, maxLng + 50]];
map.fitBounds(bounds, { padding: [50, 50] });

document.getElementById('map').style.backgroundColor = 'black';

var regionColors = {
    'Deep Core': '#004d26',
    'Core': '#007a33',
    'Colonies': '#00a63a',
    'Expansion Region': '#33cc33',
    'Extragalactic': '#66e066',
    'Hutt Space': '#cc9900',
    'Inner Rim Territories': '#e6b800',
    'Mid Rim Territories': '#ffcc00',
    'Outer Rim Territories': '#ffe066',
    'Talcene Sector': '#8c4d00',
    'The Centrality': '#b37700',
    'Tingel Arm': '#d9a300',
    'Wild Space': '#f2d78c'
};

const departureCoordinates = mapData.departure.coordinates;
const arrivalCoordinates = mapData.arrival.coordinates;
const departureDiameter = mapData.departure.diameter;
const arrivalDiameter = mapData.arrival.diameter;

function getDiameterScale(diameter) {
    if (diameter === 0) return 0.1;
    if (diameter > 0 && diameter <= 50000) return 0.2;
    if (diameter > 50000 && diameter <= 100000) return 0.4;
    if (diameter > 100000 && diameter <= 150000) return 0.6;
    if (diameter > 150000 && diameter <= 200000) return 0.8;
    if (diameter > 200000) return 1.0;
}

function getDiameterPlanet(diameter) {
    return diameter / 10000 * 0.5;
}

mapData.allPlanets.forEach(planet => {
    const planetCoordinates = planet.coordinates;
    const planetRegion = planet.region;
    const planetColor = regionColors[planetRegion];
    const diameter = planet.diameter;
    const radius = getDiameterScale(diameter);

    L.circle(planetCoordinates, {
        color: planetColor,
        fillColor: planetColor,
        fillOpacity: 1,
        radius: radius
    }).addTo(map)
        .bindPopup(`
            <div style="text-align: center;">
                <b>Planet:</b> ${planet.name}<br>
                <b>Region:</b> ${planetRegion}<br>
                ${planet.imageHtml}
            </div>
        `);
});

const departureCircle = L.circle(departureCoordinates, {
    color: 'white',
    fillColor: 'white',
    fillOpacity: 1,
    radius: getDiameterPlanet(departureDiameter)
}).addTo(map)
    .bindPopup(`
        <div style="text-align: center;">
            <b>Departure Planet:</b> ${mapData.departure.name}<br>
            <b>Region:</b> ${mapData.departure.region}<br>
            ${mapData.departure.imageHtml}
        </div>
    `, { autoClose: false });

const arrivalCircle = L.circle(arrivalCoordinates, {
    color: 'white',
    fillColor: 'white',
    fillOpacity: 1,
    radius: getDiameterPlanet(arrivalDiameter)
}).addTo(map)
    .bindPopup(`
        <div style="text-align: center;">
            <b>Arrival Planet:</b> ${mapData.arrival.name}<br>
            <b>Region:</b> ${mapData.arrival.region}<br>
            ${mapData.arrival.imageHtml}
        </div>
    `, { autoClose: false });

departureCircle.openPopup();
arrivalCircle.openPopup();

L.polyline([departureCoordinates, arrivalCoordinates], {
    color: 'white',
    weight: 4
}).addTo(map);

const legend = L.control({ position: 'bottomright' });

legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'info legend');

    // Add the regions section in the legend
    let legendContent = '<h4>Regions</h4>';
    Object.keys(regionColors).forEach(region => {
        legendContent += `
            <i style="background:${regionColors[region]}"></i> ${region}<br>
        `;
    });

    // Add the diameters section in the legend
    legendContent += '<h4 style="margin-top: 15px;">Diameter</h4>';
    const diameters = [0, 50000, 100000, 150000, 200000, 250000];

    diameters.forEach(diameter => {
        const size = getDiameterPlanet(diameter);
        legendContent += `
            <div style="margin: 5px 0; display: flex; align-items: center;">
                <i style="
                    background: black; 
                    width: ${size}px; 
                    height: ${size}px; 
                    border-radius: 50%; 
                    display: inline-block;
                    margin-right: 8px;
                "></i>
                ${diameter.toLocaleString()}
            </div>
        `;
    });

    div.innerHTML = legendContent;
    return div;
};

legend.addTo(map);
