package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Label;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

public class AdminGroupsController {

    @FXML private Label statusLabel;
    @FXML private VBox groupsList;

    @FXML
    public void initialize() {
        new Thread(() -> {
            try {
                JsonArray groups = ApiService.getArray("/admin/groups");

                Platform.runLater(() -> {
                    groupsList.getChildren().clear();
                    if (groups.size() == 0) {
                        statusLabel.setText("No groups found.");
                    } else {
                        statusLabel.setText("");
                        for (int i = 0; i < groups.size(); i++) {
                            JsonObject g = groups.get(i).getAsJsonObject();
                            VBox item = new VBox(2);
                            Label name = new Label(g.get("name").getAsString());
                            name.setStyle("-fx-font-weight: bold; -fx-text-fill: #023e8a;");
                            Label meta = new Label(g.get("members").getAsString() + " members");
                            meta.setStyle("-fx-text-fill: #888; -fx-font-size: 12px;");
                            item.getChildren().addAll(name, meta);
                            item.setStyle("-fx-background-color: white; -fx-padding: 12; -fx-background-radius: 6;");
                            groupsList.getChildren().add(item);
                        }
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Error: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void backToDashboard() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/AdminDashboard.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) groupsList.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }
}