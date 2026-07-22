package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.VBox;

import java.util.List;

public class TopicsController {

    @FXML private VBox topicsContainer;
    @FXML private Label statusLabel;
    @FXML private Label discussionTitleLabel;
    @FXML private Label discussionMetaLabel;
    @FXML private Label discussionDescLabel;
    @FXML private Button backButton;

    private String discussionId;

    public void setDiscussion(String discussionId, String title, String description, String userId, String groupName) {
        this.discussionId = discussionId;
        discussionTitleLabel.setText(title);
        discussionMetaLabel.setText("Started by User #" + userId + " in " + groupName);
        discussionDescLabel.setText(description);
        loadTopics();
    }

    @FXML
    public void initialize() {
        backButton.setOnAction(event -> goBack());
    }

    private void goBack() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/discussions.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) backButton.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    private void loadTopics() {
        statusLabel.setText("Loading topics...");

        new Thread(() -> {
            try {
                List<ApiClient.Topic> topics = ApiClient.getTopics(discussionId);
                Platform.runLater(() -> topicsContainer.getChildren().clear());

                for (ApiClient.Topic t : topics) {
                    int postCount;
                    try {
                        postCount = ApiClient.getPosts(t.id()).size();
                    } catch (Exception e) {
                        postCount = 0;
                    }
                    final int count = postCount;
                    Platform.runLater(() -> topicsContainer.getChildren().add(buildTopicCard(t, count)));
                }

                Platform.runLater(() -> statusLabel.setText(topics.size() + " topic(s) loaded."));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load topics: " + e.getMessage()));
            }
        }).start();
    }

    private VBox buildTopicCard(ApiClient.Topic topic, int postCount) {
        Label title = new Label(topic.title() + " (" + capitalize(topic.status()) + ")");
        title.getStyleClass().add("group-card-title");

        Label desc = new Label(topic.description());
        desc.getStyleClass().add("group-card-desc");
        desc.setWrapText(true);

        Label meta = new Label("👤 User #" + topic.userId() + "  ·  💬 " + postCount + " post" + (postCount == 1 ? "" : "s"));
        meta.getStyleClass().add("topic-meta");

        VBox card = new VBox(4, title, desc, meta);
        card.getStyleClass().add("group-card");
        card.setPadding(new Insets(14, 16, 14, 16));
        card.setOnMouseClicked(event -> openTopic(topic));
        return card;
    }

    private String capitalize(String s) {
        if (s == null || s.isEmpty()) return s;
        return Character.toUpperCase(s.charAt(0)) + s.substring(1);
    }

    private void openTopic(ApiClient.Topic topic) {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/posts.fxml"));
        javafx.scene.Parent root = loader.load();
        PostsController controller = loader.getController();
        controller.setTopic(topic.id(), topic.title(), topic.description(), topic.userId());
        javafx.stage.Stage stage = (javafx.stage.Stage) topicsContainer.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to open topic: " + e.getMessage());
    }
}
@FXML
private void handleAddTopic() {
    javafx.scene.control.Dialog<javafx.util.Pair<String, String>> dialog = new javafx.scene.control.Dialog<>();
    dialog.setTitle("Add Topic");
    dialog.setHeaderText("Create a new topic in this discussion");

    javafx.scene.control.ButtonType createButtonType = new javafx.scene.control.ButtonType("Create", javafx.scene.control.ButtonBar.ButtonData.OK_DONE);
    dialog.getDialogPane().getButtonTypes().addAll(createButtonType, javafx.scene.control.ButtonType.CANCEL);

    javafx.scene.control.TextField titleField = new javafx.scene.control.TextField();
    titleField.setPromptText("Title");
    javafx.scene.control.TextArea descField = new javafx.scene.control.TextArea();
    descField.setPromptText("Description");
    descField.setPrefRowCount(3);

    javafx.scene.layout.VBox content = new javafx.scene.layout.VBox(10, titleField, descField);
    content.setPadding(new Insets(10));
    dialog.getDialogPane().setContent(content);

    dialog.setResultConverter(buttonType -> {
        if (buttonType == createButtonType) {
            return new javafx.util.Pair<>(titleField.getText(), descField.getText());
        }
        return null;
    });

    dialog.showAndWait().ifPresent(result -> {
        String title = result.getKey();
        String description = result.getValue();

        if (title == null || title.isBlank()) {
            statusLabel.setText("Title is required.");
            return;
        }

        statusLabel.setText("Creating topic...");
        new Thread(() -> {
            try {
                ApiClient.createTopic(discussionId, title, description);
                Platform.runLater(this::loadTopics);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to create topic: " + e.getMessage()));
            }
        }).start();
    });
}
}