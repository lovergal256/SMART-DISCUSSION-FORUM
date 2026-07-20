package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.stage.Stage;

import java.net.URL;
import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ResourceBundle;

public class QuizzesController implements Initializable {

    @FXML private Label userLabel;
    @FXML private Label statusLabel;
    @FXML private VBox quizzesList;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        userLabel.setText(ApiService.getCurrentUserName());
        loadQuizzes();
    }

    private void loadQuizzes() {
        statusLabel.setText("Loading...");
        quizzesList.getChildren().clear();

        new Thread(() -> {
            try {
                JsonArray quizzes = ApiService.getArray("/quizzes");
                Platform.runLater(() -> {
                    if (quizzes.size() == 0) {
                        statusLabel.setText("No quizzes yet.");
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
        boolean attempted = quiz.get("attempted").getAsBoolean();
        String groupName = quiz.has("group_name") && !quiz.get("group_name").isJsonNull()
                ? quiz.get("group_name").getAsString() : "";
        int groupId = quiz.has("group_id") ? quiz.get("group_id").getAsInt() : -1;

        String dueText;
        try {
            OffsetDateTime start = OffsetDateTime.parse(quiz.get("start_time").getAsString());
            dueText = start.format(DateTimeFormatter.ofPattern("d MMM yyyy, h:mm a"));
        } catch (Exception e) {
            dueText = quiz.get("start_time").getAsString();
        }

        VBox card = new VBox(8);
        card.setStyle("-fx-background-color: white; -fx-padding: 15; -fx-background-radius: 6; "
            + "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2);");

        HBox row = new HBox(10);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        VBox info = new VBox(3);
        Label groupLabel = new Label(groupName);
        groupLabel.setStyle("-fx-text-fill: #0077b6; -fx-font-size: 11px; -fx-font-weight: bold;");
        Label titleLabel = new Label(title);
        titleLabel.setStyle("-fx-font-weight: bold; -fx-text-fill: #333; -fx-font-size: 14px;");
        Label dueLabel = new Label("Due " + dueText + " · " + quiz.get("duration").getAsInt() + " min");
        dueLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");
        info.getChildren().addAll(groupLabel, titleLabel, dueLabel);

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        if (attempted) {
            Label attemptedLabel = new Label("Attempted");
            attemptedLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px; -fx-padding: 4 10; "
                + "-fx-background-color: #f0f0f0; -fx-background-radius: 10;");
            row.getChildren().addAll(info, spacer, attemptedLabel);
        } else {
            Button openButton = new Button("Open");
            openButton.setStyle("-fx-background-color: #0077b6; -fx-text-fill: white; "
                + "-fx-padding: 6 14; -fx-background-radius: 4; -fx-cursor: hand;");
            openButton.setOnAction(e -> openQuiz(quizId, groupId));
            row.getChildren().addAll(info, spacer, openButton);
        }

        card.getChildren().add(row);
        return card;
    }

    private void openQuiz(int quizId, int groupId) {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/QuizDetail.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            QuizDetailController controller = loader.getController();
            controller.loadQuiz(quizId, groupId);
            Stage stage = (Stage) quizzesList.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void goToMyGroups() {
        navigateToGroups(false);
    }

    @FXML
    private void goToDiscoverGroups() {
        navigateToGroups(true);
    }

    private void navigateToGroups(boolean discover) {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Groups.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            GroupsController controller = loader.getController();
            if (discover) {
                controller.loadDiscoverGroups();
            }
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