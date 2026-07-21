package ug.ac.mak.sdf;

import javafx.scene.Scene;

public class ThemeManager {

    private static String currentTheme = "light";
    private static final String DARK_STYLESHEET = "/ug/ac/mak/sdf/dark-style.css";

    public static String getCurrentTheme() {
        return currentTheme;
    }

    public static void setTheme(String theme) {
        currentTheme = (theme == null || theme.isBlank()) ? "light" : theme;
    }

   public static void applyTheme(Scene scene) {
        if (scene == null) return;
        javafx.scene.Parent root = scene.getRoot();
        if (root == null) return;

        String darkUrl = ThemeManager.class.getResource(DARK_STYLESHEET).toExternalForm();
        root.getStylesheets().remove(darkUrl);

        if ("dark".equalsIgnoreCase(currentTheme)) {
            root.getStylesheets().add(darkUrl);
        }
    }
}