package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmeListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class FilmesManager {
    private static FilmesManager instance = null;
    private static RequestQueue queue;

    public enum Filter {ALL, EM_EXIBICAO, KIDS, BREVEMENTE};

    private List<Filme> filmesEmExibicao = new ArrayList<>();
    private List<Filme> filmesKids = new ArrayList<>();
    private List<Filme> filmesBrevemente = new ArrayList<>();

    public static synchronized FilmesManager getInstance() {
        if (instance == null) {
            instance = new FilmesManager();
        }

        return instance;
    }

    public List<Filme> getFilmesEmExibicao() {
        return filmesEmExibicao;
    }

    public void getFilmesEmExibicao(Context context, FilmeListener listener) {
        if (!filmesEmExibicao.isEmpty()) {
            listener.onFilmesLoaded(filmesEmExibicao);
            return;
        }

        PreferencesManager preferences = new PreferencesManager(context);

        String url = preferences.getApiUrl() + ApiRoutes.FILMES_EM_EXIBICAO  + preferences.getCinemaId();

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
                filmesEmExibicao.clear();

                if (response.length() == 0) {
                    listener.onInvalidCinema();
                    return;
                }

                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj != null) {
                        filmesEmExibicao.add(new Filme(
                            obj.optInt("id"),
                            obj.optString("titulo"),
                            obj.optString("poster_url")
                        ));
                    }
                }
                listener.onFilmesLoaded(filmesEmExibicao);
            },
            error -> {
                int status = error.networkResponse != null ? error.networkResponse.statusCode : -1;

                if (status == 404) {
                    listener.onInvalidCinema();
                } else {
                    listener.onError();
                }
            }
        );
        Volley.newRequestQueue(context).add(request);
    }
}
