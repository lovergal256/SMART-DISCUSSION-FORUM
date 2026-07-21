package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.VBox;

public class QuizResultController {

    @FXML private Label titleLabel;
    @FXML private Label scoreLabel;
    @FXML private Label noticeLabel;
    @FXML private VBox breakdownContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    public void setQuizId(String quizId) {
        if (sidebarController != null) {
            sidebarController.setActiveItem("quizzes");
        }
        loadResult(quizId);
    }

    private void loadResult(String quizId) {
        statusLabel.setText("Loading result...");

        new Thread(() -> {
            try {
                ApiClient.QuizDetail quiz = ApiClient.getQuiz(quizId);
                Platform.runLater(() -> render(quiz));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load result: " + e.getMessage()));
            }
        }).start();
    }
    @FXML
private void handleBack() {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/quizzes_list.fxml"));
        javafx.scene.Parent root = loader.load();
        javafx.stage.Stage stage = (javafx.stage.Stage) breakdownContainer.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to go back: " + e.getMessage());
    }
}

    private void render(ApiClient.QuizDetail quiz) {
        titleLabel.setText(quiz.title());
        scoreLabel.setText(String.format("Score: %.2f%%", quiz.score()));

        if (!quiz.resultsReleased()) {
            noticeLabel.setText("Detailed answer breakdown will appear once your lecturer releases results.");
            statusLabel.setText("");
            return;
        }

        noticeLabel.setText("");
        breakdownContainer.getChildren().clear();
        for (ApiClient.QuizQuestion q : quiz.questions()) {
            breakdownContainer.getChildren().add(buildQuestionResult(q));
        }
        statusLabel.setText("");
    }

    private VBox buildQuestionResult(ApiClient.QuizQuestion q) {
        Label questionText = new Label(q.questionText());
        questionText.getStyleClass().add("group-card-title");
        questionText.setWrapText(true);

        Label yourAnswer = new Label("Your answer: " + (q.selectedOption().isBlank() ? "No answer" : q.selectedOption()));
        Label correctAnswer = new Label("Correct answer: " + q.correctOption());
        yourAnswer.getStyleClass().add(q.isCorrect() ? "quiz-answer-correct" : "quiz-answer-wrong");
        correctAnswer.getStyleClass().add("topic-meta");

        VBox block = new VBox(6, questionText, yourAnswer, correctAnswer);
        block.getStyleClass().add("group-card");
        block.setPadding(new Insets(14, 16, 14, 16));
        return block;
    }
}