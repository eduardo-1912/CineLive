package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.PerfilListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserFormListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class PerfilManager {
    private static PerfilManager instance = null;
    private static RequestQueue queue;
    private User perfil;

    public static synchronized PerfilManager getInstance() {
        if (instance == null) {
            instance = new PerfilManager();
        }

        return instance;
    }

    public User getPerfil() {
        return perfil;
    }

    public void setPerfil(User perfil) {
        this.perfil = perfil;
    }

    public void clearCache() {
        perfil = null;
    }

    private Map<String, String> getEditedFields(User original, User edited) {
        Map<String, String> params = new HashMap<>();

        if (!edited.getUsername().equals(original.getUsername())) params.put("username", edited.getUsername());
        if (!edited.getEmail().equals(original.getEmail())) params.put("email", edited.getEmail());
        if (!edited.getNome().equals(original.getNome())) params.put("nome", edited.getNome());
        if (!edited.getTelemovel().equals(original.getTelemovel())) params.put("telemovel", edited.getTelemovel());
        if (!edited.getPassword().isEmpty()) params.put("password", edited.getPassword());

        return params;
    }

    public void fetchPerfil(Context context, PerfilListener listener) {
        // Se tiver cache --> evitar pedido à API
        if (perfil != null) {
            listener.onSuccess(perfil);
            return;
        }

        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {

            setPerfil(
                new User(
                    response.optInt("id"),
                    response.optString("username"),
                    response.optString("email"),
                    response.optString("nome"),
                    response.optString("telemovel")
                )
            );

            listener.onSuccess(getPerfil());
            },

            error -> listener.onError()
        );

        Volley.newRequestQueue(context).add(request);
    }

    public void updatePerfil(Context context, User original, User edited, UserFormListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        // Obter campos editados
        Map<String, String> params = getEditedFields(original, edited);

        // Se não houver alterações --> voltar
        if (params.isEmpty()) {
            listener.onSuccess();
            return;
        }

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.PUT, url, new JSONObject(params),
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

                // Limpar cache
                clearCache();
                listener.onSuccess();
            },
            error -> {
                listener.onError();
            }
        );

        Volley.newRequestQueue(context).add(request);
    }

    public void deleteAccount(Context context, StandardListener listener) {
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
                // O perfil não existe
                if (error.networkResponse != null && error.networkResponse.statusCode == 404) {
                    // Eliminar token e limpar cache
                    preferences.deleteToken();
                    clearCache();
                }

                listener.onError();
            }
        );

        Volley.newRequestQueue(context).add(request);
    }
}
