package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonObject;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;

public class RegisterLecturerController {

    @FXML private TextField fullNameField;
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private Label statusLabel;
    @FXML private Button submitButton;

    @FXML
    private void handleSubmit() {
        String fullName = fullNameField.getText().trim();
        String email = emailField.getText().trim();
        String password = passwordField.getText().trim();

        if (fullName.isEmpty() || email.isEmpty() || password.isEmpty()) {
            statusLabel.setStyle("-fx-text-fill: red;");
            statusLabel.setText("All fields are required.");
            return;
        }

        submitButton.setDisable(true);
        submitButton.setText("Registering...");

        new Thread(() -> {
            try {
                JsonObject body = new JsonObject();
                body.addProperty("FullName", fullName);
                body.addProperty("Email", email);
                body.addProperty("Password", password);

                JsonObject response = ApiService.post("/admin/register-lecturer", body);

                Platform.runLater(() -> {
                    if (response.has("message") && !response.has("errors")) {
                        statusLabel.setStyle("-fx-text-fill: green;");
                        statusLabel.setText(response.get("message").getAsString());
                        fullNameField.clear();
                        emailField.clear();
                        passwordField.clear();
                    } else {
                        statusLabel.setStyle("-fx-text-fill: red;");
                        statusLabel.setText("Failed to register lecturer.");
                    }
                    submitButton.setDisable(false);
                    submitButton.setText("Register Lecturer");
                });

            } catch (Exception e) {
                Platform.runLater(() -> {
                    statusLabel.setStyle("-fx-text-fill: red;");
                    statusLabel.setText("Error: " + e.getMessage());
                    submitButton.setDisable(false);
                    submitButton.setText("Register Lecturer");
                });
            }
        }).start();
    }

    @FXML
    private void backToDashboard() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/AdminDashboard.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) fullNameField.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }
}