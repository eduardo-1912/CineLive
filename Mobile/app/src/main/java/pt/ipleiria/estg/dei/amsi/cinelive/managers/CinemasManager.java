package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CinemasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class CinemasManager {
    private static CinemasManager instance = null;
    private static RequestQueue queue;
    private List<Cinema> cache = new ArrayList<>();

    public static synchronized CinemasManager getInstance() {
        if (instance == null) instance = new CinemasManager();
        return instance;
    }

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    public List<Cinema> getCache() {
        return cache;
    }

    public void clearCache() {
        cache.clear();
    }

    public void getCinemas(Context context, CinemasListener listener) {
        // Evitar pedido à API se tiver cache
        if (!cache.isEmpty()) {
            listener.onSuccess(cache);
            return;
        }

        // Obter o URL
        String url = ApiRoutes.cinemas(new PreferencesManager(context).getApiUrl());

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
                // Limpar cache
                cache.clear();

                // Nenhum cinema foi encontrado
                if (response.length() == 0) {
                    listener.onEmpty();
                    return;
                }

                // Guardar cinemas em cache
                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj == null) continue;

                    cache.add(new Cinema(
                        obj.optInt("id"),
                        obj.optString("nome"),
                        obj.optString("morada"),
                        obj.optString("telefone"),
                        obj.optString("email"),
                        obj.optString("horario"),
                        obj.optString("capacidade"),
                        obj.optBoolean("has_sessoes")
                    ));
                }

                listener.onSuccess(cache);
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
    }
}
