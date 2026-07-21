package com.discussforum.services;

import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;

public class ApiService {

    private static final String BASE_URL = "http://127.0.0.1:8000/api";
    private static String authToken = null;
    private static String currentUserName = null;
    private static int currentUserRole = -1;
    private static final HttpClient client = HttpClient.newHttpClient();
    private static final Gson gson = new Gson();

    public static String getToken() { return authToken; }
    public static String getCurrentUserName() { return currentUserName != null ? currentUserName : ""; }
    public static int getCurrentUserRole() { return currentUserRole; }
    public static boolean isLecturer() { return currentUserRole == 2; }
public static boolean isAdmin() { return currentUserRole == 3; }

    public static void logout() {
        authToken = null;
        currentUserName = null;
        currentUserRole = -1;
    }

    public static JsonObject login(String email, String password) throws Exception {
        JsonObject body = new JsonObject();
        body.addProperty("email", email);
        body.addProperty("password", password);

        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/login"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .POST(HttpRequest.BodyPublishers.ofString(body.toString()))
            .build();

        HttpResponse<String> response = client.send(request,
            HttpResponse.BodyHandlers.ofString());

        JsonObject result = gson.fromJson(response.body(), JsonObject.class);

        if (response.statusCode() == 200) {
            authToken = result.get("token").getAsString();
            JsonObject user = result.getAsJsonObject("user");
            currentUserName = user.get("name").getAsString();
            if (user.has("role") && !user.get("role").isJsonNull()) {
                currentUserRole = user.get("role").getAsInt();
            }
        }

        return result;
    }

    public static JsonObject get(String endpoint) throws Exception {
        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + endpoint))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

        HttpResponse<String> response = client.send(request,
            HttpResponse.BodyHandlers.ofString());

        return gson.fromJson(response.body(), JsonObject.class);
    }

    public static JsonArray getArray(String endpoint) throws Exception {
        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + endpoint))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

        HttpResponse<String> response = client.send(request,
            HttpResponse.BodyHandlers.ofString());

        return gson.fromJson(response.body(), JsonArray.class);
    }

    public static JsonObject post(String endpoint, JsonObject body) throws Exception {
        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + endpoint))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(
                body != null ? body.toString() : "{}"))
            .build();

        HttpResponse<String> response = client.send(request,
            HttpResponse.BodyHandlers.ofString());

        return gson.fromJson(response.body(), JsonObject.class);
    }
}