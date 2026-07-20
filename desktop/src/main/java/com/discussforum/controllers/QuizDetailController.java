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
import java.time.temporal.ChronoUnit;
import java.util.HashMap;
import java.util.Map;

public class QuizDetailController {

    @FXML private Label titleLabel;
    @FXML private Label subtitleLabel;
    @FXML private Label statusLabel;
    @FXML private Label countdownLabel;
    @FXML private VBox questionsContainer;
    @FXML private Button submitButton;
    @FXML private Label feedbackLabel;

    private int quizId;
    private int groupId;

    private long endTimeEpochMs;
    private long clockOffsetMs; // serverNow - clientNow, same idea as the web version
    private Timeline countdownTimeline;
    private boolean submitted = false;

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
        titleLabel.setText(quiz.get("title").getAsString());
        subtitleLabel.setText(quiz.get("group_name").getAsString() + " · "
            + quiz.get("duration").getAsInt() + " minutes");

        boolean isAttempted = quiz.get("is_attempted").getAsBoolean();
        boolean isActive = quiz.get("is_active").getAsBoolean();
        boolean resultsReleased = quiz.get("results_released").getAsBoolean();

        if (isAttempted) {
            if (resultsReleased && !quiz.get("score").isJsonNull()) {
                statusLabel.setText(String.format("Attempted — Score: %.2f%%", quiz.get("score").getAsDouble()));
            } else {
                statusLabel.setText("Attempted — results not yet released");
            }
            submitButton.setVisible(false);
            submitButton.setManaged(false);
            return;
        }

        if (!isActive) {
            statusLabel.setText("This quiz is not currently open for attempts.");
            submitButton.setVisible(false);
            submitButton.setManaged(false);
            return;
        }

        // Active and unattempted: render questions and start the countdown.
        statusLabel.setText("Available now");

        OffsetDateTime endTime = OffsetDateTime.parse(quiz.get("end_time").getAsString());
        OffsetDateTime serverTime = OffsetDateTime.parse(quiz.get("server_time").getAsString());
        this.endTimeEpochMs = endTime.toInstant().toEpochMilli();
        long serverNowMs = serverTime.toInstant().toEpochMilli();
        long clientNowMs = System.currentTimeMillis();
        this.clockOffsetMs = serverNowMs - clientNowMs;

        JsonArray questions = quiz.getAsJsonArray("questions");
        for (JsonElement el : questions) {
            questionsContainer.getChildren().add(buildQuestionCard(el.getAsJsonObject()));
        }

        startCountdown();
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
        countdownTimeline = new Timeline(new KeyFrame(Duration.seconds(1), e -> tickCountdown()));
        countdownTimeline.setCycleCount(Timeline.INDEFINITE);
        countdownTimeline.play();
        tickCountdown(); // show immediately, don't wait a full second
    }

    private void tickCountdown() {
        long serverNow = System.currentTimeMillis() + clockOffsetMs;
        long remainingMs = endTimeEpochMs - serverNow;

        if (remainingMs <= 0) {
            countdownLabel.setText("00:00");
            countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #d9302a;");
            if (countdownTimeline != null) countdownTimeline.stop();
            if (!submitted) {
                submitted = true;
                submitAnswers();
            }
            return;
        }

        long totalSeconds = remainingMs / 1000;
        long minutes = totalSeconds / 60;
        long seconds = totalSeconds % 60;
        countdownLabel.setText(String.format("%02d:%02d", minutes, seconds));

        if (remainingMs <= 10_000) {
            countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #d9302a;");
        } else {
            countdownLabel.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #1a7a45;");
        }
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
