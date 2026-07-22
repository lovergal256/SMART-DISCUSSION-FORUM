package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonObject;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;

public class LoginController {

    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private Label errorLabel;
    @FXML private Button loginButton;

    @FXML
    private void handleLogin() {
        String email = emailField.getText().trim();
        String password = passwordField.getText().trim();

        if (email.isEmpty() || password.isEmpty()) {
            errorLabel.setStyle("-fx-text-fill: red;");
            errorLabel.setText("Please enter both email and password.");
            return;
        }

        loginButton.setDisable(true);
        loginButton.setText("Logging in...");

        new Thread(() -> {
            try {
                JsonObject response = ApiService.login(email, password);

                javafx.application.Platform.runLater(() -> {
                    if (response.has("token")) {
                        try {
                           String view;
if (ApiService.isAdmin()) {
    view = "/com/discussforum/views/AdminDashboard.fxml";
} else if (ApiService.isLecturer()) {
    view = "/com/discussforum/views/Dashboard.fxml";
} else {
    view = "/com/discussforum/views/Groups.fxml";
}
                            FXMLLoader loader = new FXMLLoader(getClass().getResource(view));
                            Scene scene = new Scene(loader.load(), 900, 600);
                            Stage stage = (Stage) loginButton.getScene().getWindow();
                            stage.setScene(scene);
                        } catch (Exception e) {
                            errorLabel.setStyle("-fx-text-fill: red;");
                            errorLabel.setText("Error loading home screen: " + e.getMessage());
                            loginButton.setDisable(false);
                            loginButton.setText("Login");
                        }
                    } else {
                        errorLabel.setStyle("-fx-text-fill: red;");
                        errorLabel.setText(response.has("message") ?
                            response.get("message").getAsString() : "Login failed.");
                        loginButton.setDisable(false);
                        loginButton.setText("Login");
                    }
                });

            } catch (Exception e) {
                javafx.application.Platform.runLater(() -> {
                    errorLabel.setStyle("-fx-text-fill: red;");
                    errorLabel.setText("Cannot connect to server. Make sure the server is running.");
                    loginButton.setDisable(false);
                    loginButton.setText("Login");
                });
            }
        }).start();
    }
}