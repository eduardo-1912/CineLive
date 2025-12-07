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
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserFormListener;
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
        if (instance == null) {
            instance = new AuthManager();
        }

        return instance;
    }

    public void login(Context context, User user, LoginListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.login(preferences.getApiUrl());

        Map<String, String> params = new HashMap<>();
        params.put("username", user.getUsername());
        params.put("password", user.getPassword());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, new JSONObject(params),
            response -> {
                // Guardar o token (API verifica sempre se tem token válido)
                String token = response.optString("access-token", null);
                preferences.setToken(token);

                // Obter dados do utilizador
                JSONObject perfil = response.optJSONObject("perfil");

                // Guardar dados em cache
                if (perfil != null) {
                    PerfilManager.getInstance().setPerfil(
                        new User(
                            perfil.optInt("id"),
                            perfil.optString("username"),
                            perfil.optString("email"),
                            perfil.optString("nome"),
                            perfil.optString("telemovel")
                        )
                    );
                }

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

        Volley.newRequestQueue(context).add(request);
    }

    public void signup(Context context, User user, UserFormListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.signup(preferences.getApiUrl());

        Map<String, String> params = new HashMap<>();
        params.put("username", user.getUsername());
        params.put("password", user.getPassword());
        params.put("email", user.getEmail());
        params.put("nome", user.getNome());
        params.put("telemovel", user.getTelemovel());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, new JSONObject(params),
            response -> {
                // Verificar se tem erros
                JSONObject errors = response.optJSONObject("errors");

                // Obter erros
                if (errors != null) {
                    if (errors.has("username")) {
                        listener.onUsernameTaken();
                    }

                    if (errors.has("email")) {
                        listener.onEmailTaken();
                    }

                    return;
                }

                // Guardar o token (API verifica sempre se tem token válido)
                String token = response.optString("access-token", null);
                preferences.setToken(token);

                // Obter dados do utilizador
                JSONObject perfil = response.optJSONObject("perfil");

                // Guardar dados em cache
                if (perfil != null) {
                    PerfilManager.getInstance().setPerfil(
                        new User(
                            perfil.optInt("id"),
                            perfil.optString("username"),
                            perfil.optString("email"),
                            perfil.optString("nome"),
                            perfil.optString("telemovel")
                        )
                    );
                }

                listener.onSuccess();
            },
            error -> {listener.onError();}
        );

        Volley.newRequestQueue(context).add(request);
    }

    public void validateToken(Context context, StandardListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.validateToken(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null,
            response -> {
                // Guardar dados em cache
                PerfilManager.getInstance().setPerfil(
                    new User(
                        response.optInt("id"),
                        response.optString("username"),
                        response.optString("email"),
                        response.optString("nome"),
                        response.optString("telemovel")
                    )
                );

                listener.onSuccess();
            },
            error -> {
                // Se o token não é válido --> efetuar logout
                if (error.networkResponse != null && error.networkResponse.statusCode == 401) {
                    preferences.deleteToken();
                    listener.onError();
                }
            }
        );

        Volley.newRequestQueue(context).add(request);
    }

    public boolean isLoggedIn(Context context) {
        return (new PreferencesManager(context).getToken() != null);
    }

    public void logout(Context context) {
        new PreferencesManager(context).deleteToken();
    }
}
