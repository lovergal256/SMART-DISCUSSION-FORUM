package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Locale;

public class QuizReviewController {

    @FXML private Label titleLabel;
    @FXML private Label scheduleLabel;
    @FXML private Label statsLabel;
    @FXML private Label releaseStatusLabel;
    @FXML private Label statusLabel;
    @FXML private Button releaseResultsButton;
    @FXML private SideBarController sidebarController;

    private String quizId;

    public void setQuizId(String quizId) {
        this.quizId = quizId;
        if (sidebarController != null) {
            sidebarController.setActiveItem("quizzes");
        }
        loadReview();
    }

    private void loadReview() {
        statusLabel.setText("Loading quiz review...");

        new Thread(() -> {
            try {
                ApiClient.QuizReviewDetail review = ApiClient.getQuizReview(quizId);
                Platform.runLater(() -> render(review));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load quiz review: " + e.getMessage()));
            }
        }).start();
    }

    private void render(ApiClient.QuizReviewDetail review) {
        titleLabel.setText(review.title() + " — " + review.groupName());

        scheduleLabel.setText("Start: " + formatDateTime(review.startTime())
            + "  ·  End: " + formatDateTime(review.endTime())
            + "  ·  Duration: " + review.duration() + " min"
            + "  ·  Status: " + capitalize(review.status()));

        String average = review.averageScore() == null ? "N/A" : String.format(Locale.US, "%.2f%%", review.averageScore());
        String highest = review.highestScore() == null ? "N/A" : String.format(Locale.US, "%.2f%%", review.highestScore());
        String lowest = review.lowestScore() == null ? "N/A" : String.format(Locale.US, "%.2f%%", review.lowestScore());

        statsLabel.setText("Questions: " + review.questionsCount()
            + "  ·  Attempts: " + review.attemptCount()
            + "  ·  Avg: " + average
            + "  ·  Highest: " + highest
            + "  ·  Lowest: " + lowest);

        boolean released = review.resultsReleased();
        releaseStatusLabel.setText(released
            ? "Results are already released to students."
            : "Results are not released yet.");
        releaseResultsButton.setDisable(released);
        statusLabel.setText("");
    }

    @FXML
    private void handleReleaseResults() {
        releaseResultsButton.setDisable(true);
        statusLabel.setText("Releasing results...");

        new Thread(() -> {
            try {
                String message = ApiClient.releaseQuizResults(quizId);
                Platform.runLater(() -> {
                    statusLabel.setText(message);
                    loadReview();
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    releaseResultsButton.setDisable(false);
                    statusLabel.setText("Failed to release results: " + e.getMessage());
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/quizzes_list.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) titleLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    private String formatDateTime(String value) {
        try {
            return OffsetDateTime.parse(value).format(DateTimeFormatter.ofPattern("MMM d, yyyy · h:mm a"));
        } catch (Exception ex) {
            return value;
        }
    }

    private String capitalize(String value) {
        if (value == null || value.isBlank()) {
            return value;
        }
        return Character.toUpperCase(value.charAt(0)) + value.substring(1);
    }
}
