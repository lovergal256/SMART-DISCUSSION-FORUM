package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.VBox;

import java.util.List;

public class DiscussionsController {

    @FXML private VBox discussionsContainer;
    @FXML private Label statusLabel;
    @FXML private Label groupTitleLabel;
    @FXML private Label backLabel;

    private String groupId;
    private String groupName;

    public void setGroup(String groupId, String groupName) {
        this.groupId = groupId;
        this.groupName = groupName;
        groupTitleLabel.setText(groupName + " — Discussions");
        loadDiscussions();
    }

    @FXML
    public void initialize() {
        backLabel.setOnMouseClicked(this::goBackToGroups);
    }

    private void goBackToGroups(MouseEvent event) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/groups.fxml"));
            javafx.scene.Parent groupsRoot = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) backLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(groupsRoot, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    private void loadDiscussions() {
        statusLabel.setText("Loading discussions...");

        new Thread(() -> {
            try {
                List<ApiClient.Discussion> discussions = ApiClient.getDiscussions(groupId);
                Platform.runLater(() -> {
                    discussionsContainer.getChildren().clear();
                    for (ApiClient.Discussion d : discussions) {
                        discussionsContainer.getChildren().add(buildDiscussionCard(d));
                    }
                    statusLabel.setText(discussions.size() + " discussion(s) loaded.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load discussions: " + e.getMessage()));
            }
        }).start();
    }
private VBox buildDiscussionCard(ApiClient.Discussion discussion) {
    Label title = new Label(discussion.title());
    title.getStyleClass().add("group-card-title");

    Label desc = new Label(discussion.description());
    desc.getStyleClass().add("group-card-desc");
    desc.setWrapText(true);

    VBox card = new VBox(4, title, desc);
    card.getStyleClass().add("group-card");
    card.setPadding(new Insets(14, 16, 14, 16));
    card.setOnMouseClicked(event -> openDiscussion(discussion));
    return card;
}

private void openDiscussion(ApiClient.Discussion discussion) {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/topics.fxml"));
        javafx.scene.Parent root = loader.load();
        TopicsController controller = loader.getController();
        controller.setDiscussion(discussion.id(), discussion.title(), discussion.description(), discussion.userId(), this.groupName);
        javafx.stage.Stage stage = (javafx.stage.Stage) discussionsContainer.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to open discussion: " + e.getMessage());
    }
}
   
}