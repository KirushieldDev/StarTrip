import java.io.*;
import java.sql.*;
import java.util.*;

/**
 * Classe representant un graphe oriente pour le reseau galactique de transport.
 */
public class GraphMaker {

    // Classe interne representant une arête dans le graphe
    public static class Edge {
        public final int target;
        public final double weight;

        public Edge(int target, double weight) {
            this.target = target;
            this.weight = weight;
        }
    }

    private final Map<Integer, List<Edge>> adjacencyList;

    public GraphMaker() {
        this.adjacencyList = new HashMap<>();
    }

    /**
     * Ajoute un sommet au graphe si non existant.
     * @param planetId l'identifiant unique de la planete (sommet).
     */
    public void addVertex(int planetId) {
        adjacencyList.putIfAbsent(planetId, new ArrayList<>());
    }

    /**
     * Ajoute une arête orientée entre deux sommets.
     * @param from l'identifiant de la planete source.
     * @param to l'identifiant de la planete cible.
     * @param weight le poids de l'arête (distance, temps, etc.).
     */
    public void addEdge(int from, int to, double weight) {
        addVertex(from);
        addVertex(to);
        adjacencyList.get(from).add(new Edge(to, weight));
    }

    /**
     * Charge les données des tables SQL et crée le graphe.
     * @param connection une connexion JDBC active.
     * @throws SQLException si une erreur survient lors de l'exécution des requêtes SQL.
     */
    public void loadGraphFromDatabase(Connection connection) throws SQLException {
        // Charger les sommets (planètes)
        String planetQuery = "SELECT id FROM planet";
        try (Statement planetStmt = connection.createStatement();
             ResultSet planetRs = planetStmt.executeQuery(planetQuery)) {
            while (planetRs.next()) {
                addVertex(planetRs.getInt("id"));
            }
        }

        // Charger les arêtes (trips)
        String tripQuery = "SELECT planet_id, destination_planet_id, distance FROM trip ";
        try (Statement tripStmt = connection.createStatement();
             ResultSet tripRs = tripStmt.executeQuery(tripQuery)) {
            while (tripRs.next()) {
                int from = tripRs.getInt("planet_id");
                int to = tripRs.getInt("destination_planet_id");
                double weight = tripRs.getDouble("distance");
                addEdge(from, to, weight);
            }
        }
    }

    /**
     * Exporte le graphe dans un fichier texte.
     * @param filePath le chemin du fichier de sortie.
     * @throws IOException si une erreur survient lors de l'écriture du fichier.
     */
    public void exportGraphToTxt(String filePath) throws IOException {
        try (BufferedWriter writer = new BufferedWriter(new FileWriter(filePath))) {
            for (Map.Entry<Integer, List<Edge>> entry : adjacencyList.entrySet()) {
                int from = entry.getKey();
                for (Edge edge : entry.getValue()) {
                    writer.write(from + " -> " + edge.target + " [weight=" + edge.weight + "]\n");
                }
            }
        }
    }

    /**
     * Implémente l'algorithme de Dijkstra pour trouver le plus court chemin.
     * @param start l'identifiant du sommet de départ.
     * @param target l'identifiant du sommet de destination.
     * @return le poids du plus court chemin et le chemin en lui-même sous forme de liste.
     */
    public Optional<PathResult> findShortestPath(int start, int target) {
        Map<Integer, Double> distances = new HashMap<>();
        Map<Integer, Integer> predecessors = new HashMap<>();
        PriorityQueue<Node> priorityQueue = new PriorityQueue<>(Comparator.comparingDouble(node -> node.distance));

        for (int vertex : adjacencyList.keySet()) {
            distances.put(vertex, Double.POSITIVE_INFINITY);
        }
        distances.put(start, 0.0);
        priorityQueue.add(new Node(start, 0.0));

        while (!priorityQueue.isEmpty()) {
            Node current = priorityQueue.poll();

            if (current.id == target) {
                return Optional.of(reconstructPath(start, target, predecessors, distances.get(target)));
            }

            for (Edge edge : getNeighbors(current.id)) {
                double newDist = distances.get(current.id) + edge.weight;
                if (newDist < distances.get(edge.target)) {
                    distances.put(edge.target, newDist);
                    predecessors.put(edge.target, current.id);
                    priorityQueue.add(new Node(edge.target, newDist));
                }
            }
        }

        return Optional.empty(); // Aucun chemin trouvé
    }

    /**
     * Reconstitue le chemin à partir des prédécesseurs.
     * @param start le sommet de départ.
     * @param target le sommet de destination.
     * @param predecessors la map des prédécesseurs.
     * @param totalWeight le poids total du chemin trouvé.
     * @return un objet PathResult contenant le chemin et son poids.
     */
    private PathResult reconstructPath(int start, int target, Map<Integer, Integer> predecessors, double totalWeight) {
        List<Integer> path = new LinkedList<>();
        for (Integer at = target; at != null; at = predecessors.get(at)) {
            path.add(0, at);
        }
        if (path.get(0) != start) {
            throw new IllegalStateException("Le chemin reconstruit est invalide.");
        }
        return new PathResult(path, totalWeight);
    }

    /**
     * Obtient les voisins d'un sommet donné.
     * @param vertex l'identifiant du sommet.
     * @return la liste des arêtes sortantes du sommet.
     */
    private List<Edge> getNeighbors(int vertex) {
        return adjacencyList.getOrDefault(vertex, Collections.emptyList());
    }

    /**
     * Classe representant un resultat de chemin.
     */
    public static class PathResult {
        public final List<Integer> path;
        public final double totalWeight;

        public PathResult(List<Integer> path, double totalWeight) {
            this.path = path;
            this.totalWeight = totalWeight;
        }

        @Override
        public String toString() {
            return "Path: " + path + ", Total Weight: " + totalWeight;
        }
    }

    /**
     * Classe interne representant un sommet dans la file de priorite.
     */
    private static class Node {
        public final int id;
        public final double distance;

        public Node(int id, double distance) {
            this.id = id;
            this.distance = distance;
        }
    }

    // Exemple de test du graphe
    public static void main(String[] args) {
        try {
            // Connexion à la base de données
            String url = "jdbc:mysql://localhost/startrip";
            String user = "root";
            String password = "";
            try (Connection connection = DriverManager.getConnection(url, user, password)) {
                GraphMaker graph = new GraphMaker();

                // Charger le graphe à partir de la base de données
                graph.loadGraphFromDatabase(connection);

                // Exporter le graphe dans un fichier texte
                String filePath = "graph_output.txt";
                graph.exportGraphToTxt(filePath);
                System.out.println("Le graphe a été exporté dans : " + filePath);

                // Tester Dijkstra
                Optional<PathResult> result = graph.findShortestPath(3
                        , 4);
                result.ifPresentOrElse(
                        System.out::println,
                        () -> System.out.println("Aucun chemin trouvé.")
                );
            }
        } catch (SQLException | IOException e) {
            e.printStackTrace();
        }
    }
}
