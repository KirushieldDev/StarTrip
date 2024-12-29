#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <limits.h>

#define INFINITY LLONG_MAX
#define MAX_PLANETS 6000
#define MAX_TRIPS 128000

typedef struct {
    long long destination;
    double distance;
} Edge;

typedef struct Node {
    Edge edge;
    struct Node *next;
} Node;

typedef struct {
    Node *adjacency_list[MAX_PLANETS];
} Graph;

// File de priorité pour le Dijkstra
typedef struct {
    long long planet;
    double distance;
} PriorityQueueNode;

Graph* create_graph() {
    Graph *graph = (Graph *)malloc(sizeof(Graph));
    for (int i = 0; i < MAX_PLANETS; i++) {
        graph->adjacency_list[i] = NULL;
    }
    return graph;
}

void add_edge(Graph *graph, long long source, long long destination, double distance) {
    Node *new_node = (Node *)malloc(sizeof(Node));
    new_node->edge.destination = destination;
    new_node->edge.distance = distance;
    new_node->next = graph->adjacency_list[source];
    graph->adjacency_list[source] = new_node;
}

// Fonction pour lire le fichier et construire le graphe
Graph* read_graph(const char *filename) {
    FILE *file = fopen(filename, "r");
    if (!file) {
        perror("Erreur d'ouverture du fichier");
        exit(EXIT_FAILURE);
    }

    Graph *graph = create_graph();
    char line[256];
    while (fgets(line, sizeof(line), file)) {
        long long source, destination;
        double distance;
        sscanf(line, "%lld %lld %lf", &source, &destination, &distance);
        add_edge(graph, source, destination, distance);
    }

    fclose(file);
    return graph;
}

// Fonction pathfinding avec Dikjstra pour trouver le chemin le plus court
void dikjstra(Graph *graph, long long start, long long end) {
    double distances[MAX_PLANETS];
    long long previous[MAX_PLANETS];
    int visited[MAX_PLANETS] = {0};

    for (int i = 0; i < MAX_PLANETS; i++) {
        distances[i] = INFINITY;
        previous[i] = -1;
    }

    distances[start] = 0.0;

    for (int count = 0; count < MAX_PLANETS; count++) {
        // Trouver la planète non visitée avec la distance minimale
        long long current = -1;
        double min_distance = INFINITY;
        for (int i = 0; i < MAX_PLANETS; i++) {
            if (!visited[i] && distances[i] < min_distance) {
                current = i;
                min_distance = distances[i];
            }
        }

        if (current == -1) break; // Aucun noeud accessible
        if (current == end) break; // Chemin trouvé

        visited[current] = 1;

        // Mettre à jour les distances des voisins
        Node *neighbor = graph->adjacency_list[current];
        while (neighbor) {
            long long dest = neighbor->edge.destination;
            double weight = neighbor->edge.distance;
            if (!visited[dest] && distances[current] + weight < distances[dest]) {
                distances[dest] = distances[current] + weight;
                previous[dest] = current;
            }
            neighbor = neighbor->next;
        }
    }

    // Reconstruire le chemin
    if (distances[end] == INFINITY) {
        printf("Aucun chemin disponible entre %lld et %lld\n", start, end);
    } else {
        printf("Distance minimale : %.2lf\n", distances[end]);
        printf("Chemin : ");
        long long path[MAX_PLANETS];
        int path_length = 0;
        for (long long at = end; at != -1; at = previous[at]) {
            path[path_length++] = at;
        }
        for (int i = path_length - 1; i >= 0; i--) {
            printf("%lld%s", path[i], (i == 0) ? "\n" : " -> ");
        }
    }
}

int main() {
    const char *filename = "graph.txt";
    Graph *graph = read_graph(filename);

    long long start, end;
    printf("Entrez la planete de depart : ");
    scanf("%lld", &start);
    printf("Entrez la planete d'arrivee : ");
    scanf("%lld", &end);

    dikjstra(graph, start, end);

    return 0;
}
