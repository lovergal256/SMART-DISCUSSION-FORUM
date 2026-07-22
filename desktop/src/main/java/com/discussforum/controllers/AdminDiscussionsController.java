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

public class AdminDiscussionsController {

    @FXML private Label statusLabel;
    @FXML private VBox discussionsList;

    @FXML
    public void initialize() {
        new Thread(() -> {
            try {
                JsonArray discussions = ApiService.getArray("/admin/discussions");

                Platform.runLater(() -> {
                    discussionsList.getChildren().clear();
                    if (discussions.size() == 0) {
                        statusLabel.setText("No discussions found.");
                    } else {
                        statusLabel.setText("");
                        for (int i = 0; i < discussions.size(); i++) {
                            JsonObject d = discussions.get(i).getAsJsonObject();
                            VBox item = new VBox(2);
                            Label title = new Label(d.get("title").getAsString());
                            title.setStyle("-fx-font-weight: bold; -fx-text-fill: #023e8a;");
                            Label meta = new Label(d.get("author").getAsString() + " · " + d.get("posted_at").getAsString());
                            meta.setStyle("-fx-text-fill: #888; -fx-font-size: 12px;");
                            item.getChildren().addAll(title, meta);
                            item.setStyle("-fx-background-color: white; -fx-padding: 12; -fx-background-radius: 6;");
                            discussionsList.getChildren().add(item);
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
            Stage stage = (Stage) discussionsList.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }
}