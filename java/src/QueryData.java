import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class QueryData {
    public static void main(String[] args) {
        String querySql = "SELECT planet_id, destination_planet_id, day_of_week, departure_time, ship_id FROM trip";
        Graph graph = new Graph();

        try (Connection con = DatabaseConnection.getConnection();
             Statement stmt = con.createStatement();
             ResultSet rs = stmt.executeQuery(querySql)) {

            // Build the graph from the data
            while (rs.next()) {
                int sourcePlanet = rs.getInt("planet_id");
                int destPlanet = rs.getInt("destination_planet_id");
                String dayOfWeek = rs.getString("day_of_week");
                String departureTime = rs.getString("departure_time");
                int shipId = rs.getInt("ship_id");

                graph.addEdge(sourcePlanet, destPlanet, dayOfWeek, departureTime, shipId);
            }

            // Display the graph
            System.out.println("Graph structure:");
            graph.printGraph();

        } catch (SQLException e) {
            System.err.println("Error executing query: " + e.getMessage());
        }
    }
}