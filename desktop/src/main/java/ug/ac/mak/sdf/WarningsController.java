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

public class WarningsController {

    @FXML private VBox blacklistBanner;
    @FXML private VBox warningsPanel;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("warnings");
        }
        loadWarnings();
    }

    private void loadWarnings() {
        statusLabel.setText("Loading...");

        new Thread(() -> {
            try {
                ApiClient.WarningsResponse resp = ApiClient.getWarnings();
                Platform.runLater(() -> render(resp));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load warnings: " + e.getMessage()));
            }
        }).start();
    }

    private void render(ApiClient.WarningsResponse resp) {
        blacklistBanner.getChildren().clear();

        if (resp.activeBlacklist() != null) {
            ApiClient.ActiveBlacklist bl = resp.activeBlacklist();

            Label title = new Label("Account Blocked");
            title.getStyleClass().add("blacklist-banner-title");

            Label reason = new Label("Reason: " + bl.reason());
            reason.getStyleClass().add("blacklist-banner-text");
            reason.setWrapText(true);

            Label dates = new Label("Blocked from " + bl.startDate() + " until " + bl.endDate() + ".");
            dates.getStyleClass().add("blacklist-banner-text");

            VBox banner = new VBox(4, title, reason, dates);
            banner.getStyleClass().add("blacklist-banner");
            banner.setPadding(new Insets(14));
            blacklistBanner.getChildren().add(banner);
        }

        warningsPanel.getChildren().clear();
        warningsPanel.getChildren().add(panelHeader("⚠️", "Warning History"));

        if (resp.warnings().isEmpty()) {
            Label empty = new Label("You have no warnings. Keep up the activity!");
            empty.getStyleClass().add("panel-empty-state");
            warningsPanel.getChildren().add(empty);
        } else {
            for (ApiClient.WarningItem w : resp.warnings()) {
                warningsPanel.getChildren().add(buildWarningRow(w));
            }
        }

        statusLabel.setText("");
    }

    private Label panelHeader(String icon, String title) {
        Label header = new Label(icon + "  " + title);
        header.getStyleClass().add("panel-title");
        return header;
    }

    private HBox buildWarningRow(ApiClient.WarningItem w) {
        Label label = new Label("Warning #" + w.warningNumber());
        label.getStyleClass().add("panel-item-title");
        HBox.setHgrow(label, Priority.ALWAYS);

        Label date = new Label(formatDate(w.warningDate()));
        date.getStyleClass().add("panel-item-meta");

        HBox row = new HBox(10, label, date);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.getStyleClass().add("panel-item");
        return row;
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