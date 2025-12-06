package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CinemaListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class CinemasManager {
    private static CinemasManager instance = null;
    private static RequestQueue queue;
    private List<Cinema> cinemas = new ArrayList<>();

    public static synchronized CinemasManager getInstance() {
        if (instance == null) {
            instance = new CinemasManager();
        }

        return instance;
    }

    public List<Cinema> getCinemas() {
        return cinemas;
    }

    public void clearCache() {
        cinemas.clear();
    }

    public void fetchCinemas(Context context, CinemaListener listener) {
        // Se tiver cache --> evitar pedido à API
        if (!cinemas.isEmpty()) {
            listener.onSuccess(cinemas);
            return;
        }

        // Obter o URL
        String url = ApiRoutes.cinemas(new PreferencesManager(context).getApiUrl());

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
                // Limpar lista
                cinemas.clear();

                // Nenhum cinema foi encontrado
                if (response.length() == 0) {
                    listener.onEmpty();
                    return;
                }

                // Obter cinemas
                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj != null) {
                        cinemas.add(new Cinema(
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
                }

                listener.onSuccess(cinemas);
            },
            error -> listener.onError()
        );
        Volley.newRequestQueue(context).add(request);
    }
}
