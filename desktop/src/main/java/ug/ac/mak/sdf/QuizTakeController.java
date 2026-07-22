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
import java.util.Map;

public class QuizTakeController {

    @FXML private Label titleLabel;
    @FXML private Label timerLabel;
    @FXML private VBox questionsContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    private String quizId;
    private final Map<String, ToggleGroup> answerGroups = new HashMap<>();
    private Timeline countdown;

    private long startTimeEpochMs;
    private long endTimeEpochMs;
    private long clockOffsetMs;
    private boolean submitted = false;

    private enum Phase { PRE_START, ACTIVE, ENDED, ATTEMPTED }
    private Phase phase;

    // Fire submission slightly before the client's displayed countdown hits zero,
    // so the request lands before the server's own deadline check.
    private static final long SUBMIT_BUFFER_MS = 2000;

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
        if (countdown != null) {
            countdown.stop();
        }
        questionsContainer.getChildren().clear();
        answerGroups.clear();

        titleLabel.setText(quiz.title());

        if (quiz.attempted()) {
            phase = Phase.ATTEMPTED;
            openResult();
            return;
        }

        long startMs, endMs, serverMs;
        try {
            startMs = OffsetDateTime.parse(quiz.startTime()).toInstant().toEpochMilli();
        } catch (Exception ex) {
            startMs = 0;
        }
        try {
            endMs = OffsetDateTime.parse(quiz.endTime()).toInstant().toEpochMilli();
        } catch (Exception ex) {
            endMs = System.currentTimeMillis() + 10 * 60_000;
        }
        try {
            serverMs = OffsetDateTime.parse(quiz.serverTime()).toInstant().toEpochMilli();
        } catch (Exception ex) {
            serverMs = System.currentTimeMillis();
        }

        this.startTimeEpochMs = startMs;
        this.endTimeEpochMs = endMs;
        this.clockOffsetMs = serverMs - System.currentTimeMillis();

        long serverNow = System.currentTimeMillis() + clockOffsetMs;

        if (serverNow < startTimeEpochMs) {
            phase = Phase.PRE_START;
            statusLabel.setText("This quiz hasn't started yet. It will open automatically.");
            startCountdown();
            return;
        }

        if (!quiz.active() || serverNow > endTimeEpochMs) {
            phase = Phase.ENDED;
            statusLabel.setText("This quiz is not currently active.");
            return;
        }

        phase = Phase.ACTIVE;
        submitted = false;
        statusLabel.setText("");

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
        addOption(optionsBox, group, "A", q.optionA());
        addOption(optionsBox, group, "B", q.optionB());
        if (q.optionC() != null && !q.optionC().isBlank()) {
            addOption(optionsBox, group, "C", q.optionC());
        }
        if (q.optionD() != null && !q.optionD().isBlank()) {
            addOption(optionsBox, group, "D", q.optionD());
        }

        VBox block = new VBox(10, questionText, optionsBox);
        block.getStyleClass().add("group-card");
        block.setPadding(new javafx.geometry.Insets(14, 16, 14, 16));
        return block;
    }

    private void addOption(VBox container, ToggleGroup group, String letter, String text) {
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
        if (phase == Phase.PRE_START) {
            tickPreStart();
        } else if (phase == Phase.ACTIVE) {
            tickActive();
        }
    }

    private void tickPreStart() {
        long serverNow = System.currentTimeMillis() + clockOffsetMs;
        long remainingMs = startTimeEpochMs - serverNow;

        if (remainingMs <= 0) {
            if (countdown != null) countdown.stop();
            timerLabel.setText("Opening...");
            statusLabel.setText("Opening quiz...");
            loadQuiz(); // re-fetch authoritative state from the server
            return;
        }

        timerLabel.setText("Starts in: " + formatDuration(remainingMs));
    }

    private void tickActive() {
        long serverNow = System.currentTimeMillis() + clockOffsetMs;
        long remainingMs = endTimeEpochMs - serverNow;

        if (remainingMs <= SUBMIT_BUFFER_MS) {
            timerLabel.setText("Time's up!");
            if (countdown != null) countdown.stop();
            if (!submitted) {
                submitQuiz(true);
            }
            return;
        }

        timerLabel.setText("Time left: " + formatDuration(remainingMs));
    }

    private String formatDuration(long ms) {
        long totalSeconds = ms / 1000;
        long mins = totalSeconds / 60;
        long secs = totalSeconds % 60;
        return String.format("%02d:%02d", mins, secs);
    }

    @FXML
    private void handleSubmit() {
        if (phase != Phase.ACTIVE) {
            statusLabel.setText("You can only submit while the quiz is active.");
            return;
        }
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
                ApiClient.submitQuizAttempt(quizId, answers);
                Platform.runLater(this::openResult);
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