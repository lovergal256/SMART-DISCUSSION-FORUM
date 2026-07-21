package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonObject;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;

public class QuizReviewController {

    @FXML private Label titleLabel;
    @FXML private Label subtitleLabel;
    @FXML private Label startsLabel;
    @FXML private Label endsLabel;
    @FXML private Label durationLabel;
    @FXML private Label attemptsLabel;
    @FXML private Label averageScoreLabel;
    @FXML private Label releasedLabel;
    @FXML private Button releaseButton;
    @FXML private Label feedbackLabel;

    private int quizId;
    private static final DateTimeFormatter FMT = DateTimeFormatter.ofPattern("MMM d, yyyy · hh:mm a");

    public void loadQuiz(int quizId) {
        this.quizId = quizId;
        titleLabel.setText("Loading...");

        new Thread(() -> {
            try {
                JsonObject quiz = ApiService.get("/quizzes/" + quizId + "/review");
                Platform.runLater(() -> render(quiz));
            } catch (Exception e) {
                Platform.runLater(() -> titleLabel.setText("Error loading quiz: " + e.getMessage()));
            }
        }).start();
    }

    private void render(JsonObject quiz) {
        titleLabel.setText(quiz.get("title").getAsString());
        subtitleLabel.setText(quiz.get("group_name").getAsString() + " · "
            + quiz.get("duration").getAsInt() + " minutes");

        try {
            OffsetDateTime start = OffsetDateTime.parse(quiz.get("start_time").getAsString());
            OffsetDateTime end = OffsetDateTime.parse(quiz.get("end_time").getAsString());
            startsLabel.setText("Starts: " + start.format(FMT));
            endsLabel.setText("Ends: " + end.format(FMT));
        } catch (Exception e) {
            startsLabel.setText("Starts: " + quiz.get("start_time").getAsString());
            endsLabel.setText("Ends: " + quiz.get("end_time").getAsString());
        }
        durationLabel.setText("Duration: " + quiz.get("duration").getAsInt() + " minutes");

        attemptsLabel.setText("Attempts: " + quiz.get("attempt_count").getAsInt());

        if (!quiz.get("average_score").isJsonNull()) {
            averageScoreLabel.setText(String.format("Average score: %.2f%%", quiz.get("average_score").getAsDouble()));
        } else {
            averageScoreLabel.setText("Average score: —");
        }

        boolean released = quiz.get("results_released").getAsBoolean();
        releasedLabel.setText("Results released: " + (released ? "Yes" : "No"));
        releaseButton.setVisible(!released);
        releaseButton.setManaged(!released);
    }

    @FXML
    private void handleReleaseResults() {
        releaseButton.setDisable(true);
        new Thread(() -> {
            try {
                JsonObject response = ApiService.post("/quizzes/" + quizId + "/release-results", null);
                Platform.runLater(() -> {
                    if (response.has("results_released") && response.get("results_released").getAsBoolean()) {
                        feedbackLabel.setStyle("-fx-text-fill: green;");
                        feedbackLabel.setText("Results released to students.");
                        releasedLabel.setText("Results released: Yes");
                        releaseButton.setVisible(false);
                        releaseButton.setManaged(false);
                    } else {
                        feedbackLabel.setStyle("-fx-text-fill: red;");
                        feedbackLabel.setText(response.has("message")
                            ? response.get("message").getAsString() : "Failed to release results.");
                        releaseButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    feedbackLabel.setStyle("-fx-text-fill: red;");
                    feedbackLabel.setText("Error: " + e.getMessage());
                    releaseButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/LecturerQuizzes.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) titleLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}