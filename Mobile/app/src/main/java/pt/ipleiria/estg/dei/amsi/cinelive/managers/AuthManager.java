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
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class AuthManager {
    private static AuthManager instance = null;
    private static RequestQueue queue;

    public static synchronized AuthManager getInstance() {
        if (instance == null) {
            instance = new AuthManager();
        }

        return instance;
    }

    public static void deleteAccount() {
        // TODO: THIS
    }

    public void login(Context context, String username, String password, LoginListener listener) {
        // Obter o URL
        String url = ApiRoutes.login(new PreferencesManager(context).getApiUrl());

        Map<String, String> params = new HashMap<>();
        params.put("username", username);
        params.put("password", password);

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST, url, new JSONObject(params),
                response -> {

                    String token = response.optString("access-token", null);
                    if (token == null) {
                        listener.onError();
                        return;
                    }

                    // Guardar o token
                    new PreferencesManager(context).setToken(token);

                    listener.onSuccess();
                },
                error -> {
                    String msg = "Erro ao ligar ao servidor.";
                    if (error.networkResponse != null) {
                        if (error.networkResponse.statusCode == 401)
                            msg = "Credenciais inválidas.";
                        else
                            msg = "Erro na API: " + error.networkResponse.statusCode;
                    }
                    listener.onError();
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
