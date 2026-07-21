package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;

public class TopBarController {
    @FXML private Label topbarUserName;
    @FXML private Label topbarUserRole;
    @FXML private Label topbarAvatar;
    @FXML private Label bellIcon;
    @FXML private Label unreadBadge;
    @FXML private HBox userMenuArea;

    @FXML
    public void initialize() {
        loadUnreadCount();
        loadUserInfo();
    }

    private void loadUserInfo() {
        new Thread(() -> {
            try {
                ApiClient.ProfileData profile = ApiClient.getProfile();
                Platform.runLater(() -> {
                    topbarUserName.setText(profile.fullName());
                    topbarUserRole.setText(capitalize(profile.role()));
                    topbarAvatar.setText(initials(profile.fullName()));
                });
            } catch (Exception e) {
                // Silently ignore — labels just keep their default text if this fails
            }
        }).start();
    }

    private String capitalize(String s) {
        if (s == null || s.isBlank()) return "";
        return s.substring(0, 1).toUpperCase() + s.substring(1).toLowerCase();
    }

    private String initials(String fullName) {
        if (fullName == null || fullName.isBlank()) return "?";
        String[] parts = fullName.trim().split("\\s+");
        StringBuilder sb = new StringBuilder();
        for (int i = 0; i < Math.min(2, parts.length); i++) {
            if (!parts[i].isEmpty()) sb.append(Character.toUpperCase(parts[i].charAt(0)));
        }
        return sb.length() > 0 ? sb.toString() : "?";
    }

    private void loadUnreadCount() {
        new Thread(() -> {
            try {
                java.util.List<ApiClient.NotificationItem> items = ApiClient.getNotifications();
                long unread = items.stream().filter(n -> "Unread".equalsIgnoreCase(n.status())).count();

                Platform.runLater(() -> {
                    if (unread > 0) {
                        unreadBadge.setText(unread > 9 ? "9+" : String.valueOf(unread));
                        unreadBadge.setVisible(true);
                        unreadBadge.setManaged(true);
                    } else {
                        unreadBadge.setVisible(false);
                        unreadBadge.setManaged(false);
                    }
                });
            } catch (Exception e) {
                // Silently ignore — badge just won't show if this fails (e.g. not logged in yet)
            }
        }).start();
    }

    @FXML
    private void handleBellClick() {
        navigateTo("/ug/ac/mak/sdf/notifications.fxml", bellIcon);
    }

    @FXML
    private void handleUserMenuClick() {
        navigateTo("/ug/ac/mak/sdf/profile.fxml", userMenuArea);
    }

    private void navigateTo(String fxmlPath, javafx.scene.Node fromNode) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource(fxmlPath));
            javafx.scene.Parent root = loader.load();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            javafx.stage.Stage stage = (javafx.stage.Stage) fromNode.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            System.err.println("Failed to navigate to " + fxmlPath + ": " + e.getMessage());
        }
    }
}