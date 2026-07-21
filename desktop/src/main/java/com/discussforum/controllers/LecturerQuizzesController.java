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

    @FXML private VBox quizzesList;

    private static final DateTimeFormatter FMT = DateTimeFormatter.ofPattern("d MMM yyyy, h:mm a");


    private void loadQuizzes() {
        new Thread(() -> {
            try {
                JsonObject data = ApiService.get("/lecturer/quizzes");
                Platform.runLater(() -> render(data.getAsJsonArray("quizzes")));
            } catch (Exception e) {
                Platform.runLater(() -> {
                    Label error = new Label("Error loading quizzes: " + e.getMessage());
                    error.setStyle("-fx-text-fill: red;");
                    quizzesList.getChildren().add(error);
                });
            }
        }).start();
    }

    private void render(JsonArray quizzes) {
        quizzesList.getChildren().clear();

        if (quizzes.size() == 0) {
            Label empty = new Label("You haven't created any quizzes yet.");
            empty.setStyle("-fx-text-fill: #888; -fx-font-size: 12px;");
            quizzesList.getChildren().add(empty);
            return;
        }

        for (JsonElement el : quizzes) {
            quizzesList.getChildren().add(createQuizCard(el.getAsJsonObject()));
        }
    }

    private HBox createQuizCard(JsonObject quiz) {
        int quizId = quiz.get("id").getAsInt();

        HBox row = new HBox(12);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setStyle("-fx-background-color: white; -fx-padding: 16; -fx-background-radius: 6; "
            + "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2);");

        VBox info = new VBox(3);
        Label title = new Label(quiz.get("title").getAsString());
        title.setStyle("-fx-font-weight: bold; -fx-text-fill: #333; -fx-font-size: 13px;");

        String dueText;
        try {
            OffsetDateTime due = OffsetDateTime.parse(quiz.get("due").getAsString());
            dueText = due.format(FMT);
        } catch (Exception e) {
            dueText = quiz.get("due").getAsString();
        }

        Label meta = new Label(quiz.get("group_name").getAsString() + " · "
            + quiz.get("duration").getAsInt() + " min · Due " + dueText);
        meta.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");

        Label attempts = new Label(quiz.get("attempt_count").getAsInt() + " attempts · Results "
            + (quiz.get("results_released").getAsBoolean() ? "released" : "not released"));
        attempts.setStyle("-fx-text-fill: #666; -fx-font-size: 11px;");

        info.getChildren().addAll(title, meta, attempts);

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Button reviewBtn = new Button("Review Quiz");
        reviewBtn.setStyle("-fx-background-color: #0077b6; -fx-text-fill: white; "
            + "-fx-padding: 6 14; -fx-background-radius: 4; -fx-cursor: hand; -fx-font-size: 11px;");
        reviewBtn.setOnAction(e -> openQuizReview(quizId));

        row.getChildren().addAll(info, spacer, reviewBtn);
        return row;
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

    @FXML private SidebarController sidebarController;

@Override
public void initialize(URL location, ResourceBundle resources) {
    sidebarController.setActive("dashboard");
    loadQuizzes();
}
}