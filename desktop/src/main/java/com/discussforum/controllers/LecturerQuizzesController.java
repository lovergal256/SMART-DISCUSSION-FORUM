package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.geometry.Pos;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;

import java.net.URL;
import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ResourceBundle;

public class LecturerQuizzesController implements Initializable {

    @FXML private Label userLabel;
    @FXML private Label statusLabel;
    @FXML private VBox quizzesList;
    @FXML private SidebarController sidebarController;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        userLabel.setText(ApiService.getCurrentUserName());
        if (sidebarController != null) {
            sidebarController.setActive("dashboard");
        }
        loadQuizzes();
    }

    private void loadQuizzes() {
        statusLabel.setText("Loading...");
        quizzesList.getChildren().clear();

        new Thread(() -> {
            try {
                JsonObject data = ApiService.get("/lecturer/quizzes");
                JsonArray quizzes = data.getAsJsonArray("quizzes");
                Platform.runLater(() -> {
                    if (quizzes.size() == 0) {
                        statusLabel.setText("You haven't created any quizzes yet.");
                        return;
                    }
                    statusLabel.setText(quizzes.size() + " quiz(zes)");
                    for (JsonElement el : quizzes) {
                        quizzesList.getChildren().add(createQuizCard(el.getAsJsonObject()));
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Error loading quizzes: " + e.getMessage()));
            }
        }).start();
    }

    private VBox createQuizCard(JsonObject quiz) {
        int quizId = quiz.get("id").getAsInt();
        String title = quiz.get("title").getAsString();
        String groupName = quiz.has("group_name") && !quiz.get("group_name").isJsonNull()
                ? quiz.get("group_name").getAsString() : "";
        int attemptCount = quiz.get("attempt_count").getAsInt();
        boolean released = quiz.get("results_released").getAsBoolean();

        String dueText;
        try {
            OffsetDateTime due = OffsetDateTime.parse(quiz.get("due").getAsString());
            dueText = due.format(DateTimeFormatter.ofPattern("d MMM yyyy, h:mm a"));
        } catch (Exception e) {
            dueText = quiz.get("due").getAsString();
        }

        VBox card = new VBox(8);
        card.setStyle("-fx-background-color: white; -fx-padding: 15; -fx-background-radius: 6; "
            + "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2); -fx-cursor: hand;");

        HBox row = new HBox(10);
        row.setAlignment(Pos.CENTER_LEFT);

        VBox info = new VBox(3);
        Label groupLabel = new Label(groupName);
        groupLabel.setStyle("-fx-text-fill: #0077b6; -fx-font-size: 11px; -fx-font-weight: bold;");
        Label titleLabel = new Label(title);
        titleLabel.setStyle("-fx-font-weight: bold; -fx-text-fill: #333; -fx-font-size: 14px;");
        Label dueLabel = new Label("Due " + dueText + " · " + quiz.get("duration").getAsInt() + " min · "
            + attemptCount + " attempt(s)");
        dueLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");
        info.getChildren().addAll(groupLabel, titleLabel, dueLabel);

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Label statusBadge = new Label(released ? "Released" : "Not released");
        statusBadge.setStyle("-fx-text-fill: " + (released ? "#1a7a45" : "#888") + "; -fx-font-size: 11px; "
            + "-fx-padding: 4 10; -fx-background-color: " + (released ? "#e6f4ea" : "#f0f0f0")
            + "; -fx-background-radius: 10;");

        Button reviewBtn = new Button("Review Quiz");
        reviewBtn.setStyle("-fx-background-color: #0077b6; -fx-text-fill: white; "
            + "-fx-padding: 6 14; -fx-background-radius: 4; -fx-cursor: hand;");
        reviewBtn.setOnAction(e -> openQuizReview(quizId));

        row.getChildren().addAll(info, spacer, statusBadge, reviewBtn);
        card.getChildren().add(row);
        card.setOnMouseClicked(e -> openQuizReview(quizId));
        return card;
    }

    private void openQuizReview(int quizId) {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/QuizReview.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            QuizReviewController controller = loader.getController();
            controller.loadQuiz(quizId);
            Stage stage = (Stage) quizzesList.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void handleLogout() {
        ApiService.logout();
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Login.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) quizzesList.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}