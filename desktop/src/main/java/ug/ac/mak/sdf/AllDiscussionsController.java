package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.scene.layout.VBox;

import java.util.List;

public class AllDiscussionsController {

    @FXML private VBox discussionsContainer;
    @FXML private Label statusLabel;
    @FXML private TextField searchField;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("discussions");
        }
        loadDiscussions(null);
    }

    @FXML
    private void handleSearch() {
        loadDiscussions(searchField.getText());
    }

    private void loadDiscussions(String search) {
        statusLabel.setText("Loading discussions...");

        new Thread(() -> {
            try {
                List<ApiClient.AllDiscussionsItem> discussions = ApiClient.getAllDiscussions(search);
                Platform.runLater(() -> {
                    discussionsContainer.getChildren().clear();
                    for (ApiClient.AllDiscussionsItem d : discussions) {
                        discussionsContainer.getChildren().add(buildCard(d));
                    }
                    statusLabel.setText(discussions.size() + " discussion(s) loaded.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load discussions: " + e.getMessage()));
            }
        }).start();
    }

    private VBox buildCard(ApiClient.AllDiscussionsItem d) {
        Label title = new Label(d.title() + "  —  " + d.groupName());
        title.getStyleClass().add("group-card-title");

        Label desc = new Label(d.description());
        desc.getStyleClass().add("group-card-desc");
        desc.setWrapText(true);

        Label meta = new Label("👤 " + d.authorName() + "  ·  📁 " + d.topicCount() + " topic" + (d.topicCount() == 1 ? "" : "s"));
        meta.getStyleClass().add("topic-meta");

        VBox card = new VBox(4, title, desc, meta);
        card.getStyleClass().add("group-card");
        card.setPadding(new Insets(14, 16, 14, 16));
        card.setStyle("-fx-cursor: hand;");
        card.setOnMouseClicked(event -> openDiscussion(d));
        return card;
    }

    private void openDiscussion(ApiClient.AllDiscussionsItem d) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/topics.fxml"));
            javafx.scene.Parent root = loader.load();
            TopicsController controller = loader.getController();
            controller.setDiscussion(d.id(), d.title(), d.description(), d.userId(), d.groupName());
            javafx.stage.Stage stage = (javafx.stage.Stage) discussionsContainer.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to open discussion: " + e.getMessage());
        }
    }
}