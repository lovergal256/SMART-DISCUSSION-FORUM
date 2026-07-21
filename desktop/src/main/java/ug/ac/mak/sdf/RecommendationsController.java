package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

public class RecommendationsController {

    @FXML private VBox trendingPanel;
    @FXML private VBox activePostsPanel;
    @FXML private VBox suggestedGroupsPanel;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("recommendations");
        }
        loadRecommendations();
    }

    private void loadRecommendations() {
        statusLabel.setText("Loading recommendations...");

        new Thread(() -> {
            try {
                ApiClient.Recommendations rec = ApiClient.getRecommendations();
                Platform.runLater(() -> {
                    buildTrendingPanel(rec.trendingTopics());
                    buildActivePostsPanel(rec.activePosts());
                    buildSuggestedGroupsPanel(rec.suggestedGroups());
                    statusLabel.setText("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load recommendations: " + e.getMessage()));
            }
        }).start();
    }

    private Label panelHeader(String icon, String title) {
        Label header = new Label(icon + "  " + title);
        header.getStyleClass().add("panel-title");
        return header;
    }

    private void buildTrendingPanel(java.util.List<ApiClient.TrendingTopic> topics) {
        trendingPanel.getChildren().clear();
        trendingPanel.getChildren().add(panelHeader("🔥", "Trending Topics"));

        for (ApiClient.TrendingTopic t : topics) {
            Label title = new Label(t.title());
            title.getStyleClass().add("panel-item-title");

            Label meta = new Label(t.postCount() + " posts · " + t.status());
            meta.getStyleClass().add("panel-item-meta");

            VBox textBlock = new VBox(2, title, meta);
            HBox.setHgrow(textBlock, Priority.ALWAYS);

            Label countBadge = new Label(t.postCount() + " posts");
            countBadge.getStyleClass().add("trending-count-badge");

            HBox row = new HBox(10, textBlock, countBadge);
            row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
            row.getStyleClass().add("panel-item");
            trendingPanel.getChildren().add(row);
        }
    }

    private void buildActivePostsPanel(java.util.List<ApiClient.ActivePost> posts) {
        activePostsPanel.getChildren().clear();
        activePostsPanel.getChildren().add(panelHeader("💬", "Most Active Posts"));

        for (ApiClient.ActivePost p : posts) {
            String preview = p.content().length() > 60 ? p.content().substring(0, 60) + "..." : p.content();
            Label title = new Label(preview);
            title.getStyleClass().add("panel-item-title");
            title.setWrapText(true);

            Label meta = new Label(p.topicTitle());
            meta.getStyleClass().add("panel-item-meta");

            VBox item = new VBox(2, title, meta);
            item.getStyleClass().add("panel-item");
            activePostsPanel.getChildren().add(item);
        }
    }

    private void buildSuggestedGroupsPanel(java.util.List<ApiClient.SuggestedGroup> groups) {
        suggestedGroupsPanel.getChildren().clear();
        suggestedGroupsPanel.getChildren().add(panelHeader("👥", "Suggested Groups"));

        HBox row = new HBox(14);
        for (ApiClient.SuggestedGroup g : groups) {
            Label avatar = new Label("👥");
            avatar.getStyleClass().add("group-avatar");

            Label name = new Label(g.name());
            name.getStyleClass().add("panel-item-title");

            Label desc = new Label(g.description());
            desc.getStyleClass().add("panel-item-meta");
            desc.setWrapText(true);

            VBox textBlock = new VBox(2, name, desc);
            HBox card = new HBox(10, avatar, textBlock);
            card.getStyleClass().add("group-card");
            card.setPadding(new Insets(12));
            HBox.setHgrow(card, Priority.ALWAYS);
            card.setStyle("-fx-cursor: hand;");
            card.setOnMouseClicked(event -> openGroup(g.id(), g.name()));
            row.getChildren().add(card);
        }
        suggestedGroupsPanel.getChildren().add(row);
    }

    private void openGroup(String groupId, String groupName) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/discussions.fxml"));
            javafx.scene.Parent root = loader.load();
            DiscussionsController controller = loader.getController();
            controller.setGroup(groupId, groupName);
            javafx.stage.Stage stage = (javafx.stage.Stage) suggestedGroupsPanel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to open group: " + e.getMessage());
        }
    }
}