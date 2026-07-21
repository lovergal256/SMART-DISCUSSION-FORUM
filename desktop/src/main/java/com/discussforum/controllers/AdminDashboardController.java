package com.discussforum.controllers;

import com.discussforum.services.ApiService;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Label;
import javafx.stage.Stage;

public class AdminDashboardController {

    @FXML private Label userLabel;
    @FXML private Label statusLabel;
    @FXML private Label totalUsersLabel;
    @FXML private Label totalLecturersLabel;
    @FXML private Label totalStudentsLabel;
    @FXML private Label totalGroupsLabel;
    @FXML private Label totalDiscussionsLabel;

    @FXML
public void initialize() {
    new Thread(() -> {
        try {
            JsonObject stats = ApiService.get("/admin/dashboard");

            Platform.runLater(() -> {
                statusLabel.setText("");
                totalUsersLabel.setText(String.valueOf(stats.get("totalUsers").getAsInt()));
                totalLecturersLabel.setText(String.valueOf(stats.get("totalLecturers").getAsInt()));
                totalStudentsLabel.setText(String.valueOf(stats.get("totalStudents").getAsInt()));
                totalGroupsLabel.setText(String.valueOf(stats.get("totalGroups").getAsInt()));
                totalDiscussionsLabel.setText(String.valueOf(stats.get("totalDiscussions").getAsInt()));
            });
        } catch (Exception e) {
            Platform.runLater(() -> statusLabel.setText("Error loading stats: " + e.getMessage()));
        }
    }).start();
}

    @FXML
    private void handleLogout() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/Login.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }

    @FXML
    private void openRegisterLecturer() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/RegisterLecturer.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }

    @FXML
    private void openViewGroups() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/AdminGroups.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }

    @FXML
    private void openViewDiscussions() {
        try {
            FXMLLoader loader = new FXMLLoader(
                getClass().getResource("/com/discussforum/views/AdminDiscussions.fxml"));
            Scene scene = new Scene(loader.load(), 900, 600);
            Stage stage = (Stage) userLabel.getScene().getWindow();
            stage.setScene(scene);
        } catch (Exception e) {
            statusLabel.setText("Error: " + e.getMessage());
        }
    }
}