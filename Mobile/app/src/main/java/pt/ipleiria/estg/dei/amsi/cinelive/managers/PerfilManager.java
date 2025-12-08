package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.PerfilListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserValidationListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class PerfilManager {
    private static PerfilManager instance = null;
    private static RequestQueue queue;
    private User cache;

    public static synchronized PerfilManager getInstance() {
        if (instance == null) instance = new PerfilManager();
        return instance;
    }

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    public User getCache() {
        return cache;
    }

    public void setCache(User cache) {
        this.cache = cache;
    }

    public void clearCache() {
        cache = null;
    }

    // region Requests
    public void getPerfil(Context context, PerfilListener listener) {
        // Evitar pedido à API se tiver cache
        if (cache != null) {
            listener.onSuccess(cache);
            return;
        }

        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {
                // Guardar em cache
                setCache(new User(
                    response.optInt("id"),
                    response.optString("username"),
                    response.optString("email"),
                    response.optString("nome"),
                    response.optString("telemovel")
                ));

                listener.onSuccess(cache);
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
    }

    public void updatePerfil(Context context, User original, User edited, UserValidationListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        // Obter campos editados
        Map<String, String> params = getEditedFields(original, edited);

        // Voltar se não houver alterações
        if (params.isEmpty()) {
            listener.onSuccess();
            return;
        }

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.PUT, url, new JSONObject(params), response -> {
                // Obter erros
                JSONObject errors = response.optJSONObject("errors");
                if (errors != null) {
                    if (errors.has("username")) listener.onUsernameTaken();
                    if (errors.has("email")) listener.onEmailTaken();
                    return;
                }

                // Limpar cache
                clearCache();
                listener.onSuccess();
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
    }

    public void deletePerfil(Context context, StandardListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.DELETE, url, null, response -> {
                // Eliminar token e limpar cache
                preferences.deleteToken();
                clearCache();

                listener.onSuccess();
            },
            error -> {
                // Eliminar token e limpar cache se o perfil não existe
                if (error.networkResponse != null && error.networkResponse.statusCode == 404) {
                    preferences.deleteToken();
                    clearCache();
                }

                listener.onError();
            }
        );

        getRequestQueue(context).add(request);
    }
    // endregion

    private Map<String, String> getEditedFields(User original, User edited) {
        Map<String, String> params = new HashMap<>();

        if (!edited.getUsername().equals(original.getUsername()))
            params.put("username", edited.getUsername());
        if (!edited.getEmail().equals(original.getEmail()))
            params.put("email", edited.getEmail());
        if (!edited.getNome().equals(original.getNome()))
            params.put("nome", edited.getNome());
        if (!edited.getTelemovel().equals(original.getTelemovel()))
            params.put("telemovel", edited.getTelemovel());
        if (!edited.getPassword().isEmpty())
            params.put("password", edited.getPassword());

        return params;
    }
}
