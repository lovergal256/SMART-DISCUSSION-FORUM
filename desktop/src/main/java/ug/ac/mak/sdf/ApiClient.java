package ug.ac.mak.sdf;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import ug.ac.mak.sdf.ApiClient.GroupMember;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.Map;
import java.util.List;

public class ApiClient {

    private static final String BASE_URL = "http://127.0.0.1:8000/api";
    private static String authToken;
    private static String currentUserId;
    private static int currentUserRole = -1;

    private static final HttpClient client = HttpClient.newHttpClient();
    private static final ObjectMapper mapper = new ObjectMapper();

    public static String login(String email, String password) throws Exception {
    String jsonBody = mapper.writeValueAsString(
            Map.of("email", email, "password", password)
    );

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/login"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Login failed (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    authToken = root.get("token").asText();
    JsonNode user = root.get("user");
    currentUserId = user.get("id").asText();
    currentUserRole = user.has("role") && !user.get("role").isNull() ? user.get("role").asInt(-1) : -1;
    return authToken;
}
public static String getCurrentUserId() {
    return currentUserId;
}

    public static String getToken() {
        return authToken;
    }
    public static boolean isLecturer() {
    return currentUserRole == 2;
}

public static int getCurrentUserRole() {
    return currentUserRole;
}
    public static void logout() throws Exception {
    if (authToken == null) return;

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/logout"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.noBody())
            .build();

    client.send(request, HttpResponse.BodyHandlers.ofString());
      authToken = null;
      currentUserId = null;
}
    public record Group(String id, String name, String description) {}

public static java.util.List<Group> getGroups() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load groups (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    java.util.List<Group> groups = new java.util.ArrayList<>();
    for (JsonNode g : root) {
        groups.add(new Group(
            g.get("GroupID").asText(),
            g.get("GroupName").asText(),
            g.has("Description") && !g.get("Description").isNull() ? g.get("Description").asText() : ""
        ));
    }
    return groups;
}
public record Discussion(String id, String title, String description, String userId, String groupName) {}

public static List<Discussion> getDiscussions(String groupId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/discussions"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load discussions (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<Discussion> discussions = new java.util.ArrayList<>();
    for (JsonNode d : root) {
      discussions.add(new Discussion(
    d.get("DiscussionID").asText(),
    d.get("Title").asText(),
    d.has("Description") && !d.get("Description").isNull() ? d.get("Description").asText() : "",
    d.has("UserID") ? d.get("UserID").asText() : "",
    ""
));  
    }
    return discussions;
}
public static Discussion getDiscussion(String discussionId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/discussions/" + discussionId))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load discussion (" + response.statusCode() + "): " + response.body());
    }

    JsonNode d = mapper.readTree(response.body());
    return new Discussion(
        d.get("DiscussionID").asText(),
        d.get("Title").asText(),
        d.has("Description") && !d.get("Description").isNull() ? d.get("Description").asText() : "",
        d.has("UserID") ? d.get("UserID").asText() : "",
        d.has("GroupName") && !d.get("GroupName").isNull() ? d.get("GroupName").asText() : ""
    );
}
public record AllDiscussionsItem(String id, String title, String description, String userId,
                                  String groupId, String groupName, String authorName, int topicCount) {}

public static List<AllDiscussionsItem> getAllDiscussions(String search) throws Exception {
    String url = BASE_URL + "/discussions";
    if (search != null && !search.isBlank()) {
        url += "?search=" + java.net.URLEncoder.encode(search, java.nio.charset.StandardCharsets.UTF_8);
    }

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(url))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load discussions (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<AllDiscussionsItem> items = new java.util.ArrayList<>();
    for (JsonNode d : root) {
        items.add(new AllDiscussionsItem(
            d.get("DiscussionID").asText(),
            d.get("Title").asText(),
            d.has("Description") && !d.get("Description").isNull() ? d.get("Description").asText() : "",
            d.has("UserID") ? d.get("UserID").asText() : "",
            d.has("GroupID") ? d.get("GroupID").asText() : "",
            d.has("GroupName") ? d.get("GroupName").asText() : "",
            d.has("AuthorName") ? d.get("AuthorName").asText() : "",
            d.has("TopicCount") ? d.get("TopicCount").asInt(0) : 0
        ));
    }
    return items;
}
public record Topic(String id, String title, String description, String status, String userId) {}

public static List<Topic> getTopics(String discussionId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/discussions/" + discussionId + "/topics"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load topics (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<Topic> topics = new java.util.ArrayList<>();
    for (JsonNode t : root) {
        topics.add(new Topic(
            t.get("TopicID").asText(),
            t.get("Title").asText(),
            t.has("Description") && !t.get("Description").isNull() ? t.get("Description").asText() : "",
            t.has("Status") && !t.get("Status").isNull() ? t.get("Status").asText() : "open",
            t.has("UserID") ? t.get("UserID").asText() : ""
        ));
    }
    return topics;
}
public record Post(String id, String content, String datePosted, String userId) {}

public static List<Post> getPosts(String topicId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/topics/" + topicId + "/posts"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load posts (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<Post> posts = new java.util.ArrayList<>();
    for (JsonNode p : root) {
        posts.add(new Post(
            p.get("PostID").asText(),
            p.get("content").asText(),
            p.has("DatePosted") && !p.get("DatePosted").isNull() ? p.get("DatePosted").asText() : "",
            p.has("UserID") ? p.get("UserID").asText() : ""
        ));
    }
    return posts;
}
public record Reply(String id, String body, String parentReplyId, String userId, String createdAt) {}

public static List<Reply> getReplies(String postId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/posts/" + postId + "/replies"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load replies (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<Reply> replies = new java.util.ArrayList<>();
    for (JsonNode r : root) {
        replies.add(new Reply(
            r.get("ReplyID").asText(),
            r.get("Body").asText(),
            r.has("ParentReplyID") && !r.get("ParentReplyID").isNull() ? r.get("ParentReplyID").asText() : null,
            r.has("UserID") ? r.get("UserID").asText() : "",
            r.has("created_at") && !r.get("created_at").isNull() ? r.get("created_at").asText() : ""
        ));
    }
    return replies;
}
public static Reply postReply(String postId, String body, String parentReplyId) throws Exception {
    Map<String, Object> payload = new java.util.HashMap<>();
    payload.put("PostID", postId);
    payload.put("Body", body);
    if (parentReplyId != null && !parentReplyId.isEmpty()) {
        payload.put("ParentReplyID", parentReplyId);
    }

    String jsonBody = mapper.writeValueAsString(payload);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/replies"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 201) {
        throw new RuntimeException("Failed to post reply (" + response.statusCode() + "): " + response.body());
    }

    JsonNode r = mapper.readTree(response.body());
    return new Reply(
        r.get("ReplyID").asText(),
        r.get("Body").asText(),
        r.has("ParentReplyID") && !r.get("ParentReplyID").isNull() ? r.get("ParentReplyID").asText() : null,
        r.has("UserID") ? r.get("UserID").asText() : "",
        r.has("created_at") && !r.get("created_at").isNull() ? r.get("created_at").asText() : ""
    );
}
public record DashboardUser(String name, String role, String initials) {}
public record StatCard(String icon, String value, String label, String change) {}
public record RecentDiscussion(String id, String category, String title, String author, String postedAt, int replies) {}
public record UpcomingQuiz(String id, String title, String subtitle, String due) {}
public record Recommendation(String icon, String title, String subtitle) {}
public record DashboardGroup(String id, String name, int members, int newPosts, String status) {}
public record ActivityItem(String icon, String label, String value, String change) {}

public record ChartPoint(double x, double y) {}

public record Dashboard(
    DashboardUser user,
    List<StatCard> stats,
    List<RecentDiscussion> discussions,
    List<UpcomingQuiz> quizzes,
    List<Recommendation> recommendations,
    List<DashboardGroup> groups,
    List<ActivityItem> activity,
    List<ChartPoint> activityChartPoints,
    int unreadNotifications
) {}

public static Dashboard getDashboard() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/dashboard"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load dashboard (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());

    DashboardUser user = new DashboardUser(
        root.path("user").path("name").asText(""),
        root.path("user").path("role").asText("Student"),
        root.path("user").path("initials").asText("?")
    );

    List<StatCard> stats = new java.util.ArrayList<>();
    for (JsonNode s : root.path("stats")) {
        stats.add(new StatCard(
            s.path("icon").asText(""),
            s.path("value").asText(""),
            s.path("label").asText(""),
            s.path("change").asText("")
        ));
    }

    List<RecentDiscussion> discussions = new java.util.ArrayList<>();
    for (JsonNode d : root.path("discussions")) {
        discussions.add(new RecentDiscussion(
            d.path("id").asText(""),
            d.path("category").asText(""),
            d.path("title").asText(""),
            d.path("author").asText(""),
            d.path("posted_at").asText(""),
            d.path("replies").asInt(0)
        ));
    }

    List<UpcomingQuiz> quizzes = new java.util.ArrayList<>();
    for (JsonNode q : root.path("quizzes")) {
        quizzes.add(new UpcomingQuiz(
            q.path("id").asText(""),
            q.path("title").asText(""),
            q.path("subtitle").asText(""),
            q.path("due").asText("")
        ));
    }

    List<Recommendation> recommendations = new java.util.ArrayList<>();
    for (JsonNode r : root.path("recommendations")) {
        recommendations.add(new Recommendation(
            r.path("icon").asText(""),
            r.path("title").asText(""),
            r.path("subtitle").asText("")
        ));
    }

    List<DashboardGroup> groups = new java.util.ArrayList<>();
    for (JsonNode g : root.path("groups")) {
        groups.add(new DashboardGroup(
            g.path("id").asText(""),
            g.path("name").asText(""),
            g.path("members").asInt(0),
            g.path("new_posts").asInt(0),
            g.path("status").asText("")
        ));
    }

    List<ActivityItem> activity = new java.util.ArrayList<>();
    for (JsonNode a : root.path("activity")) {
        activity.add(new ActivityItem(
            a.path("icon").asText(""),
            a.path("label").asText(""),
            a.path("value").asText(""),
            a.path("change").asText("")
        ));
    }

    List<ChartPoint> chartPoints = new java.util.ArrayList<>();
    for (JsonNode p : root.path("activityChartPoints")) {
        chartPoints.add(new ChartPoint(p.path("x").asDouble(), p.path("y").asDouble()));
    }

    int unread = root.path("unreadNotifications").asInt(0);

   return new Dashboard(user, stats, discussions, quizzes, recommendations, groups, activity, chartPoints, unread);
}

public record QuizListItem(String id, String title, String groupName, String startTime, String endTime,
                           int duration, int questionsCount, String status, boolean attempted,
                           int attemptCount, Double averageScore, boolean resultsReleased) {}

public record QuizQuestion(String id, String questionText, String optionA, String optionB,
                           String optionC, String optionD, int marks,
                           String selectedOption, boolean isCorrect, String correctOption) {}

public record QuizDetail(String id, String title, String groupId, String groupName, int duration,
                         String startTime, String endTime, String serverTime, String status,
                         boolean active, boolean attempted, boolean resultsReleased, double score,
                         List<QuizQuestion> questions) {}

public record QuizResultDetail(String id, String title, double score, List<QuizQuestion> questions) {}

public record QuizReviewDetail(String id, String title, String groupName, String startTime, String endTime,
                               int duration, int questionsCount, String status, int attemptCount,
                               Double averageScore, Double highestScore, Double lowestScore,
                               boolean resultsReleased) {}

public record QuizQuestionInput(String questionText, String optionA, String optionB, String optionC,
                                String optionD, String correctOption, int marks) {}

public static List<QuizListItem> getQuizzes() throws Exception {
    String endpoint = isLecturer() ? "/lecturer/quizzes" : "/quizzes";

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + endpoint))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load quizzes (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    JsonNode itemsNode = root.isArray() ? root : root.path("quizzes");

    List<QuizListItem> items = new java.util.ArrayList<>();
    for (JsonNode q : itemsNode) {
        items.add(new QuizListItem(
            q.path("id").asText(""),
            q.path("title").asText(""),
            q.path("group_name").asText(""),
            q.path("start_time").asText(""),
            q.path("end_time").asText(""),
            q.path("duration").asInt(0),
            q.path("questions_count").asInt(0),
            q.path("status").asText("upcoming"),
            q.path("attempted").asBoolean(false),
            q.path("attempt_count").asInt(0),
            q.has("average_score") && !q.get("average_score").isNull() ? q.get("average_score").asDouble() : null,
            q.path("results_released").asBoolean(false)
        ));
    }
    return items;
}

public static QuizDetail getQuiz(String quizId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/quizzes/" + quizId))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load quiz (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<QuizQuestion> questions = new java.util.ArrayList<>();
    for (JsonNode q : root.path("questions")) {
        questions.add(new QuizQuestion(
            q.path("id").asText(""),
            q.path("text").asText(""),
            q.path("option_a").asText(""),
            q.path("option_b").asText(""),
            q.path("option_c").asText(""),
            q.path("option_d").asText(""),
            q.path("marks").asInt(0),
            q.path("selected_option").asText(""),
            q.path("is_correct").asBoolean(false),
            q.path("correct_option").asText("")
        ));
    }

    return new QuizDetail(
        root.path("id").asText(""),
        root.path("title").asText(""),
        root.path("group_id").asText(""),
        root.path("group_name").asText(""),
        root.path("duration").asInt(0),
        root.path("start_time").asText(""),
        root.path("end_time").asText(""),
        root.path("server_time").asText(""),
        root.path("status").asText("upcoming"),
        root.path("is_active").asBoolean(false),
        root.path("is_attempted").asBoolean(false),
        root.path("results_released").asBoolean(false),
        root.path("score").asDouble(0),
        questions
    );
}

public static double submitQuizAttempt(String quizId, Map<String, String> answers) throws Exception {
    Map<String, Object> body = new java.util.HashMap<>();
    body.put("answers", answers);
    String json = mapper.writeValueAsString(body);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/quizzes/" + quizId + "/attempts"))
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(json))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to submit quiz (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    return root.path("score").asDouble(0);
}

public static QuizResultDetail getQuizResults(String quizId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/quizzes/" + quizId + "/results"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load quiz results (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<QuizQuestion> questions = new java.util.ArrayList<>();
    for (JsonNode q : root.path("questions")) {
        questions.add(new QuizQuestion(
            q.path("id").asText(""),
            q.path("text").asText(""),
            q.path("option_a").asText(""),
            q.path("option_b").asText(""),
            q.path("option_c").asText(""),
            q.path("option_d").asText(""),
            q.path("marks").asInt(0),
            q.path("selected_option").asText(""),
            q.path("is_correct").asBoolean(false),
            q.path("correct_option").asText("")
        ));
    }

    return new QuizResultDetail(
        root.path("id").asText(""),
        root.path("title").asText(""),
        root.path("score").asDouble(0),
        questions
    );
}

public static void createQuiz(String groupId, String title, String startTime, int duration,
                              List<QuizQuestionInput> questions) throws Exception {
    Map<String, Object> body = new java.util.HashMap<>();
    body.put("title", title);
    body.put("start_time", startTime);
    body.put("duration", duration);

    List<Map<String, Object>> questionPayloads = new java.util.ArrayList<>();
    for (QuizQuestionInput question : questions) {
        Map<String, Object> payload = new java.util.HashMap<>();
        payload.put("question_text", question.questionText());
        payload.put("option_a", question.optionA());
        payload.put("option_b", question.optionB());
        payload.put("option_c", question.optionC());
        payload.put("option_d", question.optionD());
        payload.put("correct_option", question.correctOption());
        payload.put("marks", question.marks());
        questionPayloads.add(payload);
    }
    body.put("questions", questionPayloads);

    String json = mapper.writeValueAsString(body);
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/quizzes"))
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(json))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 201) {
        throw new RuntimeException("Failed to create quiz (" + response.statusCode() + "): " + response.body());
    }
}

public static QuizReviewDetail getQuizReview(String quizId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/quizzes/" + quizId + "/review"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load quiz review (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    return new QuizReviewDetail(
        root.path("id").asText(""),
        root.path("title").asText(""),
        root.path("group_name").asText(""),
        root.path("start_time").asText(""),
        root.path("end_time").asText(""),
        root.path("duration").asInt(0),
        root.path("questions_count").asInt(0),
        root.path("status").asText("upcoming"),
        root.path("attempt_count").asInt(0),
        root.has("average_score") && !root.get("average_score").isNull() ? root.get("average_score").asDouble() : null,
        root.has("highest_score") && !root.get("highest_score").isNull() ? root.get("highest_score").asDouble() : null,
        root.has("lowest_score") && !root.get("lowest_score").isNull() ? root.get("lowest_score").asDouble() : null,
        root.path("results_released").asBoolean(false)
    );
}

public static String releaseQuizResults(String quizId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/quizzes/" + quizId + "/release-results"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.noBody())
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";

    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }

    return message;
}
public record TrendingTopic(String id, String title, String status, int postCount) {}
public record SuggestedGroup(String id, String name, String description) {}
public record ActivePost(String id, String content, String topicTitle, String datePosted) {}

public record Recommendations(
    List<TrendingTopic> trendingTopics,
    List<SuggestedGroup> suggestedGroups,
    List<ActivePost> activePosts
) {}

public static Recommendations getRecommendations() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/recommendations"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load recommendations (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());

    List<TrendingTopic> trending = new java.util.ArrayList<>();
    for (JsonNode t : root.path("trendingTopics")) {
        trending.add(new TrendingTopic(
            t.get("TopicID").asText(),
            t.get("Title").asText(),
            t.has("Status") && !t.get("Status").isNull() ? t.get("Status").asText() : "open",
            t.has("post_count") ? t.get("post_count").asInt(0) : 0
        ));
    }

    List<SuggestedGroup> groups = new java.util.ArrayList<>();
    for (JsonNode g : root.path("suggestedGroups")) {
        groups.add(new SuggestedGroup(
            g.get("GroupID").asText(),
            g.get("GroupName").asText(),
            g.has("Description") && !g.get("Description").isNull() ? g.get("Description").asText() : ""
        ));
    }

    List<ActivePost> posts = new java.util.ArrayList<>();
    for (JsonNode p : root.path("activePosts")) {
        posts.add(new ActivePost(
            p.get("PostID").asText(),
            p.has("content") && !p.get("content").isNull() ? p.get("content").asText() : "",
            p.has("TopicTitle") && !p.get("TopicTitle").isNull() ? p.get("TopicTitle").asText() : "",
            p.has("DatePosted") && !p.get("DatePosted").isNull() ? p.get("DatePosted").asText() : ""
        ));
    }

    return new Recommendations(trending, groups, posts);
}
public record RecentQuizResult(String quizId, String quizTitle, double score, String dateRecorded) {}

public record Performance(
    int topicsCreated, int postsCreated, int repliesMade, int participationScore,
    int quizzesAttempted, int averageQuizScore, int highestScore, int quizMarks,
    int overallMarks, String grade, String status,
    List<RecentQuizResult> recentQuizzes
) {}

public static Performance getPerformance() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/performance"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load performance (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());

    List<RecentQuizResult> recent = new java.util.ArrayList<>();
    for (JsonNode q : root.path("recentQuizzes")) {
        recent.add(new RecentQuizResult(
            q.get("QuizID").asText(),
            q.get("QuizTitle").asText(),
            q.get("Score").asDouble(0),
            q.has("DateRecorded") ? q.get("DateRecorded").asText() : ""
        ));
    }

    return new Performance(
        root.path("topicsCreated").asInt(0),
        root.path("postsCreated").asInt(0),
        root.path("repliesMade").asInt(0),
        root.path("participationScore").asInt(0),
        root.path("quizzesAttempted").asInt(0),
        root.path("averageQuizScore").asInt(0),
        root.path("highestScore").asInt(0),
        root.path("quizMarks").asInt(0),
        root.path("overallMarks").asInt(0),
        root.path("grade").asText("D"),
        root.path("status").asText("Needs Improvement"),
        recent
    );
}
public record GroupMember(int userId, String fullName, String email, String role, boolean isCreator) {}
public record GroupMembersResponse(String groupId, String groupName, boolean isAdmin, List<GroupMember> members) {}

public static GroupMembersResponse getGroupMembers(String groupId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/members"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load members (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<GroupMember> members = new java.util.ArrayList<>();
    for (JsonNode m : root.path("Members")) {
        members.add(new GroupMember(
            m.get("UserID").asInt(),
            m.get("FullName").asText(),
            m.get("Email").asText(),
            m.get("Role").asText(),
            m.get("IsCreator").asBoolean(false)
        ));
    }

    return new GroupMembersResponse(
        root.get("GroupID").asText(),
        root.get("GroupName").asText(),
        root.path("IsAdmin").asBoolean(false),
        members
    );
}

public static String blacklistGroupMember(String groupId, int userId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/members/" + userId + "/blacklist"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.noBody())
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";

    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }

    return message;
}
public static String leaveGroup(String groupId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/leave"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .DELETE()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";
    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }
    return message;
}

public static String deleteGroup(String groupId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .DELETE()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";
    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }
    return message;
}
public record WarningItem(String id, int warningNumber, String warningDate) {}
public record ActiveBlacklist(String id, String reason, String startDate, String endDate) {}
public record WarningsResponse(List<WarningItem> warnings, ActiveBlacklist activeBlacklist) {}

public static WarningsResponse getWarnings() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/warnings"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load warnings (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());

    List<WarningItem> warnings = new java.util.ArrayList<>();
    for (JsonNode w : root.path("warnings")) {
        warnings.add(new WarningItem(
            w.get("WarningID").asText(),
            w.get("WarningNumber").asInt(),
            w.get("WarningDate").asText()
        ));
    }

    ActiveBlacklist blacklist = null;
    JsonNode b = root.path("activeBlacklist");
    if (b.isObject() && !b.isNull()) {
        blacklist = new ActiveBlacklist(
            b.get("BlacklistID").asText(),
            b.get("Reason").asText(),
            b.get("StartDate").asText(),
            b.get("EndDate").asText()
        );
    }

    return new WarningsResponse(warnings, blacklist);
}
      public record ActivitySummary(int postsCreated, int topicsCreated, int groupsCreated,
                               int quizzesAttempted, int groupsJoined) {}

public static ActivitySummary getActivitySummary() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/activity"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load activity (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());

    return new ActivitySummary(
        root.path("postsCreated").asInt(0),
        root.path("topicsCreated").asInt(0),
        root.path("groupsCreated").asInt(0),
        root.path("quizzesAttempted").asInt(0),
        root.path("groupsJoined").asInt(0)
    );
}
public record NotificationItem(String id, String message, String type, String status, String createdAt) {}

public static List<NotificationItem> getNotifications() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/notifications"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load notifications (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<NotificationItem> items = new java.util.ArrayList<>();
    for (JsonNode n : root) {
        items.add(new NotificationItem(
            n.get("NotificationID").asText(),
            n.get("Message").asText(),
            n.has("Type") && !n.get("Type").isNull() ? n.get("Type").asText() : "",
            n.has("Status") && !n.get("Status").isNull() ? n.get("Status").asText() : "Unread",
            n.has("CreatedAt") && !n.get("CreatedAt").isNull() ? n.get("CreatedAt").asText() : ""
        ));
    }
    return items;
}

public static void markNotificationRead(String notificationId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/notifications/" + notificationId + "/read"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.noBody())
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to mark notification as read (" + response.statusCode() + "): " + response.body());
    }
}
public record ProfileData(int userId, String fullName, String email, String theme, String role, int roleId, String dateJoined) {}

public static ProfileData getProfile() throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/profile"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load profile (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    return new ProfileData(
        root.get("UserID").asInt(),
        root.get("FullName").asText(),
        root.get("Email").asText(),
        root.has("Theme") && !root.get("Theme").isNull() ? root.get("Theme").asText() : "light",
        root.has("Role") && !root.get("Role").isNull() ? root.get("Role").asText() : "student",
        root.has("RoleID") ? root.get("RoleID").asInt(0) : 0,
        root.has("DateJoined") && !root.get("DateJoined").isNull() ? root.get("DateJoined").asText() : ""
    );
}

public static String updateProfile(String fullName, String theme) throws Exception {
    Map<String, Object> body = Map.of("FullName", fullName, "Theme", theme);
    String json = mapper.writeValueAsString(body);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/profile/update"))
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(json))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";
    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }
    return message;
}

public static String changePassword(String currentPassword, String newPassword) throws Exception {
    Map<String, Object> body = new java.util.HashMap<>();
    body.put("current_password", currentPassword);
    body.put("new_password", newPassword);
    body.put("new_password_confirmation", newPassword);
    String json = mapper.writeValueAsString(body);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/profile/change-password"))
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(json))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";
    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }
    return message;
}

public static String deleteAccount(String confirmPassword) throws Exception {
    Map<String, Object> body = Map.of("delete_confirm_password", confirmPassword);
    String json = mapper.writeValueAsString(body);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/profile/delete"))
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .method("DELETE", HttpRequest.BodyPublishers.ofString(json))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    String message = root.has("message") ? root.get("message").asText() : "";
    if (response.statusCode() != 200) {
        throw new RuntimeException(message.isBlank() ? ("Failed (" + response.statusCode() + ")") : message);
    }
    authToken = null;
    return message;
}
public static Group postGroup(String name, String description) throws Exception {
    Map<String, Object> payload = new java.util.HashMap<>();
    payload.put("GroupName", name);
    payload.put("Description", description);

    String jsonBody = mapper.writeValueAsString(payload);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 201) {
        throw new RuntimeException("Failed to create group (" + response.statusCode() + "): " + response.body());
    }

    JsonNode g = mapper.readTree(response.body());
    return new Group(
        g.get("GroupID").asText(),
        g.get("GroupName").asText(),
        g.has("Description") && !g.get("Description").isNull() ? g.get("Description").asText() : ""
    );
}
public record PendingRequest(String userId, String fullName, String email) {}
public record GroupDiscussionSummary(String id, String title) {}

public record GroupDetail(String id, String name, String description, String visibility, boolean isAdmin,
                           boolean hasPendingRequest, List<GroupMember> members,
                           List<PendingRequest> pendingRequests, List<GroupDiscussionSummary> discussions) {}

public static GroupDetail getGroupDetail(String groupId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load group (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());

    List<GroupMember> members = new java.util.ArrayList<>();
    for (JsonNode m : root.get("Members")) {
        members.add(new GroupMember(
            m.get("UserID").asInt(),
            m.get("FullName").asText(),
            m.get("Email").asText(),
            m.get("Role").asText(),
            m.get("IsCreator").asBoolean(false)
        ));
    }

    List<PendingRequest> pending = new java.util.ArrayList<>();
    for (JsonNode p : root.path("PendingRequests")) {
        pending.add(new PendingRequest(
            p.get("UserID").asText(),
            p.get("FullName").asText(),
            p.get("Email").asText()
        ));
    }

    List<GroupDiscussionSummary> discussions = new java.util.ArrayList<>();
    for (JsonNode d : root.path("Discussions")) {
        discussions.add(new GroupDiscussionSummary(
            d.get("DiscussionID").asText(),
            d.get("Title").asText()
        ));
    }

    return new GroupDetail(
        root.get("GroupID").asText(),
        root.get("GroupName").asText(),
        root.has("Description") && !root.get("Description").isNull() ? root.get("Description").asText() : "",
        root.get("Visibility").asText(),
        root.get("IsAdmin").asBoolean(),
        root.path("HasPendingRequest").asBoolean(false),
        members,
        pending,
        discussions
    );
}

public static void addMember(String groupId, String userId) throws Exception {
    String jsonBody = mapper.writeValueAsString(Map.of("user_id", userId));
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/members"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();
    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to add member (" + response.statusCode() + "): " + response.body());
    }
}

public static void approveMember(String groupId, String userId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/members/" + userId + "/approve"))
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.noBody())
            .build();
    client.send(request, HttpResponse.BodyHandlers.ofString());
}

public static void rejectMember(String groupId, String userId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/members/" + userId + "/reject"))
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.noBody())
            .build();
    client.send(request, HttpResponse.BodyHandlers.ofString());
}
public record ExclusionItem(String id, String excludedUserId, String excludedUserName) {}

public static List<ExclusionItem> getExclusions(String groupId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/exclusions"))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .GET()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to load exclusions (" + response.statusCode() + "): " + response.body());
    }

    JsonNode root = mapper.readTree(response.body());
    List<ExclusionItem> items = new java.util.ArrayList<>();
    for (JsonNode e : root) {
        items.add(new ExclusionItem(
            e.get("ExclusionID").asText(),
            e.get("ExcludedUserID").asText(),
            e.get("ExcludedUserName").asText()
        ));
    }
    return items;
}

public static void excludeUser(String groupId, String userId) throws Exception {
    String jsonBody = mapper.writeValueAsString(Map.of("excluded_user_id", userId));
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/exclusions"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
    JsonNode root = mapper.readTree(response.body());
    if (response.statusCode() != 201) {
        String message = root.has("message") ? root.get("message").asText() : "Failed to exclude user";
        throw new RuntimeException(message);
    }
}

public static void removeExclusion(String groupId, String exclusionId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/groups/" + groupId + "/exclusions/" + exclusionId))
            .header("Authorization", "Bearer " + authToken)
            .DELETE()
            .build();
    client.send(request, HttpResponse.BodyHandlers.ofString());
}
public static Topic createTopic(String discussionId, String title, String description) throws Exception {
    Map<String, Object> payload = new java.util.HashMap<>();
    payload.put("Title", title);
    payload.put("Description", description);

    String jsonBody = mapper.writeValueAsString(payload);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/discussions/" + discussionId + "/topics"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 201) {
        throw new RuntimeException("Failed to create topic (" + response.statusCode() + "): " + response.body());
    }

    JsonNode t = mapper.readTree(response.body());
    return new Topic(
        t.get("TopicID").asText(),
        t.get("Title").asText(),
        t.has("Description") && !t.get("Description").isNull() ? t.get("Description").asText() : "",
        t.has("Status") && !t.get("Status").isNull() ? t.get("Status").asText() : "open",
        t.has("UserID") ? t.get("UserID").asText() : ""
    );
}
public static Topic updateTopic(String topicId, String title, String description) throws Exception {
    Map<String, Object> payload = new java.util.HashMap<>();
    payload.put("Title", title);
    payload.put("Description", description);

    String jsonBody = mapper.writeValueAsString(payload);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/topics/" + topicId))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .method("PUT", HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to update topic (" + response.statusCode() + "): " + response.body());
    }

    JsonNode t = mapper.readTree(response.body());
    return new Topic(
        t.get("TopicID").asText(),
        t.get("Title").asText(),
        t.has("Description") && !t.get("Description").isNull() ? t.get("Description").asText() : "",
        t.has("Status") && !t.get("Status").isNull() ? t.get("Status").asText() : "open",
        t.has("UserID") ? t.get("UserID").asText() : ""
    );
}

public static void deleteTopic(String topicId) throws Exception {
    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/topics/" + topicId))
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .DELETE()
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 200) {
        throw new RuntimeException("Failed to delete topic (" + response.statusCode() + "): " + response.body());
    }
}

public static Post createPost(String topicId, String content) throws Exception {
    Map<String, Object> payload = new java.util.HashMap<>();
    payload.put("content", content);

    String jsonBody = mapper.writeValueAsString(payload);

    HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(BASE_URL + "/topics/" + topicId + "/posts"))
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .header("Authorization", "Bearer " + authToken)
            .POST(HttpRequest.BodyPublishers.ofString(jsonBody))
            .build();

    HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

    if (response.statusCode() != 201) {
        throw new RuntimeException("Failed to create post (" + response.statusCode() + "): " + response.body());
    }

    JsonNode p = mapper.readTree(response.body());
    return new Post(
        p.get("PostID").asText(),
        p.get("content").asText(),
        p.has("DatePosted") && !p.get("DatePosted").isNull() ? p.get("DatePosted").asText() : "",
        p.has("UserID") ? p.get("UserID").asText() : ""
    );
}

}