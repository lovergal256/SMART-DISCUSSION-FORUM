package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

public class GroupMembersController {

    @FXML private Label titleLabel;
    @FXML private VBox membersContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    private String groupId;

    public void setGroupId(String groupId) {
        this.groupId = groupId;
        if (sidebarController != null) {
            sidebarController.setActiveItem("groups");
        }
        loadMembers();
    }

    private void loadMembers() {
        statusLabel.setText("Loading members...");

        new Thread(() -> {
            try {
                ApiClient.GroupMembersResponse resp = ApiClient.getGroupMembers(groupId);
                Platform.runLater(() -> render(resp));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load members: " + e.getMessage()));
            }
        }).start();
    }

    private void render(ApiClient.GroupMembersResponse resp) {
        titleLabel.setText(resp.groupName() + " — Members");
        membersContainer.getChildren().clear();

        for (ApiClient.GroupMember m : resp.members()) {
            membersContainer.getChildren().add(buildMemberRow(m, resp.isAdmin()));
        }

        statusLabel.setText(resp.members().size() + " member(s) loaded.");
    }

    private HBox buildMemberRow(ApiClient.GroupMember m, boolean viewerIsAdmin) {
        String suffix = (m.isCreator() ? " (Creator)" : "");
        Label name = new Label(m.fullName() + suffix);
        name.getStyleClass().add("panel-item-title");

        Label email = new Label(m.email());
        email.getStyleClass().add("panel-item-meta");

        VBox textBlock = new VBox(2, name, email);
        HBox.setHgrow(textBlock, Priority.ALWAYS);

        Label roleBadge = new Label(m.role());
        roleBadge.getStyleClass().add("role-badge");

        HBox row = new HBox(12, textBlock, roleBadge);
        row.setAlignment(javafx.geometry.Pos.CENTER_LEFT);
        row.getStyleClass().add("panel-item");
        row.setPadding(new Insets(10));

        if (viewerIsAdmin && !m.isCreator()) {
            Button blacklistBtn = new Button("Blacklist");
            blacklistBtn.getStyleClass().add("blacklist-button");
            blacklistBtn.setOnAction(e -> confirmAndBlacklist(m));
            row.getChildren().add(blacklistBtn);
        }

        return row;
    }

    private void confirmAndBlacklist(ApiClient.GroupMember m) {
        var alert = new javafx.scene.control.Alert(
                javafx.scene.control.Alert.AlertType.CONFIRMATION,
                "Blacklist " + m.fullName() + " for one month? They will be unable to use the platform.",
                javafx.scene.control.ButtonType.YES, javafx.scene.control.ButtonType.NO
        );
        alert.showAndWait().ifPresent(response -> {
            if (response == javafx.scene.control.ButtonType.YES) {
                doBlacklist(m);
            }
        });
    }

    private void doBlacklist(ApiClient.GroupMember m) {
        statusLabel.setText("Blacklisting " + m.fullName() + "...");

        new Thread(() -> {
            try {
                String message = ApiClient.blacklistGroupMember(groupId, m.userId());
                Platform.runLater(() -> {
                    statusLabel.setText(message);
                    loadMembers();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText(e.getMessage()));
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/groups.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.stage.Stage stage = (javafx.stage.Stage) membersContainer.getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + e.getMessage());
        }
    }
}