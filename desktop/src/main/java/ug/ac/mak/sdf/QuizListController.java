package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Locale;
import java.util.List;

public class QuizListController {

    @FXML private VBox quizzesContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("quizzes");
        }
        loadQuizzes();
    }

    private void loadQuizzes() {
        statusLabel.setText("Loading quizzes...");

        new Thread(() -> {
            try {
                List<ApiClient.QuizListItem> quizzes = ApiClient.getQuizzes();
                Platform.runLater(() -> {
                    quizzesContainer.getChildren().clear();
                    for (ApiClient.QuizListItem q : quizzes) {
                        quizzesContainer.getChildren().add(buildCard(q));
                    }
                    statusLabel.setText(quizzes.size() + " quiz(zes) loaded.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load quizzes: " + e.getMessage()));
            }
        }).start();
    }

    private VBox buildCard(ApiClient.QuizListItem q) {
        Label title = new Label(q.title() + "  —  " + q.groupName());
        title.getStyleClass().add("group-card-title");

        String formattedStart;
        try {
            OffsetDateTime dt = OffsetDateTime.parse(q.startTime());
            formattedStart = dt.format(DateTimeFormatter.ofPattern("MMM d, yyyy · h:mm a"));
        } catch (Exception ex) {
            formattedStart = q.startTime();
        }

        String metaText;
        if (ApiClient.isLecturer()) {
            String average = q.averageScore() == null ? "N/A" : String.format(Locale.US, "%.2f%%", q.averageScore());
            metaText = "🕒 " + formattedStart + "  ·  " + q.questionsCount() + " question(s)  ·  "
                + q.duration() + " min  ·  Attempts: " + q.attemptCount() + "  ·  Avg: " + average;
        } else {
            metaText = "🕒 " + formattedStart + "  ·  " + q.questionsCount() + " question(s)  ·  " + q.duration() + " min";
        }

        Label meta = new Label(metaText);
        meta.getStyleClass().add("topic-meta");

        Label badge = new Label(badgeText(q));
        badge.getStyleClass().add(badgeStyleClass(q));

        HBox topRow = new HBox(10, title, badge);

        VBox card = new VBox(4, topRow, meta);
        card.getStyleClass().add("group-card");
        card.setPadding(new Insets(14, 16, 14, 16));
        card.setStyle("-fx-cursor: hand;");
        card.setOnMouseClicked(event -> openQuiz(q));
        return card;
    }

    private String badgeText(ApiClient.QuizListItem q) {
        if (ApiClient.isLecturer()) {
            return q.resultsReleased() ? "Results Released" : "Pending Release";
        }
        if (q.attempted()) return "Completed";
        return switch (q.status()) {
            case "active" -> "Active";
            case "ended" -> "Ended";
            default -> "Upcoming";
        };
    }

    private String badgeStyleClass(ApiClient.QuizListItem q) {
        if (ApiClient.isLecturer()) {
            return q.resultsReleased() ? "quiz-badge-completed" : "quiz-badge-upcoming";
        }
        if (q.attempted()) return "quiz-badge-completed";
        return switch (q.status()) {
            case "active" -> "quiz-badge-active";
            case "ended" -> "quiz-badge-ended";
            default -> "quiz-badge-upcoming";
        };
    }

    private void openQuiz(ApiClient.QuizListItem q) {
        try {
            if (ApiClient.isLecturer()) {
                var reviewLoader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/quiz_review.fxml"));
                javafx.scene.Parent reviewRoot = reviewLoader.load();
                QuizReviewController reviewController = reviewLoader.getController();
                reviewController.setQuizId(q.id());
                javafx.stage.Stage reviewStage = (javafx.stage.Stage) quizzesContainer.getScene().getWindow();
                javafx.scene.Scene reviewScene = new javafx.scene.Scene(reviewRoot, 900, 600);
                ThemeManager.applyTheme(reviewScene);
                reviewStage.setScene(reviewScene);
                return;
            }

            String fxml = q.attempted()
                    ? "/ug/ac/mak/sdf/quiz_result.fxml"
                    : "/ug/ac/mak/sdf/quiz_take.fxml";

            var loader = new javafx.fxml.FXMLLoader(getClass().getResource(fxml));
            javafx.scene.Parent root = loader.load();

            if (q.attempted()) {
                QuizResultController controller = loader.getController();
                controller.setQuizId(q.id());
            } else {
                QuizTakeController controller = loader.getController();
                controller.setQuizId(q.id());
            }

            javafx.stage.Stage stage = (javafx.stage.Stage) quizzesContainer.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to open quiz: " + e.getMessage());
        }
    }
}