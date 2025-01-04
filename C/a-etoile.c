#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <limits.h>

#define INFINITY LLONG_MAX // Distance infinie
#define MAX_PLANETS 6000 // Nombre maximum de planètes dans le graphe
#define MAX_TRIPS 128000 // Nombre maximum de voyages

// Structure représentant une arête entre deux planètes
typedef struct {
    long long destination; // Planète de destination
    double distance; // Distance vers cette planète
} Edge;

// Liste chaînée pour stocker les arêtes d'un sommet donné
typedef struct Node {
    Edge edge; // Arête sortante
    struct Node *next; // Pointeur vers l'arête suivante
} Node;

// Structure représentant le graphe entier sous forme de liste d'adjacence
typedef struct {
    Node *adjacency_list[MAX_PLANETS]; // Tableau de pointeurs vers les listes chaînées
} Graph;

// Crée un graphe vide
Graph* create_graph() {
    Graph *graph = (Graph *)malloc(sizeof(Graph));
    for (int i = 0; i < MAX_PLANETS; i++) {
        graph->adjacency_list[i] = NULL; // Initialisation des listes chaînées à NULL
    }
    return graph;
}

// Ajoute une arête dans le graphe
void add_edge(Graph *graph, long long source, long long destination, double distance) {
    Node *new_node = (Node *)malloc(sizeof(Node));
    new_node->edge.destination = destination; // Définir la destination
    new_node->edge.distance = distance; // Définir la distance
    new_node->next = graph->adjacency_list[source]; // Insérer en tête de liste
    graph->adjacency_list[source] = new_node; // Mettre à jour la tête de liste
}

// Lit le graphe à partir d'un fichier texte
Graph* read_graph(const char *filename) {
    FILE *file = fopen(filename, "r");
    if (!file) {
        return NULL; // Retourne NULL si le fichier ne peut pas être ouvert
    }

    Graph *graph = create_graph(); // Crée un graphe vide
    char line[256];
    while (fgets(line, sizeof(line), file)) {
        long long source, destination;
        double distance;
        sscanf(line, "%lld %lld %lf", &source, &destination, &distance); // Parse une ligne
        add_edge(graph, source, destination, distance); // Ajoute l'arête
    }

    fclose(file); // Ferme le fichier
    return graph;
}

// Écrit les résultats au format JSON dans un fichier
void write_json_to_file(const char *filename, int success, const char *error_message, long long *path, int path_length, double distance) {
    FILE *file = fopen(filename, "w");
    if (!file) {
        perror("Erreur lors de l'ouverture du fichier JSON");
        exit(EXIT_FAILURE);
    }

    fprintf(file, "{\n");
    fprintf(file, "  \"success\": %s,\n", success ? "true" : "false");

    if (success) {
        fprintf(file, "  \"distance\": %.2lf,\n", distance);
        fprintf(file, "  \"path\": [");
        for (int i = 0; i < path_length; i++) {
            fprintf(file, "%lld%s", path[i], (i == path_length - 1) ? "" : ", ");
        }
        fprintf(file, "]\n");
    } else {
        fprintf(file, "  \"error\": \"%s\"\n", error_message);
    }

    fprintf(file, "}\n");
    fclose(file); // Ferme le fichier
    printf("success: %s\n", success ? "true" : "false");
}

// Implémente l'algorithme de Dijkstra pour trouver le chemin le plus court
void dikjstra(Graph *graph, long long start, long long end) {
    double distances[MAX_PLANETS]; // Tableau des distances minimales
    long long previous[MAX_PLANETS]; // Tableau des prédécesseurs
    int visited[MAX_PLANETS] = {0}; // Indique si une planète a été visitée

    // Initialisation des tableaux
    for (int i = 0; i < MAX_PLANETS; i++) {
        distances[i] = INFINITY;
        previous[i] = -1;
    }

    distances[start] = 0.0; // La distance depuis le point de départ est 0

    for (int count = 0; count < MAX_PLANETS; count++) {
        long long current = -1;
        double min_distance = INFINITY;

        // Trouve le sommet non visité avec la plus petite distance
        for (int i = 0; i < MAX_PLANETS; i++) {
            if (!visited[i] && distances[i] < min_distance) {
                current = i;
                min_distance = distances[i];
            }
        }

        if (current == -1) break; // Tous les sommets accessibles ont été visités
        if (current == end) break; // Arrête si la destination est atteinte

        visited[current] = 1; // Marque le sommet comme visité

        // Parcourt tous les voisins du sommet courant
        Node *neighbor = graph->adjacency_list[current];
        while (neighbor) {
            long long dest = neighbor->edge.destination;
            double weight = neighbor->edge.distance;
            if (!visited[dest] && distances[current] + weight < distances[dest]) {
                distances[dest] = distances[current] + weight;
                previous[dest] = current; // Met à jour le prédécesseur
            }
            neighbor = neighbor->next; // Passe au voisin suivant
        }
    }

    if (distances[end] == INFINITY) {
        // Aucune route trouvée entre start et end
        write_json_to_file("output.json", 0, "Aucun chemin disponible entre les planètes spécifiées.", NULL, 0, 0.0);
    } else {
        // Reconstruit le chemin le plus court
        long long path[MAX_PLANETS];
        int path_length = 0;
        for (long long at = end; at != -1; at = previous[at]) {
            path[path_length++] = at;
        }

        // Inverse le chemin pour obtenir l'ordre correct
        long long reversed_path[MAX_PLANETS];
        for (int i = 0; i < path_length; i++) {
            reversed_path[i] = path[path_length - 1 - i];
        }

        write_json_to_file("output.json", 1, NULL, reversed_path, path_length, distances[end]);
    }
}

int main(int argc, char *argv[]) {
    // Vérifie que le nombre d'arguments est correct
    if (argc != 3) {
        fprintf(stderr, "Usage: %s <start_planet> <end_planet>\n", argv[0]);
        return EXIT_FAILURE;
    }

    // Convertit les arguments en entiers
    long long start = atoll(argv[1]);
    long long end = atoll(argv[2]);

    // Vérifie que les indices des planètes sont valides
    if (start < 1 || start > MAX_PLANETS) {
        write_json_to_file("output.json", 0, "Numéro de planète de départ invalide. Doit être entre 1 et 6000.", NULL, 0, 0.0);
        return EXIT_FAILURE;
    }

    if (end < 1 || end > MAX_PLANETS) {
        write_json_to_file("output.json", 0, "Numéro de planète d'arrivée invalide. Doit être entre 1 et 6000.", NULL, 0, 0.0);
        return EXIT_FAILURE;
    }

    // Lit le graphe à partir d'un fichier
    const char *filename = "graph.txt";
    Graph *graph = read_graph(filename);

    if (!graph) {
        write_json_to_file("output.json", 0, "Erreur lors de la lecture du fichier du graphe.", NULL, 0, 0.0);
        return EXIT_FAILURE;
    }

    // Exécute l'algorithme de Dijkstra
    dikjstra(graph, start, end);

    return 0;
}
