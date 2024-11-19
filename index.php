<?php
include "config.php"; // Inclusion de la configuration de la base de données
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Trivia</title>
</head>
<body>
<?php include "header.php"; ?>
<section>
    <aside>
        <div class="containeur">
            <form method="POST" id="planet-search-form" action="resultat.php">
                <div class="containeur">
                    <label for="planet_search">Rechercher une planète (Départ) :</label>
                    <input type="text" id="planet_search" onkeyup="searchPlanets('planet_search')" name="planet_search" required autocomplete="off">
                    <div id="planet_suggestions_1"></div>
                </div>
                <br><br>
                <div class="containeur">
                    <label for="planet_search2">Rechercher une planète (Arrivée) :</label>
                    <input type="text" id="planet_search2" onkeyup="searchPlanets('planet_search2')" name="planet_search2" required autocomplete="off">
                    <div id="planet_suggestions_2"></div>
                </div>
                <br><br>
                <button type="submit">Rechercher</button>
            </form>
            <script>
                function searchPlanets(inputId) {
                    const input = document.getElementById(inputId).value;
                    const suggestionsDiv = inputId === 'planet_search' ?
                        document.getElementById('planet_suggestions_1') :
                        document.getElementById('planet_suggestions_2');

                    // Effacer les suggestions précédentes
                    suggestionsDiv.innerHTML = '';
                    suggestionsDiv.style.display = 'none';

                    if (input.length >= 3) {
                        fetch(`search_planets.php?query=${input}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    data.forEach(planet => {
                                        const option = document.createElement('div');
                                        option.textContent = planet.Name; // Afficher le nom de la planète
                                        option.setAttribute('data-id', planet.Id); // Ajouter l'ID de la planète
                                        option.classList.add('suggestion-item');
                                        option.onclick = function() {
                                            selectPlanet(inputId, planet.Id, planet.Name);
                                        };
                                        suggestionsDiv.appendChild(option);
                                    });
                                    suggestionsDiv.style.display = 'block'; // Afficher les suggestions
                                } else {
                                    const noResult = document.createElement('div');
                                    noResult.textContent = 'Aucune planète trouvée.';
                                    suggestionsDiv.appendChild(noResult);
                                    suggestionsDiv.style.display = 'block';
                                }
                            })
                            .catch(error => {
                                console.error('Erreur lors de la recherche des planètes:', error);
                            });
                    }
                }

                function selectPlanet(inputId, id, name) {
                    document.getElementById(inputId).value = name; // Remplir le champ de texte avec le nom de la planète
                    const suggestionsDiv = inputId === 'planet_search' ?
                        document.getElementById('planet_suggestions_1') :
                        document.getElementById('planet_suggestions_2');
                    suggestionsDiv.style.display = 'none'; // Cacher les suggestions

                    // Log dans la trace
                    console.log(`Recherche effectuée pour la planète : ${name} (ID: ${id})`);
                }
            </script>
        </div>
    </aside>
</section>
</body>
</html>
