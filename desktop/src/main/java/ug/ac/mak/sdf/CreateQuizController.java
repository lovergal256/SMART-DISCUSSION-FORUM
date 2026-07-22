package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

import java.util.ArrayList;
import java.util.List;

public class CreateQuizController {

    @FXML private Label backLabel;
    @FXML private Label pageTitleLabel;
    @FXML private TextField titleField;
    @FXML private TextField startTimeField;
    @FXML private Spinner<Integer> durationSpinner;
    @FXML private VBox questionsContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    private String groupId;
    private String groupName;

    private final List<QuestionBlock> questionBlocks = new ArrayList<>();

    public void setGroup(String groupId, String groupName) {
        this.groupId = groupId;
        this.groupName = groupName;
        pageTitleLabel.setText("Create Quiz — " + groupName);

        durationSpinner.setValueFactory(
            new SpinnerValueFactory.IntegerSpinnerValueFactory(1, 300, 20)
        );

        backLabel.setOnMouseClicked(e -> goBack());

        // Start with one question block so the form isn't empty.
        handleAddQuestion();
    }

    private void goBack() {
        try {
           var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/group_detail.fxml"));
            javafx.scene.Parent root = loader.load();
            GroupDetailController controller = loader.getController();
            controller.setGroup(groupId);
            javafx.stage.Stage stage = (javafx.stage.Stage) backLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    @FXML
    private void handleCancel() {
        goBack();
    }

    @FXML
    private void handleAddQuestion() {
        QuestionBlock block = new QuestionBlock(questionBlocks.size() + 1);
        questionBlocks.add(block);
        questionsContainer.getChildren().add(block.node);
    }

    private void removeQuestion(QuestionBlock block) {
        questionBlocks.remove(block);
        questionsContainer.getChildren().remove(block.node);
        renumber();
    }

    private void renumber() {
        for (int i = 0; i < questionBlocks.size(); i++) {
            questionBlocks.get(i).setNumber(i + 1);
        }
    }

    @FXML
    private void handleSubmit() {
        String title = titleField.getText() == null ? "" : titleField.getText().trim();
        String startTime = startTimeField.getText() == null ? "" : startTimeField.getText().trim();
        int duration = durationSpinner.getValue();

        if (title.isEmpty()) {
            statusLabel.setText("Please enter a quiz title.");
            return;
        }
        if (startTime.isEmpty()) {
            statusLabel.setText("Please enter a start time (YYYY-MM-DD HH:MM).");
            return;
        }
        if (questionBlocks.isEmpty()) {
            statusLabel.setText("Add at least one question.");
            return;
        }

        List<ApiClient.QuizQuestionInput> questions = new ArrayList<>();
        for (int i = 0; i < questionBlocks.size(); i++) {
            QuestionBlock b = questionBlocks.get(i);
            String qText = b.questionField.getText() == null ? "" : b.questionField.getText().trim();
            String optA = b.optionAField.getText() == null ? "" : b.optionAField.getText().trim();
            String optB = b.optionBField.getText() == null ? "" : b.optionBField.getText().trim();
            String optC = b.optionCField.getText() == null ? "" : b.optionCField.getText().trim();
            String optD = b.optionDField.getText() == null ? "" : b.optionDField.getText().trim();
            String correct = b.correctOptionBox.getValue();
            Integer marks = b.marksSpinner.getValue();

            if (qText.isEmpty() || optA.isEmpty() || optB.isEmpty()) {
                statusLabel.setText("Question " + (i + 1) + ": text, option A, and option B are required.");
                return;
            }
            if (correct == null) {
                statusLabel.setText("Question " + (i + 1) + ": please select the correct option.");
                return;
            }
            // If the correct option is C or D, that option field must actually be filled in.
            if (("C".equals(correct) && optC.isEmpty()) || ("D".equals(correct) && optD.isEmpty())) {
                statusLabel.setText("Question " + (i + 1) + ": the correct option must have text entered.");
                return;
            }

            questions.add(new ApiClient.QuizQuestionInput(
                qText, optA, optB, optC.isEmpty() ? null : optC, optD.isEmpty() ? null : optD,
                correct, marks == null ? 1 : marks
            ));
        }

        // Convert "YYYY-MM-DD HH:MM" into ISO 8601 the server can parse.
        String isoStartTime = startTime.replace(" ", "T");

        statusLabel.setText("Creating quiz...");

        new Thread(() -> {
            try {
                ApiClient.createQuiz(groupId, title, isoStartTime, duration, questions);
                Platform.runLater(this::goBack);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to create quiz: " + e.getMessage()));
            }
        }).start();
    }

    /**
     * One question's editable form fields, plus its container node and a remove button.
     */
    private class QuestionBlock {
        final VBox node;
        final Label headerLabel;
        final TextField questionField = new TextField();
        final TextField optionAField = new TextField();
        final TextField optionBField = new TextField();
        final TextField optionCField = new TextField();
        final TextField optionDField = new TextField();
        final ComboBox<String> correctOptionBox = new ComboBox<>();
        final Spinner<Integer> marksSpinner = new Spinner<>(1, 100, 5);

        QuestionBlock(int number) {
            headerLabel = new Label("Question " + number);
            headerLabel.getStyleClass().add("section-label");

            questionField.setPromptText("Question text");
            optionAField.setPromptText("Option A");
            optionBField.setPromptText("Option B");
            optionCField.setPromptText("Option C (optional)");
            optionDField.setPromptText("Option D (optional)");
            correctOptionBox.getItems().addAll("A", "B", "C", "D");
            correctOptionBox.setPromptText("Correct option");
            marksSpinner.setEditable(true);

            Button removeBtn = new Button("Remove");
            removeBtn.getStyleClass().add("blacklist-button");
            removeBtn.setOnAction(e -> removeQuestion(this));

            HBox headerRow = new HBox(10, headerLabel, removeBtn);
            headerRow.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

            HBox optionsRow1 = new HBox(10, optionAField, optionBField);
            HBox optionsRow2 = new HBox(10, optionCField, optionDField);
            HBox metaRow = new HBox(10, new Label("Correct:"), correctOptionBox, new Label("Marks:"), marksSpinner);
            metaRow.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

            node = new VBox(8, headerRow, questionField, optionsRow1, optionsRow2, metaRow);
            node.getStyleClass().add("discussion-card");
            node.setPadding(new Insets(14));
        }

        void setNumber(int number) {
            headerLabel.setText("Question " + number);
        }
    }
}