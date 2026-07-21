package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

public class MyActivityController {

    @FXML private HBox statsRow;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("activity");
        }
        loadActivity();
    }

    private void loadActivity() {
        statusLabel.setText("Loading activity...");

        new Thread(() -> {
            try {
                ApiClient.ActivitySummary summary = ApiClient.getActivitySummary();
                Platform.runLater(() -> render(summary));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load activity: " + e.getMessage()));
            }
        }).start();
    }

   private void render(ApiClient.ActivitySummary s) {
        statsRow.getChildren().clear();

        statsRow.getChildren().add(buildStatCard("📝", String.valueOf(s.postsCreated()), "Posts Created"));
        statsRow.getChildren().add(buildStatCard("🗂", String.valueOf(s.topicsCreated()), "Topics Created"));
        statsRow.getChildren().add(buildStatCard("👥", String.valueOf(s.groupsCreated()), "Groups Created"));
        statsRow.getChildren().add(buildStatCard("✅", String.valueOf(s.quizzesAttempted()), "Quizzes Attempted"));
        statsRow.getChildren().add(buildStatCard("👥", String.valueOf(s.groupsJoined()), "Groups Joined"));

        statusLabel.setText("");
    }

    private VBox buildStatCard(String icon, String value, String label) {
        Label iconLabel = new Label(icon);
        iconLabel.getStyleClass().add("stat-card-icon");

        Label valueLabel = new Label(value);
        valueLabel.getStyleClass().add("stat-card-value");

        Label labelLabel = new Label(label);
        labelLabel.getStyleClass().add("stat-card-label");

        VBox textBlock = new VBox(2, valueLabel, labelLabel);

        HBox topRow = new HBox(10, iconLabel);
        VBox card = new VBox(8, topRow, textBlock);
        card.getStyleClass().add("stat-card");
        card.setPadding(new Insets(16));
        HBox.setHgrow(card, Priority.ALWAYS);

        return card;
    }
}