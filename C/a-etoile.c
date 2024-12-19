#include <stdio.h>
#include <stdlib.h>
#include <math.h>

// Structure pour représenter une planète
typedef struct {
    int id;         // Identifiant unique de la planète
    float x, y;     // Coordonnées de la planète
} Planet;

// Structure pour représenter un trajet
typedef struct {
    int planet_id;            // Planète de départ
    int destination_planet_id; // Planète d'arrivée
    float distance;           // Distance entre les planètes
    int ship_id;              // ID du vaisseau utilisé
} Trip;

// Structure pour représenter un vaisseau
typedef struct {
    int id;            // Identifiant unique du vaisseau
    float speed_kmh;   // Vitesse en km/h
} Ship;

// Structure pour le nœud de l'algorithme A*
typedef struct Node {
    int planet_id;          // ID de la planète
    float g_cost;           // Coût réel pour atteindre ce nœud
    float h_cost;           // Heuristique (distance estimée jusqu'à la destination)
    float f_cost;           // g_cost + h_cost
    struct Node *next;      // Pointeur vers le nœud suivant dans la liste
    struct Node *parent;    // Pointeur vers le parent
} Node;

// Fonction pour calculer la distance entre deux planètes
float calculate_distance(float x1, float y1, float x2, float y2) {
    return sqrt(pow(x2 - x1, 2) + pow(y2 - y1, 2));
}

// Fonction heuristique pour A* (distance directe entre deux planètes)
float heuristic(Planet start, Planet goal) {
    return calculate_distance(start.x, start.y, goal.x, goal.y);
}

// Fonction pour ajouter un nœud à une liste
void add_to_list(Node **list, Node *node) {
    node->next = *list;
    *list = node;
}

// Fonction pour trouver un chemin avec A*
Node *a_star(Planet *planets, Trip *trips, int num_trips, int start_id, int goal_id) {
    Node *open_list = NULL;
    Node *closed_list = NULL;

    // Ajouter la planète de départ à l'open list
    Node *start_node = malloc(sizeof(Node));
    start_node->planet_id = start_id;
    start_node->g_cost = 0;
    start_node->h_cost = heuristic(planets[start_id], planets[goal_id]);
    start_node->f_cost = start_node->g_cost + start_node->h_cost;
    start_node->parent = NULL;
    start_node->next = NULL;
    add_to_list(&open_list, start_node);

    while (open_list != NULL) {
        // Trouver le nœud avec le plus petit f_cost dans l'open list
        Node *current = open_list;
        Node *prev = NULL, *min_prev = NULL;
        Node *min_node = current;
        float min_f_cost = current->f_cost;

        while (current != NULL) {
            if (current->f_cost < min_f_cost) {
                min_f_cost = current->f_cost;
                min_node = current;
                min_prev = prev;
            }
            prev = current;
            current = current->next;
        }

        // Si la planète courante est la destination, retourner le chemin
        if (min_node->planet_id == goal_id) {
            return min_node;
        }

        // Retirer le nœud courant de l'open list et l'ajouter à la closed list
        if (min_prev) {
            min_prev->next = min_node->next;
        } else {
            open_list = min_node->next;
        }
        add_to_list(&closed_list, min_node);

        // Examiner tous les voisins de la planète courante
        for (int i = 0; i < num_trips; i++) {
            if (trips[i].planet_id == min_node->planet_id) {
                int neighbor_id = trips[i].destination_planet_id;

                // Vérifier si le voisin est déjà dans la closed list
                Node *closed = closed_list;
                int skip = 0;
                while (closed != NULL) {
                    if (closed->planet_id == neighbor_id) {
                        skip = 1;
                        break;
                    }
                    closed = closed->next;
                }
                if (skip) continue;

                // Calculer les coûts
                float g_cost = min_node->g_cost + trips[i].distance;
                float h_cost = heuristic(planets[neighbor_id], planets[goal_id]);
                float f_cost = g_cost + h_cost;

                // Vérifier si le voisin est déjà dans l'open list
                Node *open = open_list;
                while (open != NULL) {
                    if (open->planet_id == neighbor_id && f_cost >= open->f_cost) {
                        skip = 1;
                        break;
                    }
                    open = open->next;
                }
                if (skip) continue;

                // Ajouter le voisin à l'open list
                Node *neighbor = malloc(sizeof(Node));
                neighbor->planet_id = neighbor_id;
                neighbor->g_cost = g_cost;
                neighbor->h_cost = h_cost;
                neighbor->f_cost = f_cost;
                neighbor->parent = min_node;
                neighbor->next = NULL;
                add_to_list(&open_list, neighbor);
            }
        }
    }

    return NULL; // Aucun chemin trouvé
}

// Fonction pour afficher le chemin
void print_path(Node *node) {
    if (node == NULL) return;
    print_path(node->parent);
    printf("Planete ID %d -> ", node->planet_id);
}

// Exemple principal
int main() {
    // Initialisation des données
    Planet planets[] = {
        {0, 0, 2},
        {1, 2, 2},
        {2, 4, 1},
        {3, 5, 1}
    };

    Trip trips[] = {
        {0, 1, 1, 1},
        {0, 3, 3, 1},
        {1, 2, 2, 1},
        {1, 3, 1, 1},
        {2, 1, 5, 1},
        {3, 2, 4, 1}
    };

    Node *path = a_star(planets, trips, 6, 2, 3); // Correction du nombre de trajets

    if (path) {
        printf("Chemin trouve : ");
        print_path(path);
        printf("Arrivee.\n");
    } else {
        printf("Aucun chemin trouve.\n");
    }

    return 0;
}
