package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;
import javafx.util.Duration;

import java.time.OffsetDateTime;
import java.util.HashMap;
import java.util.Map;

public class QuizDetailController {

    @FXML private Label titleLabel;
    @FXML private Label subtitleLabel;
    @FXML private Label statusLabel;
    @FXML private Label countdownCaptionLabel;
    @FXML private Label countdownLabel;
    @FXML private VBox questionsContainer;
    @FXML private Button submitButton;
    @FXML private Label feedbackLabel;

    private int quizId;
    private int groupId;

    private long startTimeEpochMs;
    private long endTimeEpochMs;
    private long clockOffsetMs; // serverNow - clientNow
    private Timeline countdownTimeline;
    private boolean submitted = false;

    private enum Phase { PRE_START, ACTIVE, ENDED, ATTEMPTED }
    private Phase phase;

    // Submit a couple seconds before the client-displayed countdown hits zero,
    // so the request lands before the server's own deadline check.
    private static final long SUBMIT_BUFFER_MS = 2000;

    private final Map<Integer, ToggleGroup> answerGroups = new HashMap<>();

    public void loadQuiz(int quizId, int groupId) {
        this.quizId = quizId;
        this.groupId = groupId;
        statusLabel.setText("Loading...");

        new Thread(() -> {
            try {
                JsonObject quiz = ApiService.get("/quizzes/" + quizId);
                javafx.application.Platform.runLater(() -> render(quiz));
            } catch (Exception e) {
                javafx.application.Platform.runLater(() ->
                    statusLabel.setText("Error loading quiz: " + e.getMessage()));
            }
        }).start();
    }

    private void render(JsonObject quiz) {
        if (countdownTimeline != null) {
            countdownTimeline.stop();
        }
        questionsContainer.getChildren().clear();
        answerGroups.clear();
        feedbackLabel.setText("");

        titleLabel.setText(quiz.get("title").getAsString());
        subtitleLabel.setText(quiz.get("group_name").getAsString() + " · "
            + quiz.get("duration").getAsInt() + " minutes");

        boolean isAttempted = quiz.get("is_attempted").getAsBoolean();
        boolean resultsReleased = quiz.get("results_released").getAsBoolean();

        OffsetDateTime startTime = OffsetDateTime.parse(quiz.get("start_time").getAsString());
        OffsetDateTime endTime = OffsetDateTime.parse(quiz.get("end_time").getAsString());
        OffsetDateTime serverTime = OffsetDateTime.parse(quiz.get("server_time").getAsString());

        this.startTimeEpochMs = startTime.toInstant().toEpochMilli();
        this.endTimeEpochMs = endTime.toInstant().toEpochMilli();
        long serverNowMs = serverTime.toInstant().toEpochMilli();
        long clientNowMs = System.currentTimeMillis();
        this.clockOffsetMs = serverNowMs - clientNowMs;

        if (isAttempted) {
            phase = Phase.ATTEMPTED;
            if (resultsReleased && !quiz.get("score").isJsonNull()) {
                statusLabel.setText(String.format("Attempted — Score: %.2f%%", quiz.get("score").getAsDouble()));
            } else {
                statusLabel.setText("Attempted — results not yet released");
            }
            hideCountdown();
            submitButton.setVisible(false);
            submitButton.setManaged(false);
            return;
        }

        long serverNow = System.currentTimeMillis() + clockOffsetMs;

        if (serverNow < startTimeEpochMs) {
            phase = Phase.PRE_START;
            statusLabel.setText("This quiz hasn't started yet. It will open automatically below.");
            submitButton.setVisible(false);
            submitButton.setManaged(false);
            showCountdown("Starts in");
            startCountdown();
            return;
        }

        if (serverNow > endTimeEpochMs) {
            phase = Phase.ENDED;
            statusLabel.setText("This quiz window has closed.");
            hideCountdown();
            submitButton.setVisible(false);
            submitButton.setManaged(false);
            return;
        }

        // Active and unattempted: render questions and start the end-of-quiz countdown.
        phase = Phase.ACTIVE;
        statusLabel.setText("Available now");
        submitted = false;
        submitButton.setVisible(true);
        submitButton.setManaged(true);
        submitButton.setDisable(false);

        JsonArray questions = quiz.getAsJsonArray("questions");
        for (JsonElement el : questions) {
            questionsContainer.getChildren().add(buildQuestionCard(el.getAsJsonObject()));
        }

        showCountdown("Time remaining");
        startCountdown();
    }

    private void showCountdown(String caption) {
        countdownCaptionLabel.setText(caption);
        countdownCaptionLabel.setVisible(true);
        countdownCaptionLabel.setManaged(true);
        countdownLabel.setVisible(true);
        countdownLabel.setManaged(true);
    }

    private void hideCountdown() {
        countdownCaptionLabel.setVisible(false);
        countdownCaptionLabel.setManaged(false);
        countdownLabel.setVisible(false);
        countdownLabel.setManaged(false);
    }

    private VBox buildQuestionCard(JsonObject question) {
        int questionId = question.get("id").getAsInt();
        String text = question.get("text").getAsString();
        int marks = question.get("marks").getAsInt();

        Label header = new Label("Question (" + marks + " marks)");
        header.setStyle("-fx-font-weight: bold; -fx-text-fill: #0077b6;");
        Label body = new Label(text);
        body.setWrapText(true);

        ToggleGroup group = new ToggleGroup();
        answerGroups.put(questionId, group);

        VBox card = new VBox(8, header, body);
        card.setStyle("-fx-padding: 14; -fx-border-color: #b0c4d8; -fx-border-radius: 6; "
            + "-fx-background-color: white; -fx-background-radius: 6;");

        addOption(card, group, "A", question.get("option_a"));
        addOption(card, group, "B", question.get("option_b"));
        addOption(card, group, "C", question.has("option_c") ? question.get("option_c") : null);
        addOption(card, group, "D", question.has("option_d") ? question.get("option_d") : null);

        return card;
    }

    private void addOption(VBox card, ToggleGroup group, String letter, JsonElement value) {
        if (value == null || value.isJsonNull()) return;
        RadioButton rb = new RadioButton(letter + ". " + value.getAsString());
        rb.setUserData(letter);
        rb.setToggleGroup(group);
        card.getChildren().add(rb);
    }
    private void startCountdown() {
        countdownTimeline = new Timeline(new KeyFrame(Duration.seconds(1), e -> tick()));
        countdownTimeline.setCycleCount(Timeline.INDEFINITE);
        countdownTimeline.play();
        tick(); // show immediately, don't wait a full second
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
            // Start time reached — pop straight into the live quiz without any
            // back-and-forth, by re-fetching authoritative state from the server.
            if (countdownTimeline != null) countdownTimeline.stop();
            countdownLabel.setText("00:00");
            statusLabel.setText("Opening quiz...");
            loadQuiz(quizId, groupId);
            return;
        }

        countdownLabel.setText(formatDuration(remainingMs));
        countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #0077b6;");
    }

    private void tickActive() {
        long serverNow = System.currentTimeMillis() + clockOffsetMs;
        long remainingMs = endTimeEpochMs - serverNow;

        if (remainingMs <= SUBMIT_BUFFER_MS) {
            countdownLabel.setText("00:00");
            countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #d9302a;");
            if (countdownTimeline != null) countdownTimeline.stop();
            if (!submitted) {
                submitted = true;
                submitAnswers();
            }
            return;
        }

        countdownLabel.setText(formatDuration(remainingMs));
        if (remainingMs <= 10_000) {
            countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #d9302a;");
        } else {
            countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #1a7a45;");
        }
    }

    private String formatDuration(long ms) {
        long totalSeconds = ms / 1000;
        long minutes = totalSeconds / 60;
        long seconds = totalSeconds % 60;
        return String.format("%02d:%02d", minutes, seconds);
    }

    @FXML
    private void handleSubmit() {
        if (submitted) return;
        submitted = true;
        if (countdownTimeline != null) countdownTimeline.stop();
        submitAnswers();
    }

    private void submitAnswers() {
        submitButton.setDisable(true);

        JsonObject answers = new JsonObject();
        for (Map.Entry<Integer, ToggleGroup> entry : answerGroups.entrySet()) {
            Toggle selected = entry.getValue().getSelectedToggle();
            if (selected != null) {
                answers.addProperty(String.valueOf(entry.getKey()), (String) selected.getUserData());
            }
        }

        JsonObject body = new JsonObject();
        body.add("answers", answers);

        new Thread(() -> {
            try {
                JsonObject response = ApiService.post("/quizzes/" + quizId + "/attempts", body);
                javafx.application.Platform.runLater(() -> {
                    if (response.has("score")) {
                        feedbackLabel.setStyle("-fx-text-fill: green;");
                        feedbackLabel.setText("Submitted successfully.");
                    } else {
                        feedbackLabel.setStyle("-fx-text-fill: red;");
                        feedbackLabel.setText(response.has("message")
                            ? response.get("message").getAsString()
                            : "Submission failed.");
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() -> {
                    feedbackLabel.setStyle("-fx-text-fill: red;");
                    feedbackLabel.setText("Error: " + e.getMessage());
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        if (countdownTimeline != null) countdownTimeline.stop();
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Quizzes.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) titleLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
