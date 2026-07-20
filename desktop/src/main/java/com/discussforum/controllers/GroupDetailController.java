package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;

public class GroupDetailController {

    @FXML private Label groupNameLabel;
    @FXML private Label statusLabel;
    @FXML private VBox membersList;
    @FXML private Button createQuizButton;

    private int currentGroupId;

    public void loadGroup(int groupId, String groupName) {
        this.currentGroupId = groupId;
        groupNameLabel.setText(groupName);
        statusLabel.setText("Loading members...");
        membersList.getChildren().clear();

        // Only lecturers can create quizzes, matching the web app's manage-quizzes gate.
        if (createQuizButton != null) {
            boolean isLecturer = ApiService.isLecturer();
            createQuizButton.setVisible(isLecturer);
            createQuizButton.setManaged(isLecturer);
        }

        new Thread(() -> {
            try {
                JsonObject group = ApiService.get("/groups/" + groupId);

                javafx.application.Platform.runLater(() -> {
                    JsonArray members = group.getAsJsonArray("members");
                    statusLabel.setText(members.size() + " member(s)");

                    for (JsonElement el : members) {
                        JsonObject member = el.getAsJsonObject();
                        membersList.getChildren().add(createMemberRow(member));
                    }
                });

            } catch (Exception e) {
                javafx.application.Platform.runLater(() ->
                    statusLabel.setText("Error loading group: " + e.getMessage()));
            }
        }).start();
    }

    public void setGroupId(int groupId) {
        loadGroup(groupId, groupNameLabel.getText());
    }

    private HBox createMemberRow(JsonObject member) {
        HBox row = new HBox(10);
        row.setStyle("-fx-padding: 10; -fx-border-color: #eee; -fx-border-width: 0 0 1 0;");
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        String name = member.get("name").getAsString();
        String email = member.get("email").getAsString();
        String role = member.get("role").getAsString();

        VBox info = new VBox(3);
        Label nameLabel = new Label(name);
        nameLabel.setStyle("-fx-font-weight: bold; -fx-text-fill: #333;");
        Label emailLabel = new Label(email);
        emailLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");
        info.getChildren().addAll(nameLabel, emailLabel);

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Label roleLabel = new Label(role);
        String roleColor = role.equals("admin") ? "#0077b6" : "#888";
        roleLabel.setStyle("-fx-text-fill: " + roleColor + "; -fx-font-size: 11px; " +
                          "-fx-padding: 2 8; -fx-background-color: " +
                          (role.equals("admin") ? "#e0f0ff" : "#f0f0f0") +
                          "; -fx-background-radius: 10;");

        row.getChildren().addAll(info, spacer, roleLabel);
        return row;
    }

    @FXML
    private void handleCreateQuiz() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/CreateQuiz.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            CreateQuizController controller = loader.getController();
            controller.setGroup(currentGroupId, groupNameLabel.getText());
            Stage stage = (Stage) groupNameLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void handleBack() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Groups.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) groupNameLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}