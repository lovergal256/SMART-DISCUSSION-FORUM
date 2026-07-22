package ug.ac.mak.sdf;



import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

public class GroupDetailController {

    @FXML private Label backLabel;
    @FXML private Label backLabel2;
    @FXML private Label groupNameLabel;
    @FXML private Label groupDescLabel;
    @FXML private Label groupVisibilityLabel;
    @FXML private Button visibilityButton;
    @FXML private Label membersHeaderLabel;
    @FXML private VBox membersContainer;
    @FXML private VBox addMemberSection;
    @FXML private TextField addMemberField;
    @FXML private VBox pendingSection;
    @FXML private Label pendingHeaderLabel;
    @FXML private VBox pendingContainer;
    @FXML private Label discussionsHeaderLabel;
    @FXML private VBox discussionsContainer;
    @FXML private VBox exclusionsContainer;
    @FXML private TextField excludeUserField;
    @FXML private Button deleteGroupButton;
    @FXML private Label statusLabel;
    @FXML private Button createQuizButton;
    @FXML private SideBarController sidebarController;

    private String groupId;
    private String groupName;
    private boolean isAdmin;

    public void setGroup(String groupId) {
        this.groupId = groupId;
        if (sidebarController != null) {
            sidebarController.setActiveItem("groups");
        }
        backLabel.setOnMouseClicked(this::goBack);
        backLabel2.setOnMouseClicked(this::goBack);
        loadGroup();
        loadExclusions();
    }

    private void goBack(MouseEvent event) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/groups.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) backLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }

    private void loadGroup() {
        statusLabel.setText("Loading group...");

        new Thread(() -> {
            try {
                ApiClient.GroupDetail detail = ApiClient.getGroupDetail(groupId);
                Platform.runLater(() -> {
                    groupName = detail.name();
                    isAdmin = detail.isAdmin();

                    groupNameLabel.setText(detail.name());
                    groupDescLabel.setText(detail.description());
                    groupVisibilityLabel.setText("Visibility: " + capitalize(detail.visibility()));
                    visibilityButton.setText(detail.visibility().equalsIgnoreCase("public") ? "Make Private" : "Make Public");

                    membersHeaderLabel.setText("Members (" + detail.members().size() + ")");
                    membersContainer.getChildren().clear();
                    for (ApiClient.GroupMember m : detail.members()) {
                        membersContainer.getChildren().add(buildMemberRow(m));
                    }

                    addMemberSection.setVisible(isAdmin);
                    addMemberSection.setManaged(isAdmin);
                    deleteGroupButton.setVisible(isAdmin);
                    deleteGroupButton.setManaged(isAdmin);
                    createQuizButton.setVisible(ApiClient.isLecturer());
                    createQuizButton.setManaged(ApiClient.isLecturer());

                    pendingSection.setVisible(isAdmin);
                    pendingSection.setManaged(isAdmin);
                    if (isAdmin) {
                        pendingHeaderLabel.setText("Pending Requests (" + detail.pendingRequests().size() + ")");
                        pendingContainer.getChildren().clear();
                        if (detail.pendingRequests().isEmpty()) {
                            pendingContainer.getChildren().add(emptyLabel("No pending requests."));
                        }
                        for (ApiClient.PendingRequest p : detail.pendingRequests()) {
                            pendingContainer.getChildren().add(buildPendingRow(p));
                        }
                    }

                    discussionsHeaderLabel.setText("Discussions (" + detail.discussions().size() + ")");
                    discussionsContainer.getChildren().clear();
                    if (detail.discussions().isEmpty()) {
                        discussionsContainer.getChildren().add(emptyLabel("No discussions yet."));
                    }
                    for (ApiClient.GroupDiscussionSummary d : detail.discussions()) {
                        discussionsContainer.getChildren().add(buildDiscussionRow(d));
                    }

                    statusLabel.setText("");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load group: " + e.getMessage()));
            }
        }).start();
    }

    private void loadExclusions() {
        new Thread(() -> {
            try {
                java.util.List<ApiClient.ExclusionItem> exclusions = ApiClient.getExclusions(groupId);
                Platform.runLater(() -> {
                    exclusionsContainer.getChildren().clear();
                    if (exclusions.isEmpty()) {
                        exclusionsContainer.getChildren().add(emptyLabel("No exclusions yet."));
                    }
                    for (ApiClient.ExclusionItem e : exclusions) {
                        exclusionsContainer.getChildren().add(buildExclusionRow(e));
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load exclusions: " + e.getMessage()));
            }
        }).start();
    }

    private Label emptyLabel(String text) {
        Label l = new Label(text);
        l.getStyleClass().add("panel-empty-state");
        return l;
    }

    private HBox buildMemberRow(ApiClient.GroupMember member) {
        String suffix = member.isCreator() ? " (Creator)" : "";

        Label name = new Label(member.fullName() + suffix);
        name.getStyleClass().add("panel-item-title");

        Label email = new Label(member.email());
        email.getStyleClass().add("panel-item-meta");

        VBox textBlock = new VBox(2, name, email);
        HBox.setHgrow(textBlock, Priority.ALWAYS);

        Label roleBadge = new Label(member.role());
        roleBadge.getStyleClass().add("role-badge");

        HBox row = new HBox(10, textBlock, roleBadge);

        if (isAdmin && !member.isCreator()) {
            Button promoteBtn = new Button(member.role().equalsIgnoreCase("admin") ? "Demote" : "Promote");
            promoteBtn.getStyleClass().add("action-button");
            Button removeBtn = new Button("Remove");
            removeBtn.getStyleClass().add("blacklist-button");
            Button blacklistBtn = new Button("Blacklist");
            blacklistBtn.getStyleClass().add("blacklist-button");

            blacklistBtn.setOnAction(e -> {
                new Thread(() -> {
                    try {
                        String msg = ApiClient.blacklistGroupMember(groupId, member.userId());
                        Platform.runLater(() -> { statusLabel.setText(msg); loadGroup(); });
                    } catch (Exception ex) {
                        Platform.runLater(() -> statusLabel.setText("Failed: " + ex.getMessage()));
                    }
                }).start();
            });

            row.getChildren().addAll(promoteBtn, removeBtn, blacklistBtn);
        }

        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.setSpacing(10);
        row.getStyleClass().add("panel-item");
        row.setPadding(new Insets(10, 0, 10, 0));
        return row;
    }

    private HBox buildPendingRow(ApiClient.PendingRequest p) {
        Label name = new Label(p.fullName());
        name.getStyleClass().add("panel-item-title");
        Label email = new Label(p.email());
        email.getStyleClass().add("panel-item-meta");
        VBox textBlock = new VBox(2, name, email);
        HBox.setHgrow(textBlock, Priority.ALWAYS);

        Button approveBtn = new Button("Approve");
        approveBtn.getStyleClass().add("action-button");
        approveBtn.setOnAction(e -> {
            new Thread(() -> {
                try {
                    ApiClient.approveMember(groupId, p.userId());
                    Platform.runLater(this::loadGroup);
                } catch (Exception ex) {
                    Platform.runLater(() -> statusLabel.setText("Failed: " + ex.getMessage()));
                }
            }).start();
        });

        Button rejectBtn = new Button("Reject");
        rejectBtn.getStyleClass().add("blacklist-button");
        rejectBtn.setOnAction(e -> {
            new Thread(() -> {
                try {
                    ApiClient.rejectMember(groupId, p.userId());
                    Platform.runLater(this::loadGroup);
                } catch (Exception ex) {
                    Platform.runLater(() -> statusLabel.setText("Failed: " + ex.getMessage()));
                }
            }).start();
        });

        HBox row = new HBox(10, textBlock, approveBtn, rejectBtn);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.getStyleClass().add("panel-item");
        row.setPadding(new Insets(10, 0, 10, 0));
        return row;
    }

    private HBox buildDiscussionRow(ApiClient.GroupDiscussionSummary d) {
        Label title = new Label(d.title());
        title.getStyleClass().add("panel-item-title");
        HBox row = new HBox(title);
        row.getStyleClass().add("panel-item");
        row.setPadding(new Insets(10, 0, 10, 0));
        row.setStyle("-fx-cursor: hand;");
        row.setOnMouseClicked(e -> openDiscussion(d.id(), d.title()));
        return row;
    }

    private void openDiscussion(String discussionId, String title) {
        statusLabel.setText("Loading discussion...");
        new Thread(() -> {
            try {
                ApiClient.Discussion discussion = ApiClient.getDiscussion(discussionId);
                Platform.runLater(() -> {
                    try {
                        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/topics.fxml"));
                        javafx.scene.Parent root = loader.load();
                        TopicsController controller = loader.getController();
                        controller.setDiscussion(discussion.id(), discussion.title(), discussion.description(), discussion.userId(), groupName);
                        javafx.stage.Stage stage = (javafx.stage.Stage) discussionsContainer.getScene().getWindow();
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

    private HBox buildExclusionRow(ApiClient.ExclusionItem e) {
        Label name = new Label(e.excludedUserName());
        name.getStyleClass().add("panel-item-title");
        HBox.setHgrow(name, Priority.ALWAYS);

        Button removeBtn = new Button("Remove");
        removeBtn.getStyleClass().add("blacklist-button");
        removeBtn.setOnAction(ev -> {
            new Thread(() -> {
                try {
                    ApiClient.removeExclusion(groupId, e.id());
                    Platform.runLater(this::loadExclusions);
                } catch (Exception ex) {
                    Platform.runLater(() -> statusLabel.setText("Failed: " + ex.getMessage()));
                }
            }).start();
        });

        HBox row = new HBox(10, name, removeBtn);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.getStyleClass().add("panel-item");
        row.setPadding(new Insets(10, 0, 10, 0));
        return row;
    }

    private String capitalize(String s) {
        if (s == null || s.isEmpty()) return s;
        return Character.toUpperCase(s.charAt(0)) + s.substring(1);
    }

    @FXML
    private void handleAddMember() {
        String userId = addMemberField.getText().trim();
        if (userId.isEmpty()) return;

        new Thread(() -> {
            try {
                ApiClient.addMember(groupId, userId);
                Platform.runLater(() -> {
                    addMemberField.clear();
                    loadGroup();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to add member: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleExcludeUser() {
        String userId = excludeUserField.getText().trim();
        if (userId.isEmpty()) return;

        new Thread(() -> {
            try {
                ApiClient.excludeUser(groupId, userId);
                Platform.runLater(() -> {
                    excludeUserField.clear();
                    loadExclusions();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to exclude user: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleStartDiscussion() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/discussions.fxml"));
            javafx.scene.Parent root = loader.load();
            DiscussionsController controller = loader.getController();
            controller.setGroup(groupId, groupName);
            javafx.stage.Stage stage = (javafx.stage.Stage) statusLabel.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to open discussions: " + e.getMessage());
        }
    }
    @FXML
    private void handleCreateQuiz() {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/createquiz.fxml"));
        javafx.scene.Parent root = loader.load();
        CreateQuizController controller = loader.getController();
        controller.setGroup(groupId, groupName);
        javafx.stage.Stage stage = (javafx.stage.Stage) statusLabel.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
        ThemeManager.applyTheme(scene);
        stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to open create quiz screen: " + e.getMessage());
    }
}

    @FXML
    private void handleToggleVisibility() {
        statusLabel.setText("This action isn't wired up yet.");
    }

    @FXML
    private void handleExitGroup() {
        new Thread(() -> {
            try {
                String msg = ApiClient.leaveGroup(groupId);
                Platform.runLater(() -> { statusLabel.setText(msg); goBack(null); });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to leave group: " + e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleDeleteGroup() {
        new Thread(() -> {
            try {
                String msg = ApiClient.deleteGroup(groupId);
                Platform.runLater(() -> { statusLabel.setText(msg); goBack(null); });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to delete group: " + e.getMessage()));
            }
        }).start();
    }
}