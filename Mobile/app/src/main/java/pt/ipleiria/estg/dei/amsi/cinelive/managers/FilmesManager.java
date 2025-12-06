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

    public enum Filter {EM_EXIBICAO, KIDS, BREVEMENTE};
    private List<Filme> exibicao = new ArrayList<>();
    private List<Filme> kids = new ArrayList<>();
    private List<Filme> brevemente = new ArrayList<>();

    List<Filme> filmes; // Cache

    public static synchronized FilmesManager getInstance() {
        if (instance == null) {
            instance = new FilmesManager();
        }

        return instance;
    }

    public List<Filme> getFilmes(Filter filter) {
        if (filter == Filter.KIDS) return kids;
        else if (filter == Filter.BREVEMENTE) return brevemente;
        else return exibicao;
    }

    public void clearCache() {
        exibicao.clear();
        kids.clear();
        brevemente.clear();
    }

    public void fetchFilmes(Context context, Filter filter, FilmeListener listener) {

        // Obter a lista
        if (filter == Filter.KIDS) filmes = kids;
        else if (filter == Filter.BREVEMENTE) filmes = brevemente;
        else filmes = exibicao;

        // Se tiver cache --> evitar pedido à API
        if (!filmes.isEmpty()) {
            listener.onSuccess(filmes);
            return;
        }

        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String apiUrl = preferences.getApiUrl();
        int cinemaId = preferences.getCinemaId();
        String url;

        if (filter == Filter.BREVEMENTE) url = ApiRoutes.filmesBrevemente(apiUrl);
        else if (filter == Filter.KIDS) url = ApiRoutes.filmesKids(apiUrl, cinemaId);
        else url = ApiRoutes.filmesEmExibicao(apiUrl, cinemaId);

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
                // Limpar lista
                filmes.clear();

                // O cinema escolhido não tem sessões
                if (filter != Filter.BREVEMENTE && response.length() == 0) {
                    listener.onInvalidCinema();
                    return;
                }

                // Obter filmes
                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj != null) {
                        filmes.add(new Filme(
                            obj.optInt("id"),
                            obj.optString("titulo"),
                            obj.optString("poster_url")
                        ));
                    }
                }

                listener.onSuccess(filmes);
            },
            error -> {
                if (filter != Filter.BREVEMENTE && error.networkResponse != null) {
                    // O cinema não existe
                    if (error.networkResponse.statusCode == 404) listener.onInvalidCinema();
                }

                else listener.onError();
            }
        );
        Volley.newRequestQueue(context).add(request);
    }
}
