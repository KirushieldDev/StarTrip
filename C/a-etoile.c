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

Graph* read_graph(const char *filename) {
    FILE *file = fopen(filename, "r");
    if (!file) {
        return NULL;
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
    fclose(file);
    printf("success: %s\n", success ? "true" : "false");
}

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
        long long current = -1;
        double min_distance = INFINITY;
        for (int i = 0; i < MAX_PLANETS; i++) {
            if (!visited[i] && distances[i] < min_distance) {
                current = i;
                min_distance = distances[i];
            }
        }

        if (current == -1) break;
        if (current == end) break;

        visited[current] = 1;

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

    if (distances[end] == INFINITY) {
        write_json_to_file("output.json", 0, "Aucun chemin disponible entre les planètes spécifiées.", NULL, 0, 0.0);
    } else {
        long long path[MAX_PLANETS];
        int path_length = 0;
        for (long long at = end; at != -1; at = previous[at]) {
            path[path_length++] = at;
        }

        long long reversed_path[MAX_PLANETS];
        for (int i = 0; i < path_length; i++) {
            reversed_path[i] = path[path_length - 1 - i];
        }

        write_json_to_file("output.json", 1, NULL, reversed_path, path_length, distances[end]);
    }
}

int main(int argc, char *argv[]) {
    if (argc != 3) {
        fprintf(stderr, "Usage : %s <planete_depart> <planete_arrivee>\n", argv[0]);
        return EXIT_FAILURE;
    }

    long long start = atoll(argv[1]); // convertit les string en nombre
    long long end = atoll(argv[2]);

    if (start < 1 || start > MAX_PLANETS) {
        write_json_to_file("output.json", 0, "Numéro de planète de départ invalide. Doit être entre 1 et 6000.", NULL, 0, 0.0);
        return EXIT_FAILURE;
    }

    if (end < 1 || end > MAX_PLANETS) {
        write_json_to_file("output.json", 0, "Numéro de planète d'arrivée invalide. Doit être entre 1 et 6000.", NULL, 0, 0.0);
        return EXIT_FAILURE;
    }

    const char *filename = "graph.txt";
    Graph *graph = read_graph(filename);

    if (!graph) {
        write_json_to_file("output.json", 0, "Erreur lors de la lecture du fichier du graphe.", NULL, 0, 0.0);
        return EXIT_FAILURE;
    }

    dikjstra(graph, start, end);

    return 0;
}
