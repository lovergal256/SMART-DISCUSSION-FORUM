package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;

public class PerformanceController {

    @FXML private VBox overallPanel;
    @FXML private VBox participationPanel;
    @FXML private VBox quizPerfPanel;
    @FXML private VBox marksPanel;
    @FXML private VBox recentQuizzesPanel;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("performance");
        }
        loadPerformance();
    }

    private void loadPerformance() {
        statusLabel.setText("Loading performance...");

        new Thread(() -> {
            try {
                ApiClient.Performance perf = ApiClient.getPerformance();
                Platform.runLater(() -> {
                    buildOverallPanel(perf);
                    buildParticipationPanel(perf);
                    buildQuizPerfPanel(perf);
                    buildMarksPanel(perf);
                    buildRecentQuizzesPanel(perf.recentQuizzes());
                    statusLabel.setText("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load performance: " + e.getMessage()));
            }
        }).start();
    }

    private Label panelHeader(String icon, String title) {
        Label header = new Label(icon + "  " + title);
        header.getStyleClass().add("panel-title");
        return header;
    }

    private HBox statRow(String label, String value) {
        Label l = new Label(label);
        l.getStyleClass().add("perf-stat-label");
        HBox.setHgrow(l, Priority.ALWAYS);

        Label v = new Label(value);
        v.getStyleClass().add("perf-stat-value");

        HBox row = new HBox(10, l, v);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.getStyleClass().add("perf-stat-row");
        return row;
    }

    private void buildOverallPanel(ApiClient.Performance p) {
        overallPanel.getChildren().clear();
        overallPanel.getChildren().add(panelHeader("🏅", "Overall Performance"));

        HBox row = new HBox(40);
        row.getChildren().add(labeledStat("Overall Score", p.overallMarks() + "%"));
        row.getChildren().add(labeledStat("Grade", p.grade()));
        row.getChildren().add(labeledStat("Status", p.status()));
        overallPanel.getChildren().add(row);
    }

    private VBox labeledStat(String label, String value) {
        Label l = new Label(label);
        l.getStyleClass().add("perf-stat-heading");
        Label v = new Label(value);
        v.getStyleClass().add("perf-stat-big-value");
        return new VBox(4, l, v);
    }

    private void buildParticipationPanel(ApiClient.Performance p) {
        participationPanel.getChildren().clear();
        participationPanel.getChildren().add(panelHeader("💬", "Discussion Participation"));
        participationPanel.getChildren().add(statRow("Topics Created", String.valueOf(p.topicsCreated())));
        participationPanel.getChildren().add(statRow("Posts Created", String.valueOf(p.postsCreated())));
        participationPanel.getChildren().add(statRow("Replies Made", String.valueOf(p.repliesMade())));
        participationPanel.getChildren().add(statRow("Participation Score", p.participationScore() + " / 50"));
    }

    private void buildQuizPerfPanel(ApiClient.Performance p) {
        quizPerfPanel.getChildren().clear();
        quizPerfPanel.getChildren().add(panelHeader("📖", "Quiz Performance"));
        quizPerfPanel.getChildren().add(statRow("Quizzes Attempted", String.valueOf(p.quizzesAttempted())));
        quizPerfPanel.getChildren().add(statRow("Average Quiz Score", p.averageQuizScore() + "%"));
        quizPerfPanel.getChildren().add(statRow("Highest Score", p.highestScore() + "%"));
        quizPerfPanel.getChildren().add(statRow("Quiz Marks", p.quizMarks() + " / 50"));
    }

    private void buildMarksPanel(ApiClient.Performance p) {
        marksPanel.getChildren().clear();
        marksPanel.getChildren().add(panelHeader("📝", "Overall Marks"));
        marksPanel.getChildren().add(statRow("Participation Marks", p.participationScore() + " / 50"));
        marksPanel.getChildren().add(statRow("Quiz Marks", p.quizMarks() + " / 50"));
        marksPanel.getChildren().add(statRow("Overall Performance", p.overallMarks() + " / 100"));
    }

    private void buildRecentQuizzesPanel(java.util.List<ApiClient.RecentQuizResult> quizzes) {
        recentQuizzesPanel.getChildren().clear();
        recentQuizzesPanel.getChildren().add(panelHeader("📋", "Recent Quiz Results"));

        if (quizzes.isEmpty()) {
            Label empty = new Label("No quiz attempts yet.");
            empty.getStyleClass().add("panel-empty-state");
            recentQuizzesPanel.getChildren().add(empty);
            return;
        }

        for (ApiClient.RecentQuizResult q : quizzes) {
            Label title = new Label(q.quizTitle());
            title.getStyleClass().add("panel-item-title");
            HBox.setHgrow(title, Priority.ALWAYS);

            String formattedDate = formatDate(q.dateRecorded());
            Label date = new Label(formattedDate);
            date.getStyleClass().add("panel-item-meta");

            Label score = new Label(String.format("%.1f%%", q.score()));
            score.getStyleClass().add(q.score() >= 50 ? "quiz-answer-correct" : "quiz-answer-wrong");

            VBox textBlock = new VBox(2, title, date);
            HBox.setHgrow(textBlock, Priority.ALWAYS);

            HBox row = new HBox(10, textBlock, score);
            row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
            row.getStyleClass().add("panel-item");
            recentQuizzesPanel.getChildren().add(row);
        }
    }

    private String formatDate(String iso) {
        try {
            OffsetDateTime dt = OffsetDateTime.parse(iso);
            return dt.format(DateTimeFormatter.ofPattern("MMM d, yyyy"));
        } catch (Exception e) {
            return iso;
        }
    }
}