import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class QueryData {
    public static void main(String[] args) {
        // Requête SQL corrigée avec LPAD pour gérer les zéros initiaux
        String querySql = """
                SELECT 
                    T.planet_id AS sourcePlanet,
                    T.destination_planet_id AS destPlanet,
                    T.day_of_week,
                    T.departure_time,
                    T.ship_id,
                    (P1.X + P1.SubGridX) * 6 AS sourceX,
                    (P1.Y + P1.SubGridY) * 6 AS sourceY,
                    (P2.X + P2.SubGridX) * 6 AS destX,
                    (P2.Y + P2.SubGridY) * 6 AS destY
                FROM 
                    trip T
                INNER JOIN 
                    planet P1 ON LPAD(T.planet_id, 11, '0') = P1.id
                INNER JOIN 
                    planet P2 ON LPAD(T.destination_planet_id, 11, '0') = P2.id;
                """;

        String outputFilePath = "trip_data_with_distance.csv"; // Fichier de sortie

        try (Connection con = DatabaseConnection.getConnection();
             Statement stmt = con.createStatement();
             ResultSet rs = stmt.executeQuery(querySql);
             BufferedWriter writer = new BufferedWriter(new FileWriter(outputFilePath))) {

            // Écrire l'en-tête du fichier
            writer.write("sourcePlanet,destPlanet,dayOfWeek,departureTime,shipId,sourceX,sourceY,destX,destY,distance");
            writer.newLine();

            // Parcourir les résultats de la requête
            while (rs.next()) {
                // Récupérer les données pour la planète source et destination
                int sourcePlanet = rs.getInt("sourcePlanet");
                int destPlanet = rs.getInt("destPlanet");
                String dayOfWeek = rs.getString("day_of_week");
                String departureTime = rs.getString("departure_time");
                int shipId = rs.getInt("ship_id");

                // Coordonnées calculées directement en SQL
                double sourceX = rs.getDouble("sourceX");
                double sourceY = rs.getDouble("sourceY");
                double destX = rs.getDouble("destX");
                double destY = rs.getDouble("destY");

                // Calculer la distance euclidienne
                double distance = Math.sqrt(Math.pow(destX - sourceX, 2) + Math.pow(destY - sourceY, 2));

                // Écrire les données dans le fichier CSV
                writer.write(sourcePlanet + "," + destPlanet + "," + dayOfWeek + "," + departureTime + "," + shipId + ","
                        + sourceX + "," + sourceY + "," + destX + "," + destY + "," + distance);
                writer.newLine();
            }

            System.out.println("Les données ont été écrites dans le fichier : " + outputFilePath);

        } catch (SQLException e) {
            System.err.println("Erreur lors de l'exécution de la requête : " + e.getMessage());
        } catch (IOException e) {
            System.err.println("Erreur lors de l'écriture du fichier : " + e.getMessage());
        }
    }
}
