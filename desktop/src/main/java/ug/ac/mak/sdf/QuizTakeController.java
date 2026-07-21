package ug.ac.mak.sdf;

import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.RadioButton;
import javafx.scene.control.ToggleGroup;
import javafx.scene.layout.VBox;
import javafx.util.Duration;

import java.time.OffsetDateTime;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class QuizTakeController {

    @FXML private Label titleLabel;
    @FXML private Label timerLabel;
    @FXML private VBox questionsContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    private String quizId;
    private final Map<String, ToggleGroup> answerGroups = new HashMap<>();
    private final Map<String, String> optionLetterByToggle = new HashMap<>();
    private Timeline countdown;
    private OffsetDateTime endTime;
    private boolean submitted = false;

    public void setQuizId(String quizId) {
        this.quizId = quizId;
        if (sidebarController != null) {
            sidebarController.setActiveItem("quizzes");
        }
        loadQuiz();
    }

    private void loadQuiz() {
        statusLabel.setText("Loading quiz...");

        new Thread(() -> {
            try {
                ApiClient.QuizDetail quiz = ApiClient.getQuiz(quizId);
                Platform.runLater(() -> renderQuiz(quiz));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load quiz: " + e.getMessage()));
            }
        }).start();
    }

        private void renderQuiz(ApiClient.QuizDetail quiz) {
        titleLabel.setText(quiz.title());

        if (quiz.attempted()) {
            openResult();
            return;
        }

        if (!quiz.active()) {
            statusLabel.setText("This quiz is not currently active.");
            return;
        }

        try {
            endTime = OffsetDateTime.parse(quiz.endTime());
        } catch (Exception ex) {
            endTime = OffsetDateTime.now().plusMinutes(10);
        }

        questionsContainer.getChildren().clear();
        for (ApiClient.QuizQuestion q : quiz.questions()) {
            questionsContainer.getChildren().add(buildQuestionBlock(q));
        }

        startCountdown();
    }

    private VBox buildQuestionBlock(ApiClient.QuizQuestion q) {
        Label questionText = new Label(q.questionText() + "  (" + q.marks() + " mark" + (q.marks() == 1 ? "" : "s") + ")");
        questionText.getStyleClass().add("group-card-title");
        questionText.setWrapText(true);

        ToggleGroup group = new ToggleGroup();
        answerGroups.put(q.id(), group);

        VBox optionsBox = new VBox(6);
        addOption(optionsBox, group, q.id(), "A", q.optionA());
        addOption(optionsBox, group, q.id(), "B", q.optionB());
        if (q.optionC() != null && !q.optionC().isBlank()) {
            addOption(optionsBox, group, q.id(), "C", q.optionC());
        }
        if (q.optionD() != null && !q.optionD().isBlank()) {
            addOption(optionsBox, group, q.id(), "D", q.optionD());
        }

        VBox block = new VBox(10, questionText, optionsBox);
        block.getStyleClass().add("group-card");
        block.setPadding(new javafx.geometry.Insets(14, 16, 14, 16));
        return block;
    }

    private void addOption(VBox container, ToggleGroup group, String questionId, String letter, String text) {
        RadioButton rb = new RadioButton(letter + ". " + text);
        rb.setToggleGroup(group);
        rb.setUserData(letter);
        rb.setWrapText(true);
        container.getChildren().add(rb);
    }

    private void startCountdown() {
        countdown = new Timeline(new KeyFrame(Duration.seconds(1), e -> tick()));
        countdown.setCycleCount(Timeline.INDEFINITE);
        countdown.play();
        tick();
    }

    private void tick() {
        long secondsLeft = java.time.Duration.between(OffsetDateTime.now(), endTime).getSeconds();
        if (secondsLeft <= 0) {
            timerLabel.setText("Time's up!");
            if (!submitted) {
                submitQuiz(true);
            }
            if (countdown != null) countdown.stop();
            return;
        }
        long mins = secondsLeft / 60;
        long secs = secondsLeft % 60;
        timerLabel.setText(String.format("Time left: %02d:%02d", mins, secs));
    }

    @FXML
    private void handleSubmit() {
        submitQuiz(false);
    }

    private void submitQuiz(boolean auto) {
        if (submitted) return;
        submitted = true;
        if (countdown != null) countdown.stop();

        Map<String, String> answers = new HashMap<>();
        for (Map.Entry<String, ToggleGroup> entry : answerGroups.entrySet()) {
            RadioButton selected = (RadioButton) entry.getValue().getSelectedToggle();
            if (selected != null) {
                answers.put(entry.getKey(), (String) selected.getUserData());
            }
        }

        statusLabel.setText(auto ? "Time's up — submitting..." : "Submitting...");

        new Thread(() -> {
            try {
                double score = ApiClient.submitQuizAttempt(quizId, answers);
                Platform.runLater(() -> openResult());
            } catch (Exception e) {
                Platform.runLater(() -> {
                    statusLabel.setText("Failed to submit: " + e.getMessage());
                    submitted = false;
                });
            }
        }).start();
    }
    @FXML
private void handleBack() {
    if (countdown != null) countdown.stop();
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/quizzes_list.fxml"));
        javafx.scene.Parent root = loader.load();
        javafx.stage.Stage stage = (javafx.stage.Stage) questionsContainer.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to go back: " + e.getMessage());
    }
}

    private void openResult() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/quiz_result.fxml"));
            javafx.scene.Parent root = loader.load();
            QuizResultController controller = loader.getController();
            controller.setQuizId(quizId);
            javafx.stage.Stage stage = (javafx.stage.Stage) questionsContainer.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Submitted, but failed to load result: " + e.getMessage());
        }
    }
}