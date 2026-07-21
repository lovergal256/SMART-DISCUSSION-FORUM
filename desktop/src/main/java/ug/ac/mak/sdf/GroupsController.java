package ug.ac.mak.sdf;

import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.Label;
import javafx.scene.layout.VBox;
import javafx.scene.control.Alert;
import javafx.scene.control.ButtonType;
import javafx.scene.control.Dialog;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;
import javafx.scene.layout.GridPane;

import java.util.List;

public class GroupsController {

    @FXML private VBox groupsContainer;
    @FXML private Label statusLabel;
    @FXML private SideBarController sidebarController;

    @FXML
    public void initialize() {
        if (sidebarController != null) {
            sidebarController.setActiveItem("groups");
        }
        statusLabel.setText("Loading groups...");

        new Thread(() -> {
            try {
                List<ApiClient.Group> groups = ApiClient.getGroups();
                Platform.runLater(() -> {
                    groupsContainer.getChildren().clear();
                    for (ApiClient.Group g : groups) {
                        groupsContainer.getChildren().add(buildGroupCard(g));
                    }
                    statusLabel.setText(groups.size() + " group(s) loaded.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load groups: " + e.getMessage()));
            }
        }).start();
    }

    private VBox buildGroupCard(ApiClient.Group group) {
        Label title = new Label(group.name());
        title.getStyleClass().add("group-card-title");

        Label desc = new Label(group.description());
        desc.getStyleClass().add("group-card-desc");
        desc.setWrapText(true);

        VBox card = new VBox(4, title, desc);
        card.getStyleClass().add("group-card");
        card.setPadding(new Insets(14, 16, 14, 16));
        card.setOnMouseClicked(event -> openGroup(group));
        card.setStyle(card.getStyle() + "-fx-cursor: hand;");
        return card;
    }

    private void openGroup(ApiClient.Group group) {
    try {
        var loader = new javafx.fxml.FXMLLoader(getClass().getResource("/ug/ac/mak/sdf/group_detail.fxml"));
        javafx.scene.Parent root = loader.load();
        GroupDetailController controller = loader.getController();
        controller.setGroup(group.id());
        javafx.stage.Stage stage = (javafx.stage.Stage) groupsContainer.getScene().getWindow();
        javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
        ThemeManager.applyTheme(scene);
        stage.setScene(scene);
    } catch (Exception e) {
        statusLabel.setText("Failed to open group: " + e.getMessage());
    }
}
   @FXML
private void handleNewGroup() {
    Dialog<javafx.util.Pair<String, String>> dialog = new Dialog<>();
    dialog.setTitle("Create New Group");
    dialog.setHeaderText("Enter the group's details");

    ButtonType createButtonType = new ButtonType("Create", javafx.scene.control.ButtonBar.ButtonData.OK_DONE);
    dialog.getDialogPane().getButtonTypes().addAll(createButtonType, ButtonType.CANCEL);

    TextField nameField = new TextField();
    nameField.setPromptText("Group name");
    TextArea descField = new TextArea();
    descField.setPromptText("Description");
    descField.setPrefRowCount(3);

    GridPane grid = new GridPane();
    grid.setHgap(10);
    grid.setVgap(10);
    grid.setPadding(new Insets(20, 10, 10, 10));
    grid.add(new Label("Name:"), 0, 0);
    grid.add(nameField, 1, 0);
    grid.add(new Label("Description:"), 0, 1);
    grid.add(descField, 1, 1);

    dialog.getDialogPane().setContent(grid);

    dialog.setResultConverter(buttonType -> {
        if (buttonType == createButtonType) {
            return new javafx.util.Pair<>(nameField.getText(), descField.getText());
        }
        return null;
    });

    dialog.showAndWait().ifPresent(result -> {
        String name = result.getKey().trim();
        String description = result.getValue().trim();

        if (name.isEmpty()) {
            statusLabel.setText("Group name is required.");
            return;
        }

        statusLabel.setText("Creating group...");
        new Thread(() -> {
            try {
                ApiClient.Group newGroup = ApiClient.postGroup(name, description);
                Platform.runLater(() -> {
                    groupsContainer.getChildren().add(buildGroupCard(newGroup));
                    statusLabel.setText("Group created.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to create group: " + e.getMessage()));
            }
        }).start();
    });
} 
}