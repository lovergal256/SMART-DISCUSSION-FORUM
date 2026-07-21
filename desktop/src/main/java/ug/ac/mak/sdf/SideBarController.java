package ug.ac.mak.sdf;

import javafx.fxml.FXML;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.layout.HBox;
import javafx.scene.input.MouseEvent;
import javafx.stage.Stage;

public class SideBarController {

    @FXML private HBox navDashboard;
    @FXML private HBox navDiscussions;
    @FXML private HBox navGroups;
    @FXML private HBox navQuizzes;
    @FXML private HBox navRecommendations;
    @FXML private HBox navPerformance;
    @FXML private HBox navWarnings;
    @FXML private HBox navActivity;

    public void setActiveItem(String key) {
        for (HBox box : new HBox[]{navDashboard, navDiscussions, navGroups, navQuizzes,
                navRecommendations, navPerformance, navWarnings, navActivity}) {
            if (box != null) box.getStyleClass().setAll("sidebar-item");
        }

        HBox active = switch (key) {
            case "dashboard" -> navDashboard;
            case "discussions" -> navDiscussions;
            case "groups" -> navGroups;
            case "quizzes" -> navQuizzes;
            case "recommendations" -> navRecommendations;
            case "performance" -> navPerformance;
            case "warnings" -> navWarnings;
            case "activity" -> navActivity;
            default -> null;
        };
        if (active != null) {
            active.getStyleClass().setAll("sidebar-item-active");
        }
    }

    @FXML private void handleDashboard(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/dashboard.fxml"); }
    @FXML private void handleDiscussions(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/discussions_all.fxml"); }
    @FXML private void handleGroups(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/groups.fxml"); }
    @FXML private void handleQuizzes(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/quizzes_list.fxml"); }
    @FXML private void handleRecommendations(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/recommendations.fxml"); }
    @FXML private void handlePerformance(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/performance.fxml"); }
    @FXML private void handleWarnings(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/warnings.fxml"); }
    @FXML private void handleActivity(MouseEvent e) { navigateTo(e, "/ug/ac/mak/sdf/my_activity.fxml"); }

    @FXML
    private void handleLogout(MouseEvent event) {
        new Thread(() -> {
            try {
                ApiClient.logout();
            } catch (Exception e) {
                System.err.println("Logout API call failed: " + e.getMessage());
            }
            javafx.application.Platform.runLater(() -> navigateTo(event, "/ug/ac/mak/sdf/login.fxml"));
        }).start();
    }

    private void navigateTo(MouseEvent event, String fxmlPath) {
        try {
            var loader = new javafx.fxml.FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) ((javafx.scene.Node) event.getSource()).getScene().getWindow();
            javafx.scene.Scene scene = new javafx.scene.Scene(root, 900, 600);
            ThemeManager.applyTheme(scene);
            stage.setScene(scene);
        } catch (Throwable ex) {
            ex.printStackTrace();
        }
    }
}