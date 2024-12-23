import java.io.*;
import java.sql.*;
import java.util.*;

public class QueryData {
    public static void main(String[] args) {
        try (Connection conn = DatabaseConnection.getConnection();
             PrintWriter writer = new PrintWriter("graph.txt")) {
            
            String query = "SELECT DISTINCT t.planet_id, t.destination_planet_id, " +
                          "(p1.x + p1.sub_grid_x) * 6 as source_x, (p1.y + p1.sub_grid_y) * 6 as source_y, " +
                          "(p2.x + p2.sub_grid_x) * 6 as dest_x, (p2.y + p2.sub_grid_y) * 6 as dest_y " +
                          "FROM trip t " +
                          "JOIN planet p1 ON t.planet_id = p1.id " +
                          "JOIN planet p2 ON t.destination_planet_id = p2.id";

            try (Statement stmt = conn.createStatement();
                 ResultSet rs = stmt.executeQuery(query)) {
                
                while (rs.next()) {
                    String sourcePlanet = String.format("%011d", rs.getInt("planet_id"));
                    String destPlanet = String.format("%011d", rs.getInt("destination_planet_id"));
                    
                    double sourceX = rs.getDouble("source_x");
                    double sourceY = rs.getDouble("source_y");
                    double destX = rs.getDouble("dest_x");
                    double destY = rs.getDouble("dest_y");
                    
                    double distance = Math.sqrt(Math.pow(destX - sourceX, 2) + Math.pow(destY - sourceY, 2)) * Math.pow(10, 9);
                    
                    writer.printf("%s %s %.2f%n", sourcePlanet, destPlanet, distance);
                }
            }

        } catch (SQLException | IOException e) {
            e.printStackTrace();
        }
    }
}
