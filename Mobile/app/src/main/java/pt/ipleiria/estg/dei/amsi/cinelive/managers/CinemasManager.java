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

    private CinemasManager() {}

    public static synchronized CinemasManager getInstance() {
        if (instance == null) {
            instance = new CinemasManager();
        }

        return instance;
    }

    public List<Cinema> getCinemas() {
        return cinemas;
    }

    public void getCinemasList(Context context, CinemaListener listener) {
        String url = new PreferencesManager(context).getApiUrl() + ApiRoutes.CINEMAS_LIST;

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
                cinemas.clear();
                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj != null) {
                        cinemas.add(new Cinema(obj.optInt("id"), obj.optString("nome")));
                    }
                }

                listener.onCinemasLoaded(cinemas);
            },
            error -> listener.onError("Erro ao obter cinemas")
        );

        Volley.newRequestQueue(context).add(request);
    }
}
