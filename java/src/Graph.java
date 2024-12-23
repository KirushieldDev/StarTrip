import java.util.Map;
import java.util.List;
import java.util.ArrayList;
import java.util.HashMap;

public class Graph {
    private Map<Integer, List<Edge>> adjacencyList;

    public static class Edge {
        int sourcePlanet;
        int destinationPlanet;
        String dayOfWeek;
        String departureTime;
        int shipId;
        
        public Edge(int sourcePlanet, int destinationPlanet, String dayOfWeek, String departureTime, int shipId) {
            this.sourcePlanet = sourcePlanet;
            this.destinationPlanet = destinationPlanet;
            this.dayOfWeek = dayOfWeek;
            this.departureTime = departureTime;
            this.shipId = shipId;
        }
    }

    public Graph() {
        this.adjacencyList = new HashMap<>();
    }

    public void addEdge(int sourcePlanet, int destinationPlanet, String dayOfWeek, String departureTime, int shipId) {
        adjacencyList.putIfAbsent(sourcePlanet, new ArrayList<>());
        adjacencyList.get(sourcePlanet).add(new Edge(sourcePlanet, destinationPlanet, dayOfWeek, departureTime, shipId));
    }

    public void printGraph() {
        StringBuilder sb = new StringBuilder();
        for (Map.Entry<Integer, List<Edge>> entry : adjacencyList.entrySet()) {
            sb.append("Planet ").append(entry.getKey()).append(" has trips to:\n");
            for (Edge edge : entry.getValue()) {
                sb.append("  -> Planet ").append(edge.destinationPlanet)
                  .append(" (Day: ").append(edge.dayOfWeek)
                  .append(", Departure: ").append(edge.departureTime)
                  .append(", Ship: ").append(edge.shipId).append(")\n");
            }
        }
        System.out.print(sb.toString());
    }

    public Map<Integer, List<Edge>> getAdjacencyList() {
        return adjacencyList;
    }
} 