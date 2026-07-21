package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.ResourceBundle;

public class CreateQuizController implements Initializable {

    @FXML private Label groupNameLabel;
    @FXML private TextField titleField;
    @FXML private TextField startDateField;   // format: yyyy-MM-dd
    @FXML private TextField startTimeField;   // format: HH:mm
    @FXML private TextField durationField;
    @FXML private VBox questionsContainer;
    @FXML private Label feedbackLabel;
    @FXML private Button createButton;

    private int groupId;
    private String groupName;

    private final List<QuestionRow> questionRows = new ArrayList<>();

    /** Called by whichever screen navigates here, before the scene is shown. */
    public void setGroup(int groupId, String groupName) {
        this.groupId = groupId;
        this.groupName = groupName;
        if (groupNameLabel != null) {
            groupNameLabel.setText("Group: " + groupName);
        }
    }

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        addQuestionRow();
    }

    @FXML
    private void handleAddQuestion() {
        addQuestionRow();
    }

    private void addQuestionRow() {
        QuestionRow row = new QuestionRow(questionRows.size() + 1);
        questionRows.add(row);
        questionsContainer.getChildren().add(row.getView());
    }

    @FXML
    private void handleCreate() {
        String title = titleField.getText().trim();
        String startDate = startDateField.getText().trim();
        String startTime = startTimeField.getText().trim();
        String duration = durationField.getText().trim();

        if (title.isEmpty() || startDate.isEmpty() || startTime.isEmpty() || duration.isEmpty()) {
            showError("Title, start date, start time, and duration are required.");
            return;
        }

        int durationMinutes;
        try {
            durationMinutes = Integer.parseInt(duration);
        } catch (NumberFormatException e) {
            showError("Duration must be a whole number of minutes.");
            return;
        }

        JsonArray questionsJson = new JsonArray();
        for (QuestionRow row : questionRows) {
            String error = row.validate();
            if (error != null) {
                showError(error);
                return;
            }
            questionsJson.add(row.toJson());
        }

        createButton.setDisable(true);
        createButton.setText("Creating...");

        JsonObject body = new JsonObject();
        body.addProperty("title", title);
        body.addProperty("start_time", startDate + " " + startTime + ":00");
        body.addProperty("duration", durationMinutes);
        body.add("questions", questionsJson);

        new Thread(() -> {
            try {
                JsonObject response = ApiService.post("/groups/" + groupId + "/quizzes", body);
                javafx.application.Platform.runLater(() -> {
                    if (response.has("quiz")) {
                        feedbackLabel.setStyle("-fx-text-fill: green;");
                        feedbackLabel.setText("Quiz created successfully!");
                        new Thread(() -> {
                            try { Thread.sleep(1000); } catch (Exception e) {}
                            javafx.application.Platform.runLater(this::handleBack);
                        }).start();
                    } else {
                        showError(response.has("message")
                            ? response.get("message").getAsString()
                            : "Failed to create quiz.");
                        createButton.setDisable(false);
                        createButton.setText("Create Quiz");
                    }
                });
            } catch (Exception e) {
                javafx.application.Platform.runLater(() -> {
                    showError("Error: " + e.getMessage());
                    createButton.setDisable(false);
                    createButton.setText("Create Quiz");
                });
            }
        }).start();
    }

    private void showError(String message) {
        feedbackLabel.setStyle("-fx-text-fill: red;");
        feedbackLabel.setText(message);
    }

    @FXML
    private void handleBack() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/GroupDetail.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Object controller = loader.getController();
            if (controller instanceof GroupDetailController gdc) {
                gdc.setGroupId(groupId);
            }
            Stage stage = (Stage) titleField.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    /**
     * One question's form fields, bundled together. Mirrors the web version's
     * question_text / option_a-d / correct_option / marks structure exactly.
     */
    private static class QuestionRow {
        private final VBox view;
        private final TextArea questionText = new TextArea();
        private final TextField optionA = new TextField();
        private final TextField optionB = new TextField();
        private final TextField optionC = new TextField();
        private final TextField optionD = new TextField();
        private final ComboBox<String> correctOption = new ComboBox<>();
        private final TextField marks = new TextField("1");

        QuestionRow(int number) {
            questionText.setPromptText("Question text");
            questionText.setPrefRowCount(2);
            optionA.setPromptText("Option A");
            optionB.setPromptText("Option B");
            optionC.setPromptText("Option C (optional)");
            optionD.setPromptText("Option D (optional)");
            correctOption.getItems().addAll("A", "B", "C", "D");
            correctOption.setValue("A");

            Label header = new Label("Question " + number);
            header.setStyle("-fx-font-weight: bold;");

            Label marksLabel = new Label("Marks");
            javafx.scene.layout.HBox marksRow = new javafx.scene.layout.HBox(10, marksLabel, marks);
            marksRow.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

            view = new VBox(8, header, questionText, optionA, optionB, optionC, optionD,
                new Label("Correct option"), correctOption, marksRow);
            view.setStyle("-fx-padding: 12; -fx-border-color: #b0c4d8; -fx-border-radius: 6; "
                + "-fx-background-color: white; -fx-background-radius: 6;");
        }

        VBox getView() {
            return view;
        }

        String validate() {
            if (questionText.getText().trim().isEmpty()) return "Every question needs text.";
            if (optionA.getText().trim().isEmpty()) return "Option A is required for every question.";
            if (optionB.getText().trim().isEmpty()) return "Option B is required for every question.";
            try {
                Integer.parseInt(marks.getText().trim());
            } catch (NumberFormatException e) {
                return "Marks must be a whole number.";
            }
            return null;
        }

        JsonObject toJson() {
            JsonObject obj = new JsonObject();
            obj.addProperty("question_text", questionText.getText().trim());
            obj.addProperty("option_a", optionA.getText().trim());
            obj.addProperty("option_b", optionB.getText().trim());
            String c = optionC.getText().trim();
            String d = optionD.getText().trim();
            if (!c.isEmpty()) obj.addProperty("option_c", c); else obj.add("option_c", null);
            if (!d.isEmpty()) obj.addProperty("option_d", d); else obj.add("option_d", null);
            obj.addProperty("correct_option", correctOption.getValue());
            obj.addProperty("marks", Integer.parseInt(marks.getText().trim()));
            return obj;
        }
    }
}