package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.CheckBox;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

import java.util.prefs.Preferences;

public class LoginController {

    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private Label statusLabel;
    @FXML private CheckBox rememberMeCheckbox;

    private static final Preferences prefs = Preferences.userNodeForPackage(LoginController.class);
    private static final String PREF_EMAIL = "saved_email";
    private static final String PREF_PASSWORD = "saved_password";
    private static final String PREF_REMEMBER = "remember_me";

    @FXML
    public void initialize() {
        boolean remembered = prefs.getBoolean(PREF_REMEMBER, false);
        if (remembered) {
            emailField.setText(prefs.get(PREF_EMAIL, ""));
            passwordField.setText(prefs.get(PREF_PASSWORD, ""));
            rememberMeCheckbox.setSelected(true);
        }
    }

    @FXML
    private void handleLogin(ActionEvent event) {
        String email = emailField.getText();
        String password = passwordField.getText();

        statusLabel.setText("Logging in...");

        new Thread(() -> {
            try {
                String token = ApiClient.login(email, password);

                if (rememberMeCheckbox.isSelected()) {
                    prefs.put(PREF_EMAIL, email);
                    prefs.put(PREF_PASSWORD, password);
                    prefs.putBoolean(PREF_REMEMBER, true);
                } else {
                    prefs.remove(PREF_EMAIL);
                    prefs.remove(PREF_PASSWORD);
                    prefs.putBoolean(PREF_REMEMBER, false);
                }

               Platform.runLater(() -> {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/dashboard.fxml"));
        javafx.scene.Parent dashboardRoot = loader.load();
        javafx.stage.Stage stage = (javafx.stage.Stage) emailField.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(dashboardRoot, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
    } catch (Throwable ex) {
        statusLabel.setText("Loaded token but failed to open dashboard: " + ex.getMessage());
        ex.printStackTrace();
    }
});
            } catch (Exception e) {
                Platform.runLater(() ->
                        statusLabel.setText("Login failed: " + e.getMessage())
                );
            }
        }).start();
    }
}