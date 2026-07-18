package com.discussforum.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.*;

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
            errorLabel.setText("Please enter both email and password.");
            return;
        }

        // Temporary: just show what was typed
        // We'll replace this with actual API call next
        errorLabel.setStyle("-fx-text-fill: green;");
        errorLabel.setText("Attempting login for: " + email);
    }
}