package fr.uge;

import java.io.*;
import java.sql.*;

public class QueryData {
    public static void main(String[] args) {

        String camp = null;

        if (args.length > 0) {
            camp = args[0];
        }

        try (Connection conn = DatabaseConnection.getConnection();
             PrintWriter writer = new PrintWriter("../graph.txt")) {

            String query = "SELECT DISTINCT t.planet_id, t.destination_planet_id, " +
                    "(p1.x + p1.sub_grid_x) * 6 AS source_x, (p1.y + p1.sub_grid_y) * 6 AS source_y, " +
                    "(p2.x + p2.sub_grid_x) * 6 AS dest_x, (p2.y + p2.sub_grid_y) * 6 AS dest_y, " +
                    "s.camp " +
                    "FROM trip t " +
                    "JOIN planet p1 ON t.planet_id = p1.id " +
                    "JOIN planet p2 ON t.destination_planet_id = p2.id " +
                    "JOIN ship s ON s.id = t.ship_id";

            if (camp != null && !camp.equals("Empty") && !camp.isEmpty()) {
                query += " WHERE s.camp = ?";
            }

            try (PreparedStatement stmt = conn.prepareStatement(query)) {
                if (camp != null && !camp.equals("Empty") && !camp.isEmpty()) {
                    stmt.setString(1, camp);
                }

                try (ResultSet rs = stmt.executeQuery()) {
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
            }

        } catch (SQLException | IOException e) {
            e.printStackTrace();
        }
    }
}