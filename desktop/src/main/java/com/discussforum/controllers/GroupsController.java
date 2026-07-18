package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;

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
    public void loadMyGroups() {
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
                            groupsList.getChildren().add(createGroupCard(el.getAsJsonObject()));
                        }
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() ->
                    statusLabel.setText("Error: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    public void loadDiscoverGroups() {
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
                            groupsList.getChildren().add(createGroupCard(el.getAsJsonObject()));
                        }
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() ->
                    statusLabel.setText("Error: " + e.getMessage()));
            }
        }).start();
    }

    private VBox createGroupCard(JsonObject group) {
        VBox card = new VBox(5);
        card.setStyle("-fx-background-color: white; -fx-padding: 15; -fx-background-radius: 6; " +
                      "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2); " +
                      "-fx-cursor: hand;");

        String name = group.get("name").getAsString();
        String desc = group.has("description") && !group.get("description").isJsonNull()
                ? group.get("description").getAsString() : "No description";
        int members = group.has("members_count") ? group.get("members_count").getAsInt() : 0;
        int id = group.get("id").getAsInt();

        Label nameLabel = new Label(name);
        nameLabel.setStyle("-fx-font-size: 14px; -fx-font-weight: bold; -fx-text-fill: #0077b6;");

        Label descLabel = new Label(desc);
        descLabel.setStyle("-fx-text-fill: #555; -fx-font-size: 12px;");

        Label membersLabel = new Label(members + " members · click to view");
        membersLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");

        card.getChildren().addAll(nameLabel, descLabel, membersLabel);

        // Click to open group detail
        card.setOnMouseClicked(e -> openGroupDetail(id, name));

        // Hover effect
        card.setOnMouseEntered(e -> card.setStyle(card.getStyle() +
            "-fx-background-color: #f0f7ff;"));
        card.setOnMouseExited(e -> card.setStyle(
            "-fx-background-color: white; -fx-padding: 15; -fx-background-radius: 6; " +
            "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2); -fx-cursor: hand;"));

        return card;
    }

    private void openGroupDetail(int groupId, String groupName) {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/GroupDetail.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);

            GroupDetailController controller = loader.getController();
            controller.loadGroup(groupId, groupName);

            Stage stage = (Stage) groupsList.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error opening group: " + e.getMessage());
        }
    }

    @FXML
    private void handleLogout() {
        ApiService.logout();
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Login.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
