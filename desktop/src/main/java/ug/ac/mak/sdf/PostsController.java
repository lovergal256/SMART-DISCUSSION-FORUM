package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

import java.time.Duration;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class PostsController {

    @FXML private VBox postsContainer;
    @FXML private Label statusLabel;
    @FXML private Label topicTitleLabel;
    @FXML private Label topicDescLabel;
    @FXML private Label topicMetaLabel;
    @FXML private Label backLabel;

    private String topicId;
    private String topicTitle;
    private String topicDescription;

    public void setTopic(String topicId, String title, String description, String userId) {
        this.topicId = topicId;
        this.topicTitle = title;
        this.topicDescription = description;
        topicTitleLabel.setText(title);
        topicDescLabel.setText(description);
        topicMetaLabel.setText("Posted by User #" + userId);
        loadPosts();
    }

    @FXML
    public void initialize() {
        backLabel.setOnMouseClicked(event -> goBack());
    }

    private void goBack() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/topics.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) backLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    private void loadPosts() {
        statusLabel.setText("Loading posts...");

        new Thread(() -> {
            try {
                List<ApiClient.Post> posts = ApiClient.getPosts(topicId);
                Platform.runLater(() -> {
                    postsContainer.getChildren().clear();
                    for (ApiClient.Post p : posts) {
                        postsContainer.getChildren().add(buildPostBox(p));
                    }
                    statusLabel.setText(posts.size() + " post(s) loaded.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load posts: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleEditTopic() {
        javafx.scene.control.Dialog<javafx.util.Pair<String, String>> dialog = new javafx.scene.control.Dialog<>();
        dialog.setTitle("Edit Topic");
        dialog.setHeaderText("Update this topic");

        javafx.scene.control.ButtonType saveButtonType = new javafx.scene.control.ButtonType("Save", javafx.scene.control.ButtonBar.ButtonData.OK_DONE);
        dialog.getDialogPane().getButtonTypes().addAll(saveButtonType, javafx.scene.control.ButtonType.CANCEL);

        javafx.scene.control.TextField titleField = new javafx.scene.control.TextField(topicTitle);
        javafx.scene.control.TextArea descField = new javafx.scene.control.TextArea(topicDescription);
        descField.setPrefRowCount(3);

        javafx.scene.layout.VBox content = new javafx.scene.layout.VBox(10, titleField, descField);
        content.setPadding(new Insets(10));
        dialog.getDialogPane().setContent(content);

        dialog.setResultConverter(buttonType -> {
            if (buttonType == saveButtonType) {
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

            statusLabel.setText("Updating topic...");
            new Thread(() -> {
                try {
                    ApiClient.Topic updated = ApiClient.updateTopic(topicId, title, description);
                    Platform.runLater(() -> {
                        topicTitle = updated.title();
                        topicDescription = updated.description();
                        topicTitleLabel.setText(updated.title());
                        topicDescLabel.setText(updated.description());
                        statusLabel.setText("Topic updated.");
                    });
                } catch (Exception e) {
                    Platform.runLater(() -> statusLabel.setText("Failed to update topic: " + e.getMessage()));
                }
            }).start();
        });
    }

    @FXML
    private void handleDeleteTopic() {
        javafx.scene.control.Alert confirm = new javafx.scene.control.Alert(
            javafx.scene.control.Alert.AlertType.CONFIRMATION,
            "Delete this topic and all its posts/replies? This can't be undone."
        );
        confirm.setHeaderText("Delete Topic");
        confirm.showAndWait().ifPresent(response -> {
            if (response == javafx.scene.control.ButtonType.OK) {
                statusLabel.setText("Deleting topic...");
                new Thread(() -> {
                    try {
                        ApiClient.deleteTopic(topicId);
                        Platform.runLater(this::goBack);
                    } catch (Exception e) {
                        Platform.runLater(() -> statusLabel.setText("Failed to delete topic: " + e.getMessage()));
                    }
                }).start();
            }
        });
    }

    @FXML
    private void handleAddPost() {
        javafx.scene.control.Dialog<String> dialog = new javafx.scene.control.Dialog<>();
        dialog.setTitle("Add Post");
        dialog.setHeaderText("Write a new post in this topic");

        javafx.scene.control.ButtonType postButtonType = new javafx.scene.control.ButtonType("Post", javafx.scene.control.ButtonBar.ButtonData.OK_DONE);
        dialog.getDialogPane().getButtonTypes().addAll(postButtonType, javafx.scene.control.ButtonType.CANCEL);

        javafx.scene.control.TextArea contentField = new javafx.scene.control.TextArea();
        contentField.setPromptText("What's on your mind?");
        contentField.setPrefRowCount(4);
        contentField.setWrapText(true);

        javafx.scene.layout.VBox content = new javafx.scene.layout.VBox(10, contentField);
        content.setPadding(new Insets(10));
        dialog.getDialogPane().setContent(content);

        dialog.setResultConverter(buttonType ->
            buttonType == postButtonType ? contentField.getText() : null
        );

        dialog.showAndWait().ifPresent(text -> {
            if (text == null || text.isBlank()) {
                statusLabel.setText("Post content is required.");
                return;
            }

            statusLabel.setText("Posting...");
            new Thread(() -> {
                try {
                    ApiClient.createPost(topicId, text);
                    Platform.runLater(this::loadPosts);
                } catch (Exception e) {
                    Platform.runLater(() -> statusLabel.setText("Failed to create post: " + e.getMessage()));
                }
            }).start();
        });
    }

    private VBox buildPostBox(ApiClient.Post post) {
        Label title = new Label(post.content());
        title.getStyleClass().add("post-title");
        title.setWrapText(true);

        Label meta = new Label("Posted by User #" + post.userId() + " · " + timeAgo(post.datePosted()));
        meta.getStyleClass().add("post-meta");

        TextArea replyInput = new TextArea();
        replyInput.setPromptText("Write a reply...");
        replyInput.getStyleClass().add("reply-input");
        replyInput.setPrefRowCount(3);
        replyInput.setWrapText(true);

        VBox repliesContainer = new VBox(10);
        repliesContainer.setVisible(false);
        repliesContainer.setManaged(false);
        repliesContainer.setPadding(new Insets(10, 0, 0, 0));

        Button viewRepliesButton = new Button("💬 View Replies");
        viewRepliesButton.getStyleClass().add("view-replies-button");

        boolean[] loaded = {false};
        boolean[] visible = {false};

        Button replyButton = new Button("↩ Reply");
        replyButton.getStyleClass().add("reply-button");
        replyButton.setOnAction(e -> {
            String text = replyInput.getText() == null ? "" : replyInput.getText().trim();
            if (text.isEmpty()) {
                statusLabel.setText("Reply can't be empty.");
                return;
            }
            replyButton.setDisable(true);
            new Thread(() -> {
                try {
                    ApiClient.postReply(post.id(), text, null);
                    Platform.runLater(() -> {
                        replyInput.clear();
                        replyButton.setDisable(false);
                        statusLabel.setText("Reply posted.");
                        // Refresh replies if they're currently shown; otherwise force a reload next time they're opened
                        loaded[0] = false;
                        if (visible[0]) {
                            new Thread(() -> {
                                try {
                                    List<ApiClient.Reply> replies = ApiClient.getReplies(post.id());
                                    Platform.runLater(() -> {
                                        renderReplyTree(repliesContainer, replies, post.id());
                                        viewRepliesButton.setText("💬 Hide Replies (" + replies.size() + ")");
                                        loaded[0] = true;
                                    });
                                } catch (Exception ex) {
                                    Platform.runLater(() -> statusLabel.setText("Failed to refresh replies: " + ex.getMessage()));
                                }
                            }).start();
                        }
                    });
                } catch (Exception ex) {
                    Platform.runLater(() -> {
                        statusLabel.setText("Failed to post reply: " + ex.getMessage());
                        replyButton.setDisable(false);
                    });
                }
            }).start();
        });

        viewRepliesButton.setOnAction(e -> {
            visible[0] = !visible[0];
            repliesContainer.setVisible(visible[0]);
            repliesContainer.setManaged(visible[0]);

            if (visible[0] && !loaded[0]) {
                loaded[0] = true;
                new Thread(() -> {
                    try {
                        List<ApiClient.Reply> replies = ApiClient.getReplies(post.id());
                        Platform.runLater(() -> {
                          renderReplyTree(repliesContainer, replies, post.id());
                        viewRepliesButton.setText("💬 Hide Replies (" + replies.size() + ")");
                     });
                    } catch (Exception ex) {
                        Platform.runLater(() -> statusLabel.setText("Failed to load replies: " + ex.getMessage()));
                    }
                }).start();
            } else {
                viewRepliesButton.setText(visible[0] ? "💬 Hide Replies" : "💬 View Replies");
            }
        });

        HBox buttonRow = new HBox(10, replyButton, viewRepliesButton);

        VBox box = new VBox(6, title, meta, replyInput, buttonRow, repliesContainer);
        box.getStyleClass().add("group-card");
        box.setPadding(new Insets(16));
        return box;
    }

    private void renderReplyTree(VBox container, List<ApiClient.Reply> replies, String postId) {
        container.getChildren().clear();
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
            container.getChildren().add(buildReplyBox(r, childrenByParent, container, postId));
        }
    }

    private VBox buildReplyBox(ApiClient.Reply reply, Map<String, List<ApiClient.Reply>> childrenByParent, VBox parentRepliesContainer, String postId) {
        Label body = new Label(reply.body());
        body.getStyleClass().add("reply-body");
        body.setWrapText(true);

        Label meta = new Label("By User #" + reply.userId() + " · " + timeAgo(reply.createdAt()));
        meta.getStyleClass().add("reply-meta");

        Button replyToggle = new Button("↩ Reply");
        replyToggle.getStyleClass().add("reply-button");

        TextArea nestedInput = new TextArea();
        nestedInput.setPromptText("Write a reply...");
        nestedInput.getStyleClass().add("reply-input");
        nestedInput.setPrefRowCount(2);
        nestedInput.setWrapText(true);
        nestedInput.setVisible(false);
        nestedInput.setManaged(false);

        Button sendButton = new Button("Send");
        sendButton.getStyleClass().add("reply-button");
        sendButton.setVisible(false);
        sendButton.setManaged(false);

        replyToggle.setOnAction(e -> {
            boolean show = !nestedInput.isVisible();
            nestedInput.setVisible(show);
            nestedInput.setManaged(show);
            sendButton.setVisible(show);
            sendButton.setManaged(show);
        });

        VBox childrenBox = new VBox(10);
        childrenBox.setPadding(new Insets(10, 0, 0, 20));

        sendButton.setOnAction(e -> {
            String text = nestedInput.getText().trim();
            if (text.isEmpty()) return;

            sendButton.setDisable(true);
            new Thread(() -> {
                try {
                    ApiClient.Reply newReply = ApiClient.postReply(postId, text, reply.id());
                    Platform.runLater(() -> {
                        childrenByParent.computeIfAbsent(reply.id(), k -> new ArrayList<>()).add(newReply);
                        childrenBox.getChildren().add(buildReplyBox(newReply, childrenByParent, childrenBox, postId));
                        nestedInput.clear();
                        nestedInput.setVisible(false);
                        nestedInput.setManaged(false);
                        sendButton.setVisible(false);
                        sendButton.setManaged(false);
                        sendButton.setDisable(false);
                    });
                } catch (Exception ex) {
                    Platform.runLater(() -> {
                        statusLabel.setText("Failed to post reply: " + ex.getMessage());
                        sendButton.setDisable(false);
                    });
                }
            }).start();
        });

        HBox replyActionRow = new HBox(8, replyToggle, sendButton);

        VBox box = new VBox(6, body, meta, replyActionRow, nestedInput);
        box.getStyleClass().add("reply-box");
        box.setPadding(new Insets(12));

        List<ApiClient.Reply> children = childrenByParent.get(reply.id());
        if (children != null && !children.isEmpty()) {
            for (ApiClient.Reply child : children) {
                childrenBox.getChildren().add(buildReplyBox(child, childrenByParent, childrenBox, postId));
            }
        }
        box.getChildren().add(childrenBox);

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
