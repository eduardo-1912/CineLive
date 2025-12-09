package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmeListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class FilmesManager {
    private static FilmesManager instance = null;
    private static RequestQueue queue;

    public enum Filter {EM_EXIBICAO, KIDS, BREVEMENTE}
    private List<Filme> em_exibicao = new ArrayList<>();
    private List<Filme> kids = new ArrayList<>();
    private List<Filme> brevemente = new ArrayList<>();

    List<Filme> cache; // Aponta para a lista atual

    public static synchronized FilmesManager getInstance() {
        if (instance == null) instance = new FilmesManager();
        return instance;
    }

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    public List<Filme> getCache(Filter filter) {
        if (filter == Filter.KIDS) return kids;
        else if (filter == Filter.BREVEMENTE) return brevemente;
        else return em_exibicao;
    }

    public String getFilterUrl(Context context, Filter filter) {
        PreferencesManager preferences = new PreferencesManager(context);
        String apiUrl = preferences.getApiUrl();
        int cinemaId = preferences.getCinemaId();

        // Obter o filtro
        if (filter == Filter.BREVEMENTE) return ApiRoutes.filmesBrevemente(apiUrl);
        else if (filter == Filter.KIDS) return ApiRoutes.filmesKids(apiUrl, cinemaId);
        return ApiRoutes.filmesEmExibicao(apiUrl, cinemaId);
    }

    public void clearCache() {
        em_exibicao.clear();
        kids.clear();
        brevemente.clear();
    }

    // region Requests
    public void getFilmes(Context context, Filter filter, FilmesListener listener) {
        // Evitar pedido à API se tiver cache
        cache = getCache(filter);
        if (!cache.isEmpty()) {
            listener.onSuccess(cache);
            return;
        }

        // Obter URL
        String url = getFilterUrl(context, filter);

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
                // Limpar lista
                cache.clear();

                // O cinema escolhido não tem sessões
                if (filter != Filter.BREVEMENTE && response.length() == 0) {
                    listener.onInvalidCinema();
                    return;
                }

                // Guardar filmes em cache
                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj == null) continue;
                    cache.add(new Filme(
                        obj.optInt("id"),
                        obj.optString("titulo"),
                        obj.optString("poster_url")
                    ));
                }

                listener.onSuccess(cache);
            },
            error -> {
                // O cinema não existe
                if (filter != Filter.BREVEMENTE && error.networkResponse != null) {
                    if (error.networkResponse.statusCode == 404) {
                        listener.onInvalidCinema();
                        return;
                    }
                }

                listener.onError();
            }
        );

        getRequestQueue(context).add(request);
    }

    public void getFilme(Context context, int id, FilmeListener listener) {
        // Obter URL
        String url = ApiRoutes.filme(new PreferencesManager(context).getApiUrl(), id);

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {
                // Obter os dados do filme
                try {
                    Filme filme = new Filme(
                        response.optInt("id"),
                        response.optString("titulo"),
                        response.optString("poster_url"),
                        response.optString("rating"),
                        response.optString("generos"),
                        response.optString("estreia"),
                        response.optString("duracao"),
                        response.optString("idioma"),
                        response.optString("realizacao"),
                        response.optString("sinopse"),
                        response.optBoolean("has_sessoes")
                    );

                    listener.onSuccess(filme);
                }
                catch (Exception e) {
                    listener.onError();
                }
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
    }
    // endregion
}
