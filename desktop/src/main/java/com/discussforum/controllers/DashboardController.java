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

public class DashboardController implements Initializable {

    @FXML private Label userLabel;
    @FXML private Label welcomeLabel;
    @FXML private HBox statsRow;
    @FXML private VBox discussionsList;
    @FXML private VBox quizzesList;
    @FXML private VBox recommendationsList;
    @FXML private SidebarController sidebarController;

    @Override
    public void initialize(URL location, ResourceBundle resources) {
        String name = ApiService.getCurrentUserName();
        userLabel.setText(name);
        welcomeLabel.setText("Welcome back, " + name + "!");
        sidebarController.setActive("dashboard");
        loadDashboard();
    }

    private void loadDashboard() {
        new Thread(() -> {
            try {
                JsonObject data = ApiService.get("/lecturer/dashboard");
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> {
                    Label error = new Label("Error loading dashboard: " + e.getMessage());
                    error.setStyle("-fx-text-fill: red;");
                    statsRow.getChildren().add(error);
                });
            }
        }).start();
    }

    private void render(JsonObject data) {
        statsRow.getChildren().clear();
        discussionsList.getChildren().clear();
        quizzesList.getChildren().clear();
        recommendationsList.getChildren().clear();

        JsonArray stats = data.getAsJsonArray("stats");
        for (int i = 0; i < stats.size(); i++) {
            JsonObject stat = stats.get(i).getAsJsonObject();
            statsRow.getChildren().add(createStatCard(stat));
        }

        JsonArray discussions = data.getAsJsonArray("discussions");
        if (discussions.size() == 0) {
            discussionsList.getChildren().add(emptyLabel("No discussions yet."));
        } else {
            for (JsonElement el : discussions) {
                discussionsList.getChildren().add(createDiscussionRow(el.getAsJsonObject()));
            }
        }

        JsonArray quizzes = data.getAsJsonArray("quizzes");
        if (quizzes.size() == 0) {
            quizzesList.getChildren().add(emptyLabel("No quizzes yet."));
        } else {
            for (JsonElement el : quizzes) {
                quizzesList.getChildren().add(createQuizRow(el.getAsJsonObject()));
            }
        }

        JsonArray recommendations = data.getAsJsonArray("recommendations");
        for (JsonElement el : recommendations) {
            recommendationsList.getChildren().add(createRecommendationRow(el.getAsJsonObject()));
        }
    }

    private Label emptyLabel(String text) {
        Label l = new Label(text);
        l.setStyle("-fx-text-fill: #888; -fx-font-size: 12px;");
        return l;
    }

    private VBox createStatCard(JsonObject stat) {
        String label = stat.get("label").getAsString();

        VBox card = new VBox(4);
        card.setStyle("-fx-background-color: white; -fx-padding: 16; -fx-background-radius: 6; "
            + "-fx-effect: dropshadow(gaussian, rgba(0,0,0,0.08), 6, 0, 0, 2); -fx-cursor: hand;");
        card.setPrefWidth(180);

        Label icon = new Label(stat.get("icon").getAsString());
        icon.setStyle("-fx-font-size: 18px;");
        Label value = new Label(String.valueOf(stat.get("value").getAsInt()));
        value.setStyle("-fx-font-size: 22px; -fx-font-weight: bold; -fx-text-fill: #222;");
        Label labelText = new Label(label);
        labelText.setStyle("-fx-text-fill: #555; -fx-font-size: 12px;");
        Label change = new Label("↑ " + stat.get("change").getAsString());
        change.setStyle("-fx-text-fill: #1a7a45; -fx-font-size: 11px;");

        card.getChildren().addAll(icon, value, labelText, change);

        if (label.equals("Teaching Groups")) {
            card.setOnMouseClicked(e -> navigateTo("/com/discussforum/views/Groups.fxml"));
        } else if (label.equals("Quizzes")) {
            card.setOnMouseClicked(e -> navigateTo("/com/discussforum/views/LecturerQuizzes.fxml"));
        }

        return card;
    }

    private HBox createDiscussionRow(JsonObject discussion) {
        HBox row = new HBox(6);
        VBox info = new VBox(2);
        Label title = new Label(discussion.get("title").getAsString());
        title.setStyle("-fx-font-weight: bold; -fx-text-fill: #333; -fx-font-size: 12px;");
        Label meta = new Label(discussion.get("posted_at").getAsString() + " · "
            + discussion.get("replies").getAsInt() + " replies");
        meta.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");
        info.getChildren().addAll(title, meta);
        row.getChildren().add(info);
        return row;
    }

    private HBox createQuizRow(JsonObject quiz) {
        int quizId = quiz.get("id").getAsInt();
        String title = quiz.get("title").getAsString();

        String dueText;
        try {
            OffsetDateTime due = OffsetDateTime.parse(quiz.get("due").getAsString());
            dueText = due.format(DateTimeFormatter.ofPattern("d MMM yyyy, h:mm a"));
        } catch (Exception e) {
            dueText = quiz.get("due").getAsString();
        }

        HBox row = new HBox(10);
        row.setAlignment(Pos.CENTER_LEFT);

        VBox info = new VBox(2);
        Label titleLabel = new Label(title);
        titleLabel.setStyle("-fx-font-weight: bold; -fx-text-fill: #333; -fx-font-size: 12px;");
        Label dueLabel = new Label("Duration " + quiz.get("duration").getAsInt() + " min · Due " + dueText);
        dueLabel.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");
        info.getChildren().addAll(titleLabel, dueLabel);

        Region spacer = new Region();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        Button reviewBtn = new Button("Review Quiz");
        reviewBtn.setStyle("-fx-background-color: #0077b6; -fx-text-fill: white; "
            + "-fx-padding: 5 12; -fx-background-radius: 4; -fx-cursor: hand; -fx-font-size: 11px;");
        reviewBtn.setOnAction(e -> openQuizReview(quizId));

        row.getChildren().addAll(info, spacer, reviewBtn);
        return row;
    }

    private VBox createRecommendationRow(JsonObject rec) {
        VBox box = new VBox(2);
        Label title = new Label(rec.get("icon").getAsString() + " " + rec.get("title").getAsString());
        title.setStyle("-fx-font-weight: bold; -fx-text-fill: #333; -fx-font-size: 12px;");
        Label subtitle = new Label(rec.get("subtitle").getAsString());
        subtitle.setStyle("-fx-text-fill: #888; -fx-font-size: 11px;");
        box.getChildren().addAll(title, subtitle);
        return box;
    }

    private void openQuizReview(int quizId) {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/QuizReview.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            QuizReviewController controller = loader.getController();
            controller.loadQuiz(quizId);
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void goToMyGroups() {
        navigateTo("/com/discussforum/views/Groups.fxml");
    }

    private void navigateTo(String fxml) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxml));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) userLabel.getScene().getWindow();
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
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}