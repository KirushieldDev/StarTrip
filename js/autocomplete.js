document.addEventListener('DOMContentLoaded', function() {
    function autocomplete(inp, arr, suggestionListId) {
        inp.addEventListener("input", function() {
            const val = this.value;
            const suggestionList = document.getElementById(suggestionListId);
            suggestionList.innerHTML = ""; // Réinitialise la liste des suggestions
            if (val.length < 3) {
                suggestionList.classList.remove("show");
                return;
            }
            arr.forEach(item => {
                if (item.toLowerCase().startsWith(val.toLowerCase())) {
                    const li = document.createElement("li");
                    li.className = "dropdown-item";
                    li.textContent = item;
                    li.addEventListener("click", function() {
                        inp.value = this.textContent;
                        suggestionList.classList.remove("show");
                    });
                    suggestionList.appendChild(li);
                }
            });
            suggestionList.classList.add("show");
        });

        inp.addEventListener("blur", function() {
            setTimeout(() => {
                document.getElementById(suggestionListId).classList.remove("show");
            }, 200);
        });
    }

    // Initialiser l'auto-complétion pour les champs de planète
    autocomplete(document.getElementById("departurePlanet"), planetNames, "departureSuggestions");
    autocomplete(document.getElementById("arrivalPlanet"), planetNames, "arrivalSuggestions");
});