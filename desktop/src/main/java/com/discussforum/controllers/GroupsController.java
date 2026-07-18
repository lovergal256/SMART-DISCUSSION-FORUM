package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;

import java.net.URL;
import java.util.ResourceBundle;

public class GroupsController implements Initializable {

    @FXML private Label userLabel;
    @FXML private Label sectionTitle;
    @FXML private Label statusLabel;
    @FXML private VBox groupsList;

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        userLabel.setText(ApiService.getCurrentUserName());
        loadMyGroups();
    }

    @FXML
    private void loadMyGroups() {
        sectionTitle.setText("My Groups");
        statusLabel.setText("Loading...");
        groupsList.getChildren().clear();

        new Thread(() -> {
            try {
                JsonArray groups = ApiService.getArray("/groups");
                javafx.application.Platform.runLater(() -> {
                    if (groups.size() == 0) {
                        statusLabel.setText("You are not a member of any groups yet.");
                    } else {
                        statusLabel.setText(groups.size() + " group(s)");
                        for (JsonElement el : groups) {
                            JsonObject g = el.getAsJsonObject();
                            groupsList.getChildren().add(createGroupCard(g));
                        }
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() ->
                    statusLabel.setText("Error loading groups: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void loadDiscoverGroups() {
        sectionTitle.setText("Discover Groups");
        statusLabel.setText("Loading...");
        groupsList.getChildren().clear();

        new Thread(() -> {
            try {
                JsonArray groups = ApiService.getArray("/groups/discover");
                javafx.application.Platform.runLater(() -> {
                    if (groups.size() == 0) {
                        statusLabel.setText("No public groups available.");
                    } else {
                        statusLabel.setText(groups.size() + " public group(s)");
                        for (JsonElement el : groups) {
                            JsonObject g = el.getAsJsonObject();
                            groupsList.getChildren().add(createGroupCard(g));
                        }
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() ->
                    statusLabel.setText("Error loading groups: " + e.getMessage()));
            }
        }).start();
    }

    private VBox createGroupCard(JsonObject group) {
        VBox card = new VBox(5);
        card.setStyle("-fx-background-color: white; -fx-padding: 15; -fx-background-radius: 6; " +
                      "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2); " +
                      "-fx-border-left-color: #0077b6; -fx-border-left-width: 3;");

        String name = group.get("name").getAsString();
        String desc = group.has("description") && !group.get("description").isJsonNull()
                ? group.get("description").getAsString() : "No description";
        int members = group.has("members_count") ? group.get("members_count").getAsInt() : 0;

        Label nameLabel = new Label(name);
        nameLabel.setStyle("-fx-font-size: 14px; -fx-font-weight: bold; -fx-text-fill: #0077b6;");

        Label descLabel = new Label(desc);
        descLabel.setStyle("-fx-text-fill: #555; -fx-font-size: 12px;");

        Label membersLabel = new Label(members + " members");
        membersLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");

        card.getChildren().addAll(nameLabel, descLabel, membersLabel);
        return card;
    }

    @FXML
    private void handleLogout() {
        ApiService.logout();
        try {
            javafx.fxml.FXMLLoader loader = new javafx.fxml.FXMLLoader(
                getClass().getResource("/com/discussforum/views/Login.fxml"));
            javafx.scene.Scene scene = new javafx.scene.Scene(loader.load(), 900, 600);
            javafx.stage.Stage stage = (javafx.stage.Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
