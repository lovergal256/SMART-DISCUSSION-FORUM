package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.scene.control.ToggleButton;

public class ProfileController {

    @FXML private Label infoFullNameLabel;
    @FXML private Label infoEmailLabel;
    @FXML private Label infoRoleLabel;
    @FXML private Label infoRoleIdLabel;
    @FXML private Label infoDateJoinedLabel;
    @FXML private Label infoThemeLabel;

    @FXML private TextField fullNameField;
    @FXML private ToggleButton lightToggle;
    @FXML private ToggleButton darkToggle;
    @FXML private Label profileStatusLabel;

    @FXML private PasswordField currentPasswordField;
    @FXML private PasswordField newPasswordField;
    @FXML private PasswordField confirmPasswordField;
    @FXML private Label passwordStatusLabel;

    @FXML private PasswordField deleteConfirmField;
    @FXML private Label deleteStatusLabel;

    @FXML private SideBarController sidebarController;

    private String selectedTheme = "light";

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("profile");
        }
        loadProfile();
    }

    private void loadProfile() {
        profileStatusLabel.setText("Loading profile...");

        new Thread(() -> {
            try {
                ApiClient.ProfileData profile = ApiClient.getProfile();
                Platform.runLater(() -> {
                    infoFullNameLabel.setText("Full Name: " + profile.fullName());
                    infoEmailLabel.setText("Email: " + profile.email());
                    infoRoleLabel.setText("Role: " + capitalize(profile.role()));
                    infoRoleIdLabel.setText("Role ID: " + profile.roleId());
                    infoDateJoinedLabel.setText("Date Joined: " + profile.dateJoined());
                    infoThemeLabel.setText("Theme: " + capitalize(profile.theme()));

                    fullNameField.setText(profile.fullName());
                    selectedTheme = profile.theme() == null ? "light" : profile.theme();
                    lightToggle.setSelected("light".equalsIgnoreCase(selectedTheme));
                    darkToggle.setSelected("dark".equalsIgnoreCase(selectedTheme));
                    profileStatusLabel.setText("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> profileStatusLabel.setText("Failed to load profile: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleLightSelected() {
        selectedTheme = "light";
        lightToggle.setSelected(true);
        darkToggle.setSelected(false);
    }

    @FXML
    private void handleDarkSelected() {
        selectedTheme = "dark";
        darkToggle.setSelected(true);
        lightToggle.setSelected(false);
    }

    @FXML
    private void handleSaveProfile() {
        String fullName = fullNameField.getText();
        if (fullName == null || fullName.isBlank()) {
            profileStatusLabel.setText("Full name is required.");
            return;
        }

        profileStatusLabel.setText("Saving...");
        String themeToSave = selectedTheme;

        new Thread(() -> {
            try {
                String message = ApiClient.updateProfile(fullName, themeToSave);
                Platform.runLater(() -> {
                    profileStatusLabel.setText(message);
                    // Apply theme live to the current window
                    ThemeManager.setTheme(themeToSave);
                    var scene = fullNameField.getScene();
                    ThemeManager.applyTheme(scene);
                });
            } catch (Exception e) {
                Platform.runLater(() -> profileStatusLabel.setText("Failed to save: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleChangePassword() {
        String current = currentPasswordField.getText();
        String newPass = newPasswordField.getText();
        String confirm = confirmPasswordField.getText();

        if (current == null || current.isBlank() || newPass == null || newPass.isBlank()) {
            passwordStatusLabel.setText("All fields are required.");
            return;
        }

        if (!newPass.equals(confirm)) {
            passwordStatusLabel.setText("New password and confirmation do not match.");
            return;
        }

        if (newPass.length() < 6) {
            passwordStatusLabel.setText("New password must be at least 6 characters.");
            return;
        }

        passwordStatusLabel.setText("Updating password...");

        new Thread(() -> {
            try {
                String message = ApiClient.changePassword(current, newPass);
                Platform.runLater(() -> {
                    passwordStatusLabel.setText(message);
                    currentPasswordField.clear();
                    newPasswordField.clear();
                    confirmPasswordField.clear();
                });
            } catch (Exception e) {
                Platform.runLater(() -> passwordStatusLabel.setText(e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleDeleteAccount() {
        String password = deleteConfirmField.getText();
        if (password == null || password.isBlank()) {
            deleteStatusLabel.setText("Please enter your password to confirm.");
            return;
        }

        var alert = new javafx.scene.control.Alert(
                javafx.scene.control.Alert.AlertType.CONFIRMATION,
                "This will permanently anonymize your account and log you out. This cannot be undone. Continue?",
                javafx.scene.control.ButtonType.YES, javafx.scene.control.ButtonType.NO
        );
        alert.showAndWait().ifPresent(response -> {
            if (response == javafx.scene.control.ButtonType.YES) {
                doDeleteAccount(password);
            }
        });
    }

    private void doDeleteAccount(String password) {
        deleteStatusLabel.setText("Deleting account...");

        new Thread(() -> {
            try {
                ApiClient.deleteAccount(password);
                Platform.runLater(this::goToLogin);
            } catch (Exception e) {
                Platform.runLater(() -> deleteStatusLabel.setText(e.getMessage()));
            }
        }).start();
    }

    private void goToLogin() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/login.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            javafx.stage.Stage stage = (javafx.stage.Stage) fullNameField.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            deleteStatusLabel.setText("Account deleted, but failed to return to login: " + e.getMessage());
        }
    }
    private String capitalize(String s) {
        if (s == null || s.isBlank()) return "";
        return s.substring(0, 1).toUpperCase() + s.substring(1).toLowerCase();
    }
}
