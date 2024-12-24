#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <string.h>
#include <limits.h>

#define MAX_VERTICES 500000
#define INFINITY INT_MAX

typedef struct {
    int id;
    double x;
    double y;
} Planet;

typedef struct {
    int target;
    double weight;
} Edge;

typedef struct {
    int id;
    double cost;
    double heuristic;
    int predecessor;
} Node;

typedef struct {
    Node* nodes[MAX_VERTICES];
    int size;
} PriorityQueue;

Planet planets[MAX_VERTICES];
Edge adjacencyList[MAX_VERTICES][MAX_VERTICES];
int adjacencyListSize[MAX_VERTICES];
int numPlanets = 0;

// Function to calculate the heuristic (Euclidean distance)
double heuristic(int start, int goal) {
    return sqrt(pow(planets[goal].x - planets[start].x, 2) + pow(planets[goal].y - planets[start].y, 2));
}

// Function to swap two nodes
void swap(Node* a, Node* b) {
    Node temp = *a;
    *a = *b;
    *b = temp;
}

// Function to heapify the priority queue
void heapify(PriorityQueue* pq, int idx) {
    int smallest = idx;
    int left = 2 * idx + 1;
    int right = 2 * idx + 2;

    if (left < pq->size && pq->nodes[left]->cost + pq->nodes[left]->heuristic < pq->nodes[smallest]->cost + pq->nodes[smallest]->heuristic) {
        smallest = left;
    }

    if (right < pq->size && pq->nodes[right]->cost + pq->nodes[right]->heuristic < pq->nodes[smallest]->cost + pq->nodes[smallest]->heuristic) {
        smallest = right;
    }

    if (smallest != idx) {
        swap(pq->nodes[idx], pq->nodes[smallest]);
        heapify(pq, smallest);
    }
}

// Function to extract the minimum element from the priority queue
Node* extractMin(PriorityQueue* pq) {
    if (pq->size == 0) {
        return NULL;
    }

    Node* minNode = pq->nodes[0];
    pq->nodes[0] = pq->nodes[pq->size - 1];
    pq->size--;
    heapify(pq, 0);

    return minNode;
}

// Function to insert a node into the priority queue
void insert(PriorityQueue* pq, Node* node) {
    pq->nodes[pq->size] = node;
    pq->size++;

    int i = pq->size - 1;
    while (i != 0 && pq->nodes[(i - 1) / 2]->cost + pq->nodes[(i - 1) / 2]->heuristic > pq->nodes[i]->cost + pq->nodes[i]->heuristic) {
        swap(pq->nodes[i], pq->nodes[(i - 1) / 2]);
        i = (i - 1) / 2;
    }
}

// Function to check if the priority queue is empty
int isEmpty(PriorityQueue* pq) {
    return pq->size == 0;
}

// Function to find the index of a planet by its ID
int findPlanetIndex(int id) {
    for (int i = 0; i < numPlanets; i++) {
        if (planets[i].id == id) {
            return i;
        }
    }
    return -1;
}

// Function to load the graph from the file
void loadGraph(const char* filename) {
    FILE* file = fopen(filename, "r");
    if (file == NULL) {
        perror("Unable to open file");
        exit(EXIT_FAILURE);
    }

    int sourceId, destId;
    double distance;
    while (fscanf(file, "%d %d %lf", &sourceId, &destId, &distance) == 3) {
        int sourceIndex = findPlanetIndex(sourceId);
        int destIndex = findPlanetIndex(destId);

        if (sourceIndex == -1) {
            planets[numPlanets].id = sourceId;
            sourceIndex = numPlanets;
            numPlanets++;
        }

        if (destIndex == -1) {
            planets[numPlanets].id = destId;
            destIndex = numPlanets;
            numPlanets++;
        }

        adjacencyList[sourceIndex][adjacencyListSize[sourceIndex]].target = destIndex;
        adjacencyList[sourceIndex][adjacencyListSize[sourceIndex]].weight = distance;
        adjacencyListSize[sourceIndex]++;
    }

    fclose(file);
}

// Function to reconstruct the path from the predecessors
void reconstructPath(int* predecessors, int start, int goal, int* path, int* pathSize) {
    int current = goal;
    int index = 0;

    while (current != -1) {
        path[index++] = current;
        current = predecessors[current];
    }

    *pathSize = index;
    for (int i = 0; i < index / 2; i++) {
        int temp = path[i];
        path[i] = path[index - i - 1];
        path[index - i - 1] = temp;
    }
}

// Function to apply the A* algorithm
void aStar(int start, int goal) {
    double distances[MAX_VERTICES];
    int predecessors[MAX_VERTICES];
    PriorityQueue pq;
    pq.size = 0;

    for (int i = 0; i < numPlanets; i++) {
        distances[i] = INFINITY;
        predecessors[i] = -1;
    }

    distances[start] = 0;
    Node* startNode = (Node*)malloc(sizeof(Node));
    startNode->id = start;
    startNode->cost = 0;
    startNode->heuristic = heuristic(start, goal);
    startNode->predecessor = -1;
    insert(&pq, startNode);

    while (!isEmpty(&pq)) {
        Node* current = extractMin(&pq);

        if (current->id == goal) {
            int path[MAX_VERTICES];
            int pathSize;
            reconstructPath(predecessors, start, goal, path, &pathSize);

            printf("Shortest path: ");
            for (int i = 0; i < pathSize; i++) {
                printf("%d ", planets[path[i]].id);
            }
            printf("\nTotal cost: %.2f\n", distances[goal]);
            free(current);
            return;
        }

        for (int i = 0; i < adjacencyListSize[current->id]; i++) {
            Edge edge = adjacencyList[current->id][i];
            double newDist = distances[current->id] + edge.weight;

            if (newDist < distances[edge.target]) {
                distances[edge.target] = newDist;
                predecessors[edge.target] = current->id;

                Node* neighbor = (Node*)malloc(sizeof(Node));
                neighbor->id = edge.target;
                neighbor->cost = newDist;
                neighbor->heuristic = heuristic(edge.target, goal);
                neighbor->predecessor = current->id;
                insert(&pq, neighbor);
            }
        }

        free(current);
    }

    printf("No path found.\n");
}

int main() {
    loadGraph("../graph.txt");

    int startPlanetId = 1;
    int goalPlanetId = 3;

    int startIndex = findPlanetIndex(startPlanetId);
    int goalIndex = findPlanetIndex(goalPlanetId);

    if (startIndex == -1 || goalIndex == -1) {
        printf("Start or goal planet not found.\n");
        return EXIT_FAILURE;
    }

    aStar(startIndex, goalIndex);

    return EXIT_SUCCESS;
}
