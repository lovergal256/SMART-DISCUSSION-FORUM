package com.discussforum.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.stage.Stage;

public class SidebarController {

    @FXML private Button dashboardBtn;
    @FXML private Button groupsBtn;
    @FXML private Button performanceBtn;
    @FXML private Button activityBtn;

    private static final String ACTIVE_STYLE =
        "-fx-background-color: #0077b6; -fx-text-fill: white; -fx-padding: 10 15; "
        + "-fx-cursor: hand; -fx-alignment: CENTER_LEFT;";
    private static final String INACTIVE_STYLE =
        "-fx-background-color: transparent; -fx-text-fill: white; -fx-padding: 10 15; "
        + "-fx-cursor: hand; -fx-alignment: CENTER_LEFT;";

    /** Call this from each screen's controller after the sidebar loads, e.g. setActive("dashboard"). */
    public void setActive(String key) {
        dashboardBtn.setStyle(INACTIVE_STYLE);
        groupsBtn.setStyle(INACTIVE_STYLE);
        performanceBtn.setStyle(INACTIVE_STYLE);
        activityBtn.setStyle(INACTIVE_STYLE);

        switch (key) {
            case "dashboard" -> dashboardBtn.setStyle(ACTIVE_STYLE);
            case "groups" -> groupsBtn.setStyle(ACTIVE_STYLE);
            case "performance" -> performanceBtn.setStyle(ACTIVE_STYLE);
            case "activity" -> activityBtn.setStyle(ACTIVE_STYLE);
            default -> {}
        }
    }

    @FXML
    private void goToDashboard() {
        navigateTo("/com/discussforum/views/Dashboard.fxml");
    }

    @FXML
    private void goToMyGroups() {
        navigateTo("/com/discussforum/views/Groups.fxml");
    }

    private void navigateTo(String fxml) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxml));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) dashboardBtn.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}