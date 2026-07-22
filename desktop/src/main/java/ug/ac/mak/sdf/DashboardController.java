package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.util.List;

public class DashboardController {

    @FXML private Label welcomeLabel;
    @FXML private HBox statsRow;
    @FXML private VBox discussionsPanel;
    @FXML private VBox quizzesPanel;
    @FXML private VBox recommendationsPanel;
    @FXML private VBox myGroupsPanel;
    @FXML private VBox activityPanel;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("dashboard");
        }

        statusLabel.setText("Loading dashboard...");

        new Thread(() -> {
            try {
                ApiClient.Dashboard dashboard = ApiClient.getDashboard();
                Platform.runLater(() -> {
                    welcomeLabel.setText("Welcome back, " + dashboard.user().name() + "!");

                    statsRow.getChildren().clear();
                    for (ApiClient.StatCard stat : dashboard.stats()) {
                        statsRow.getChildren().add(buildStatCard(stat));
                    }

                   buildDiscussionsPanel(dashboard.discussions());
                   buildQuizzesPanel(dashboard.quizzes());
                   buildRecommendationsPanel(dashboard.recommendations());
                   buildGroupsPanel(dashboard.groups());
                   buildActivityPanel(dashboard.activity(), dashboard.activityChartPoints());

                  statusLabel.setText("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load dashboard: " + e.getMessage()));
            }
        }).start();
    }

    private VBox buildStatCard(ApiClient.StatCard stat) {
        Label iconLabel = new Label(stat.icon());
        iconLabel.getStyleClass().add("stat-card-icon");

        Label valueLabel = new Label(stat.value());
        valueLabel.getStyleClass().add("stat-card-value");

        Label labelLabel = new Label(stat.label());
        labelLabel.getStyleClass().add("stat-card-label");

        Label changeLabel = new Label("↑ " + stat.change());
        changeLabel.getStyleClass().add("stat-card-change");

        VBox textBlock = new VBox(2, valueLabel, labelLabel, changeLabel);

        HBox topRow = new HBox(10, iconLabel);
        VBox card = new VBox(8, topRow, textBlock);
        card.getStyleClass().add("stat-card");
        card.setPadding(new Insets(16));
        HBox.setHgrow(card, Priority.ALWAYS);

        return card;
    }

    private HBox buildPanelHeader(String icon, String title) {
        Label iconLabel = new Label(icon);
        Label titleLabel = new Label(title);
        titleLabel.getStyleClass().add("panel-title");
        Label viewAll = new Label("View all →");
        viewAll.getStyleClass().add("panel-view-all");

        HBox spacer = new HBox();
        HBox.setHgrow(spacer, Priority.ALWAYS);

        HBox header = new HBox(6, iconLabel, titleLabel, spacer, viewAll);
        header.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        return header;
    }

    private void buildDiscussionsPanel(List<ApiClient.RecentDiscussion> discussions) {
        discussionsPanel.getChildren().clear();
        discussionsPanel.getChildren().add(buildPanelHeader("💬", "Recent Discussions"));

        if (discussions.isEmpty()) {
            discussionsPanel.getChildren().add(emptyStateLabel("No recent discussions."));
        }

        for (ApiClient.RecentDiscussion d : discussions) {
            Label badge = new Label(d.category());
            badge.getStyleClass().add("category-badge");

            Label title = new Label(d.title());
            title.getStyleClass().add("panel-item-title");

            Label meta = new Label(d.author() + " · " + d.postedAt());
            meta.getStyleClass().add("panel-item-meta");

            Label replies = new Label("● " + d.replies() + " replies");
            replies.getStyleClass().add("panel-item-replies");

            VBox item = new VBox(3, badge, title, meta, replies);
            item.getStyleClass().add("panel-item");
            discussionsPanel.getChildren().add(item);

            item.setStyle("-fx-cursor: hand;");
item.setOnMouseClicked(event -> openDiscussion(d.id(), d.title()));
        }
    }
    private void openDiscussion(String discussionId, String fallbackTitle) {
    statusLabel.setText("Loading discussion...");
    new Thread(() -> {
        try {
            ApiClient.Discussion discussion = ApiClient.getDiscussion(discussionId);
            Platform.runLater(() -> {
                try {
                    var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/topics.fxml"));
                    javafx.scene.Parent root = loader.load();
                    TopicsController controller = loader.getController();
                    controller.setDiscussion(discussion.id(), discussion.title(), discussion.description(), discussion.userId(), discussion.groupName());
                    javafx.stage.Stage stage = (javafx.stage.Stage) discussionsPanel.getScene().getWindow();
                    javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
                } catch (Exception e) {
                    statusLabel.setText("Failed to open discussion: " + e.getMessage());
                }
            });
        } catch (Exception e) {
            Platform.runLater(() -> statusLabel.setText("Failed to load discussion: " + e.getMessage()));
        }
    }).start();
}

    private void buildQuizzesPanel(List<ApiClient.UpcomingQuiz> quizzes) {
        quizzesPanel.getChildren().clear();
        quizzesPanel.getChildren().add(buildPanelHeader("📋", "Upcoming Quizzes"));

        if (quizzes.isEmpty()) {
            quizzesPanel.getChildren().add(emptyStateLabel("No upcoming quizzes."));
        }

        for (ApiClient.UpcomingQuiz q : quizzes) {
            Label title = new Label(q.title());
            title.getStyleClass().add("panel-item-title");

            Label subtitle = new Label(q.subtitle());
            subtitle.getStyleClass().add("panel-item-meta");

            Label due = new Label("Due: " + q.due());
            due.getStyleClass().add("panel-item-meta");

            VBox item = new VBox(3, title, subtitle, due);
            item.getStyleClass().add("panel-item");
            item.setStyle("-fx-cursor: hand;");
            item.setOnMouseClicked(event -> openQuiz(q.id()));
            quizzesPanel.getChildren().add(item);
        }
    }

    private void buildRecommendationsPanel(List<ApiClient.Recommendation> recommendations) {
        recommendationsPanel.getChildren().clear();
        recommendationsPanel.getChildren().add(buildPanelHeader("⭐", "Recommended For You"));

        if (recommendations.isEmpty()) {
            recommendationsPanel.getChildren().add(emptyStateLabel("No recommendations yet."));
        }

        for (ApiClient.Recommendation r : recommendations) {
            Label icon = new Label(r.icon());
            Label title = new Label(r.title());
            title.getStyleClass().add("panel-item-title");
            title.setWrapText(true);

            Label subtitle = new Label(r.subtitle());
            subtitle.getStyleClass().add("panel-item-meta");
            subtitle.setWrapText(true);

            VBox textBlock = new VBox(2, title, subtitle);
            HBox item = new HBox(8, icon, textBlock);
            item.getStyleClass().add("panel-item");
            recommendationsPanel.getChildren().add(item);
        }
    }

   private Label emptyStateLabel(String text) {
        Label label = new Label(text);
        label.getStyleClass().add("panel-empty-state");
        return label;
    }

    private void buildGroupsPanel(List<ApiClient.DashboardGroup> groups) {
        myGroupsPanel.getChildren().clear();
        myGroupsPanel.getChildren().add(buildPanelHeader("👥", "My Groups"));

        if (groups.isEmpty()) {
            myGroupsPanel.getChildren().add(emptyStateLabel("You haven't joined any groups yet."));
        }

        for (ApiClient.DashboardGroup g : groups) {
            Label avatar = new Label("👥");
            avatar.getStyleClass().add("group-avatar");

            Label name = new Label(g.name());
            name.getStyleClass().add("panel-item-title");

            Label meta = new Label(g.members() + " members · " + g.newPosts() + " new posts");
            meta.getStyleClass().add("panel-item-meta");

            VBox textBlock = new VBox(2, name, meta);
            HBox.setHgrow(textBlock, Priority.ALWAYS);

            Label statusBadge = new Label(g.status());
            statusBadge.getStyleClass().add("status-badge");

            HBox row = new HBox(10, avatar, textBlock, statusBadge);
row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
row.getStyleClass().add("panel-item");
row.setStyle("-fx-cursor: hand;");
row.setOnMouseClicked(event -> openGroup(g.id(), g.name()));
myGroupsPanel.getChildren().add(row);
        }
    }
    private void openGroup(String groupId, String groupName) {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/discussions.fxml"));
        javafx.scene.Parent root = loader.load();
        DiscussionsController controller = loader.getController();
        controller.setGroup(groupId, groupName);
        javafx.stage.Stage stage = (javafx.stage.Stage) myGroupsPanel.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to open group: " + e.getMessage());
    }
}
    private void openQuiz(String quizId) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/quiz_take.fxml"));
            javafx.scene.Parent root = loader.load();
            QuizTakeController controller = loader.getController();
            controller.setQuizId(quizId);
            javafx.stage.Stage stage = (javafx.stage.Stage) quizzesPanel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to open quiz: " + e.getMessage());
        }
    }

    private void buildActivityPanel(List<ApiClient.ActivityItem> activity, List<ApiClient.ChartPoint> chartPoints) {
        activityPanel.getChildren().clear();
        activityPanel.getChildren().add(buildPanelHeader("📈", "My Activity (This Week)"));

        for (ApiClient.ActivityItem a : activity) {
            Label icon = new Label(a.icon());
            Label label = new Label(a.label());
            label.getStyleClass().add("activity-label");
            HBox.setHgrow(label, Priority.ALWAYS);

            Label value = new Label(a.value());
            value.getStyleClass().add("activity-value");

            Label change = new Label("↑" + a.change());
            change.getStyleClass().add("activity-change");

            HBox row = new HBox(8, icon, label, value, change);
            row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
            row.getStyleClass().add("activity-row");
            activityPanel.getChildren().add(row);
        }

        activityPanel.getChildren().add(buildSparkline(chartPoints));

        HBox dayLabels = new HBox();
        dayLabels.setAlignment(javafx.geometry.Pos.CENTER);
        for (String day : new String[]{"Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"}) {
            Label d = new Label(day);
            d.getStyleClass().add("chart-day-label");
            HBox.setHgrow(d, Priority.ALWAYS);
            d.setMaxWidth(Double.MAX_VALUE);
            d.setAlignment(javafx.geometry.Pos.CENTER);
            dayLabels.getChildren().add(d);
        }
        activityPanel.getChildren().add(dayLabels);
    }

    private javafx.scene.layout.Pane buildSparkline(List<ApiClient.ChartPoint> points) {
        double viewWidth = 300, viewHeight = 110;

        javafx.scene.shape.Polyline line = new javafx.scene.shape.Polyline();
        for (ApiClient.ChartPoint p : points) {
            line.getPoints().addAll(p.x(), p.y());
        }
        line.setStroke(javafx.scene.paint.Color.web("#0d9bb5"));
        line.setStrokeWidth(2.5);
        line.setFill(null);

        javafx.scene.shape.Polygon fill = new javafx.scene.shape.Polygon();
        for (ApiClient.ChartPoint p : points) {
            fill.getPoints().addAll(p.x(), p.y());
        }
        fill.getPoints().addAll(viewWidth, viewHeight, 0.0, viewHeight);
        fill.setFill(javafx.scene.paint.Color.web("#0d9bb5", 0.12));
        fill.setStroke(null);

        javafx.scene.layout.Pane pane = new javafx.scene.layout.Pane(fill, line);
        pane.setPrefSize(viewWidth, viewHeight);
        pane.setMaxWidth(Double.MAX_VALUE);
        return pane;
    }
 
}