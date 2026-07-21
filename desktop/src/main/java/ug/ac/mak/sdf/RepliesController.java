package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.VBox;

import java.time.Duration;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class RepliesController {

    @FXML private VBox repliesContainer;
    @FXML private Label statusLabel;
    @FXML private Label backLabel;
    @FXML private Label postTitleLabel;
    @FXML private Label postMetaLabel;
    @FXML private TextArea replyInput;
    @FXML private Button viewRepliesButton;

    private String postId;
    private List<ApiClient.Reply> loadedReplies = new ArrayList<>();
    private boolean repliesVisible = false;

    public void setPost(String postId, String postContent, String postDate) {
        this.postId = postId;
        postTitleLabel.setText(postContent);
        postMetaLabel.setText("Posted " + timeAgo(postDate));
        loadReplies();
    }

    @FXML
    public void initialize() {
        backLabel.setOnMouseClicked(this::goBack);
    }

    @FXML
    private void handleReply() {
        statusLabel.setText("Posting replies isn't wired up yet — read-only for now.");
    }

    @FXML
    private void toggleReplies() {
        repliesVisible = !repliesVisible;
        repliesContainer.setVisible(repliesVisible);
        repliesContainer.setManaged(repliesVisible);
        updateViewRepliesLabel();
    }

    private void updateViewRepliesLabel() {
        String arrow = repliesVisible ? "▲" : "▼";
        viewRepliesButton.setText("💬 " + (repliesVisible ? "Hide" : "View") + " Replies (" + loadedReplies.size() + ") " + arrow);
    }

    private void goBack(MouseEvent event) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/posts.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) backLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    private void loadReplies() {
        statusLabel.setText("Loading replies...");

        new Thread(() -> {
            try {
                List<ApiClient.Reply> replies = ApiClient.getReplies(postId);
                Platform.runLater(() -> {
                    loadedReplies = replies;
                    repliesContainer.getChildren().clear();
                    renderReplyTree(replies);
                    repliesContainer.setVisible(false);
                    repliesContainer.setManaged(false);
                    repliesVisible = false;
                    updateViewRepliesLabel();
                    statusLabel.setText("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load replies: " + e.getMessage()));
            }
        }).start();
    }

    private void renderReplyTree(List<ApiClient.Reply> replies) {
        Map<String, List<ApiClient.Reply>> childrenByParent = new HashMap<>();
        List<ApiClient.Reply> topLevel = new ArrayList<>();

        for (ApiClient.Reply r : replies) {
            if (r.parentReplyId() == null || r.parentReplyId().isEmpty()) {
                topLevel.add(r);
            } else {
                childrenByParent.computeIfAbsent(r.parentReplyId(), k -> new ArrayList<>()).add(r);
            }
        }

        for (ApiClient.Reply r : topLevel) {
            repliesContainer.getChildren().add(buildReplyBox(r, childrenByParent));
        }
    }

    private VBox buildReplyBox(ApiClient.Reply reply, Map<String, List<ApiClient.Reply>> childrenByParent) {
        Label body = new Label(reply.body());
        body.getStyleClass().add("reply-body");
        body.setWrapText(true);

        Label meta = new Label("By User #" + reply.userId() + " · " + timeAgo(reply.createdAt()));
        meta.getStyleClass().add("reply-meta");

        VBox box = new VBox(4, body, meta);
        box.getStyleClass().add("reply-box");
        box.setPadding(new Insets(12));

        List<ApiClient.Reply> children = childrenByParent.get(reply.id());
        if (children != null && !children.isEmpty()) {
            VBox childrenBox = new VBox(10);
            childrenBox.setPadding(new Insets(10, 0, 0, 20));
            for (ApiClient.Reply child : children) {
                childrenBox.getChildren().add(buildReplyBox(child, childrenByParent));
            }
            box.getChildren().add(childrenBox);
        }

        return box;
    }

    private String timeAgo(String isoDate) {
        if (isoDate == null || isoDate.isEmpty()) return "";
        try {
            LocalDateTime dt = LocalDateTime.parse(isoDate.replace("Z", ""), DateTimeFormatter.ISO_DATE_TIME);
            long days = Duration.between(dt, LocalDateTime.now()).toDays();
            if (days <= 0) return "today";
            if (days == 1) return "1 day ago";
            return days + " days ago";
        } catch (Exception e) {
            return isoDate;
        }
    }
}