package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.LoginListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserValidationListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class AuthManager {
    private static AuthManager instance = null;
    private static RequestQueue queue;

    public static int MIN_LENGTH_USERNAME = 3;
    public static int MIN_LENGTH_PASSWORD = 8;
    public static int MIN_LENGTH_TELEMOVEL = 9;


    public static synchronized AuthManager getInstance() {
        if (instance == null) instance = new AuthManager();
        return instance;
    }

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    public boolean isLoggedIn(Context context) {
        return new PreferencesManager(context).getToken() != null;
    }

    public void logout(Context context) {
        new PreferencesManager(context).deleteToken();
    }

    // region Requests
    public void login(Context context, User user, LoginListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.login(preferences.getApiUrl());

        // Colocar dados no body
        Map<String, String> params = new HashMap<>();
        params.put("username", user.getUsername());
        params.put("password", user.getPassword());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, new JSONObject(params), response -> {
                // Guardar o token
                String token = response.optString("access-token", null);
                preferences.setToken(token);

                // Obter dados do utilizador
                JSONObject perfil = response.optJSONObject("perfil");
                if (perfil == null) {
                    listener.onError();
                    return;
                }

                // Guardar dados em cache
                PerfilManager.getInstance().setCache(new User(
                    perfil.optInt("id"),
                    perfil.optString("username"),
                    perfil.optString("email"),
                    perfil.optString("nome"),
                    perfil.optString("telemovel")
                ));

                listener.onSuccess();
            },
            error -> {
                // Credenciais inválidas
                if (error.networkResponse != null && error.networkResponse.statusCode == 401) {
                    listener.onInvalidCredentials();
                    return;
                }

                listener.onError();
            }
        );

        getRequestQueue(context).add(request);
    }

    public void signup(Context context, User user, UserValidationListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.signup(preferences.getApiUrl());

        // Colocar dados no body
        Map<String, String> params = new HashMap<>();
        params.put("username", user.getUsername());
        params.put("password", user.getPassword());
        params.put("email", user.getEmail());
        params.put("nome", user.getNome());
        params.put("telemovel", user.getTelemovel());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, new JSONObject(params), response -> {
                // Obter e verificar se tem erros
                JSONObject errors = response.optJSONObject("errors");
                if (errors != null) {
                    if (errors.has("username")) listener.onUsernameTaken();
                    if (errors.has("email")) listener.onEmailTaken();

                    return;
                }

                // Guardar o token
                String token = response.optString("access-token", null);
                preferences.setToken(token);

                // Obter dados do utilizador
                JSONObject perfil = response.optJSONObject("perfil");
                if (perfil == null) {
                    listener.onError();
                    return;
                }

                // Guardar dados em cache
                PerfilManager.getInstance().setCache(new User(
                    perfil.optInt("id"),
                    perfil.optString("username"),
                    perfil.optString("email"),
                    perfil.optString("nome"),
                    perfil.optString("telemovel")
                ));

                listener.onSuccess();
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
    }

    public void validateToken(Context context, StandardListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.validateToken(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {
                // Guardar dados em cache
                PerfilManager.getInstance().setCache(new User(
                    response.optInt("id"),
                    response.optString("username"),
                    response.optString("email"),
                    response.optString("nome"),
                    response.optString("telemovel")
                ));

                listener.onSuccess();
            },
            error -> {
                // Logout se o token não é válido
                if (error.networkResponse != null && error.networkResponse.statusCode == 401) {
                    preferences.deleteToken();
                    listener.onError();
                }

                listener.onError();
            }
        );

        getRequestQueue(context).add(request);
    }
    // endregion
}
