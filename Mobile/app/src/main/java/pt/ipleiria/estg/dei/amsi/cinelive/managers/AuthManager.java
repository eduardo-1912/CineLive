package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ConnectionListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.LoginListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ValidateTokenListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Perfil;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class AuthManager {
    private static AuthManager instance = null;
    private static RequestQueue queue;

    public static synchronized AuthManager getInstance() {
        if (instance == null) {
            instance = new AuthManager();
        }

        return instance;
    }

    public void login(Context context, String username, String password, LoginListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.login(preferences.getApiUrl());

        Map<String, String> params = new HashMap<>();
        params.put("username", username);
        params.put("password", password);

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, new JSONObject(params),
            response -> {
                // Guardar o token (API verifica sempre se tem token válido)
                String token = response.optString("access-token", null);
                preferences.setToken(token);

                // Obter dados do utilizador
                JSONObject user = response.optJSONObject("user");

                // Guardar dados em cache
                if (user != null) {
                    PerfilManager.getInstance().setPerfil(
                        new Perfil(
                            user.optInt("id"),
                            user.optString("username"),
                            user.optString("email"),
                            user.optString("nome"),
                            user.optString("telemovel")
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

    public void validateToken(Context context, ValidateTokenListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.validateToken(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null,
            response -> {
                // Guardar dados em cache
                PerfilManager.getInstance().setPerfil(
                    new Perfil(
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
