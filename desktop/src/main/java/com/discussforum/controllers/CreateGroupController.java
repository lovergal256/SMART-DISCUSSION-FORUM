package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonObject;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;

import java.net.URL;
import java.util.ResourceBundle;

public class CreateGroupController implements Initializable {

    @FXML private TextField nameField;
    @FXML private TextArea descriptionField;
    @FXML private ComboBox<String> visibilityBox;
    @FXML private Label feedbackLabel;
    @FXML private Button createButton;

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        visibilityBox.getItems().addAll("private", "public");
        visibilityBox.setValue("private");
    }

    @FXML
    private void handleCreate() {
        String name = nameField.getText().trim();
        String description = descriptionField.getText().trim();
        String visibility = visibilityBox.getValue();

        if (name.isEmpty()) {
            feedbackLabel.setStyle("-fx-text-fill: red;");
            feedbackLabel.setText("Group name is required.");
            return;
        }

        createButton.setDisable(true);
        createButton.setText("Creating...");

        JsonObject body = new JsonObject();
        body.addProperty("name", name);
        body.addProperty("description", description);
        body.addProperty("visibility", visibility);

        new Thread(() -> {
            try {
                JsonObject response = ApiService.post("/groups", body);
                javafx.application.Platform.runLater(() -> {
                    if (response.has("group")) {
                        feedbackLabel.setStyle("-fx-text-fill: green;");
                        feedbackLabel.setText("Group created successfully!");
                        // Navigate back to groups after short delay
                        new Thread(() -> {
                            try { Thread.sleep(1000); } catch (Exception e) {}
                            javafx.application.Platform.runLater(this::handleBack);
                        }).start();
                    } else {
                        feedbackLabel.setStyle("-fx-text-fill: red;");
                        feedbackLabel.setText(response.has("message") ?
                            response.get("message").getAsString() : "Failed to create group.");
                        createButton.setDisable(false);
                        createButton.setText("Create Group");
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() -> {
                    feedbackLabel.setStyle("-fx-text-fill: red;");
                    feedbackLabel.setText("Error: " + e.getMessage());
                    createButton.setDisable(false);
                    createButton.setText("Create Group");
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Groups.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) nameField.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
