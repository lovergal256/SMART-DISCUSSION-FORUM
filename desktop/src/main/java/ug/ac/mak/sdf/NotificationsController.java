package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;

public class NotificationsController {

    @FXML private VBox notificationsContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        loadNotifications();
    }

    private void loadNotifications() {
        statusLabel.setText("Loading notifications...");

        new Thread(() -> {
            try {
                List<ApiClient.NotificationItem> items = ApiClient.getNotifications();
                Platform.runLater(() -> render(items));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load notifications: " + e.getMessage()));
            }
        }).start();
    }

    private void render(List<ApiClient.NotificationItem> items) {
        notificationsContainer.getChildren().clear();

        if (items.isEmpty()) {
            Label empty = new Label("No notifications yet.");
            empty.getStyleClass().add("panel-empty-state");
            notificationsContainer.getChildren().add(empty);
            statusLabel.setText("");
            return;
        }

        for (ApiClient.NotificationItem n : items) {
            notificationsContainer.getChildren().add(buildRow(n));
        }
        statusLabel.setText("");
    }

    private HBox buildRow(ApiClient.NotificationItem n) {
        boolean isUnread = "Unread".equalsIgnoreCase(n.status());

        Label message = new Label(n.message());
        message.getStyleClass().add(isUnread ? "notif-message-unread" : "notif-message-read");
        message.setWrapText(true);
        HBox.setHgrow(message, Priority.ALWAYS);

        Label time = new Label(formatDate(n.createdAt()));
        time.getStyleClass().add("panel-item-meta");

        VBox textBlock = new VBox(3, message, time);
        HBox.setHgrow(textBlock, Priority.ALWAYS);

        HBox row = new HBox(10, textBlock);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.getStyleClass().add(isUnread ? "notif-row-unread" : "panel-item");
        row.setPadding(new Insets(12));

        if (isUnread) {
            row.setStyle("-fx-cursor: hand;");
            row.setOnMouseClicked(event -> markRead(n, row, message));
        }

        return row;
    }

    private void markRead(ApiClient.NotificationItem n, HBox row, Label messageLabel) {
        new Thread(() -> {
            try {
                ApiClient.markNotificationRead(n.id());
                Platform.runLater(() -> {
                    row.getStyleClass().remove("notif-row-unread");
                    row.getStyleClass().add("panel-item");
                    messageLabel.getStyleClass().remove("notif-message-unread");
                    messageLabel.getStyleClass().add("notif-message-read");
                    row.setOnMouseClicked(null);
                    row.setStyle("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to mark as read: " + e.getMessage()));
            }
        }).start();
    }

    private String formatDate(String iso) {
        try {
            OffsetDateTime dt = OffsetDateTime.parse(iso);
            return dt.format(DateTimeFormatter.ofPattern("MMM d, yyyy HH:mm"));
        } catch (Exception e) {
            return iso;
        }
    }
}