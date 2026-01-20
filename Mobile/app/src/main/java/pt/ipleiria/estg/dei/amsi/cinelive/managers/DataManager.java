package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.helpers.BilheteDBHelper;
import pt.ipleiria.estg.dei.amsi.cinelive.helpers.CompraDBHelper;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.BilhetesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CinemasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CompraListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ComprasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmeListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.FilmesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.LoginListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.PerfilListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessaoListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessoesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserValidationListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class DataManager {
    private static DataManager instance = null;

    private RequestQueue queue;
    private CompraDBHelper comprasDB;
    private BilheteDBHelper bilhetesDB;

    private DataManager(Context context) {
        // Queue
        queue = Volley.newRequestQueue(context.getApplicationContext());

        // DB
        comprasDB = new CompraDBHelper(context.getApplicationContext());
        bilhetesDB = new BilheteDBHelper(context.getApplicationContext());
    }

    public static synchronized DataManager getInstance(Context context) {
        if (instance == null) instance = new DataManager(context);
        return instance;
    }

    public RequestQueue getRequestQueue() {
        return queue;
    }

    // region Auth
    public static int MIN_LENGTH_USERNAME = 3;
    public static int MIN_LENGTH_PASSWORD = 8;
    public static int MIN_LENGTH_TELEMOVEL = 9;

    public boolean isLoggedIn(Context context) {
        String token = new PreferencesManager(context).getToken();
        return token != null && !token.trim().isEmpty() && !token.equals("null");
    }

    public void logout(Context context) {
        new PreferencesManager(context).deleteToken();

        // Limpar cache
        clearCacheFilmes();
        clearCacheCinemas();
        clearCacheCompras();
        clearCachePerfil();

        // Limpar dados locais
        bilhetesDB.delete();
        comprasDB.delete();

        // Cancelar requests
        RequestQueue queue = getRequestQueue();
        queue.cancelAll(request -> true);
    }

    public void login(Context context, User user, LoginListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.login(preferences.getApiUrl());

        // Colocar dados no body
        Map<String, String> params = new HashMap<>();
        params.put("username", user.getUsername());
        params.put("password", user.getPassword());

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST, url, new JSONObject(params), response -> {
            // Guardar o token
            String token = response.optString("access-token", null);
            preferences.setToken(token);

            // Obter dados do utilizador
            JSONObject perfil = response.optJSONObject("perfil");
            if (perfil == null) {
                listener.onError();
                return;
            }

            // Guardar dados em cache
            setCachePerfil(new User(
                perfil.optInt("id"),
                perfil.optString("username"),
                perfil.optString("email"),
                perfil.optString("nome"),
                perfil.optString("telemovel")
            ));

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

        getRequestQueue().add(request);
    }

    public void signup(Context context, User user, UserValidationListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.signup(preferences.getApiUrl());

        // Colocar dados no body
        Map<String, String> params = new HashMap<>();
        params.put("username", user.getUsername());
        params.put("password", user.getPassword());
        params.put("email", user.getEmail());
        params.put("nome", user.getNome());
        params.put("telemovel", user.getTelemovel());

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST, url, new JSONObject(params), response -> {
            // Obter e verificar se tem erros
            JSONObject errors = response.optJSONObject("errors");
            if (errors != null) {
                if (errors.has("username")) listener.onUsernameTaken();
                if (errors.has("email")) listener.onEmailTaken();

                return;
            }

            // Guardar o token
            String token = response.optString("access-token", null);
            preferences.setToken(token);

            // Obter dados do utilizador
            JSONObject perfil = response.optJSONObject("perfil");
            if (perfil == null) {
                listener.onError();
                return;
            }

            // Guardar dados em cache
            setCachePerfil(new User(
                perfil.optInt("id"),
                perfil.optString("username"),
                perfil.optString("email"),
                perfil.optString("nome"),
                perfil.optString("telemovel")
            ));

            listener.onSuccess();
        },
                error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void validateToken(Context context, StandardListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.validateToken(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url, null, response -> {
            // Guardar dados em cache
            setCachePerfil(new User(
                response.optInt("id"),
                response.optString("username"),
                response.optString("email"),
                response.optString("nome"),
                response.optString("telemovel")
            ));

            listener.onSuccess();
        },
        error -> {
            // Logout se o token não é válido
            if (error.networkResponse != null && error.networkResponse.statusCode == 401) {
                preferences.deleteToken();
                listener.onError();
            }

            listener.onError();
        });

        getRequestQueue().add(request);
    }
    // endregion

    // region Filmes
    public enum FilterFilmes {EM_EXIBICAO, KIDS, BREVEMENTE}
    private List<Filme> em_exibicao = new ArrayList<>();
    private List<Filme> kids = new ArrayList<>();
    private List<Filme> brevemente = new ArrayList<>();

    List<Filme> cacheFilmes; // Aponta para a lista atual

    public List<Filme> getCacheFilmes(FilterFilmes filter) {
        if (filter == FilterFilmes.KIDS) return kids;
        else if (filter == FilterFilmes.BREVEMENTE) return brevemente;
        else return em_exibicao;
    }

    public void clearCacheFilmes() {
        em_exibicao.clear();
        kids.clear();
        brevemente.clear();
    }

    public String getFilterUrl(Context context, FilterFilmes filter) {
        PreferencesManager preferences = new PreferencesManager(context);
        String apiUrl = preferences.getApiUrl();
        int cinemaId = preferences.getCinemaId();

        // Obter o filtro
        if (filter == FilterFilmes.BREVEMENTE) return ApiRoutes.filmesBrevemente(apiUrl);
        else if (filter == FilterFilmes.KIDS) return ApiRoutes.filmesKids(apiUrl, cinemaId);
        return ApiRoutes.filmesEmExibicao(apiUrl, cinemaId);
    }

    public void getFilmes(Context context, FilterFilmes filter, FilmesListener listener) {
        // Evitar pedido à API se tiver cache
        cacheFilmes = getCacheFilmes(filter);
        if (!cacheFilmes.isEmpty()) {
            listener.onSuccess(cacheFilmes);
            return;
        }

        // Obter URL
        String url = getFilterUrl(context, filter);

        JsonArrayRequest request = new JsonArrayRequest(
                Request.Method.GET, url, null, response -> {
            // Limpar lista
            cacheFilmes.clear();

            // O cinema escolhido não tem sessões
            if (filter != FilterFilmes.BREVEMENTE && response.length() == 0) {
                listener.onInvalidCinema();
                return;
            }

            // Guardar filmes em cache
            for (int i = 0; i < response.length(); i++) {
                JSONObject obj = response.optJSONObject(i);
                if (obj == null) continue;
                cacheFilmes.add(new Filme(
                        obj.optInt("id"),
                        obj.optString("titulo"),
                        obj.optString("poster_url")
                ));
            }

            listener.onSuccess(cacheFilmes);
        },
                error -> {
                    // O cinema não existe
                    if (filter != FilterFilmes.BREVEMENTE && error.networkResponse != null) {
                        if (error.networkResponse.statusCode == 404) {
                            listener.onInvalidCinema();
                            return;
                        }
                    }

                    listener.onError();
                }
        );

        getRequestQueue().add(request);
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

        getRequestQueue().add(request);
    }
    // endregion

    // region Sessões
    public void getSessoes(Context context, int filmeId, SessoesListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.sessoes(preferences.getApiUrl(), filmeId, preferences.getCinemaId());

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url, null, response -> {
            try {
                // Obter array associativo de sessões por data
                Map<String, List<Sessao>> sessoesPorData = new LinkedHashMap<>();
                Iterator<String> keys = response.keys();

                // Percorrer cada data
                while (keys.hasNext()) {
                    String data = keys.next();
                    JSONArray arraySessoes = response.getJSONArray(data);
                    List<Sessao> sessoes = new ArrayList<>();

                    // Para cada sessão desta data
                    for (int i = 0; i < arraySessoes.length(); i++) {
                        JSONObject obj = arraySessoes.getJSONObject(i);
                        Sessao sessao = new Sessao(obj.getInt("id"), obj.getString("hora_inicio"));
                        sessoes.add(sessao);
                    }

                    // Associar a sessão à data
                    sessoesPorData.put(data, sessoes);
                }

                listener.onSuccess(sessoesPorData);
            }
            catch (Exception e) {
                listener.onError();
            }
        },
                error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void getSessao(Context context, int id, SessaoListener listener) {
        // Obter o URL
        String url = ApiRoutes.sessao(new PreferencesManager(context).getApiUrl(), id);

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url, null, response -> {
            // Obter o objeto sala
            JSONObject sala = response.optJSONObject("sala");
            if (sala == null) {
                listener.onError();
                return;
            }

            // Converter lugares ocupados
            JSONArray arrayLugaresOcupados = sala.optJSONArray("lugares_ocupados");
            List<String> lugaresOcupados = new ArrayList<>();

            if (arrayLugaresOcupados != null) {
                for (int i = 0; i < arrayLugaresOcupados.length(); i++) {
                    lugaresOcupados.add(arrayLugaresOcupados.optString(i));
                }
            }

            // Obter os dados da sessão
            try {
                Sessao sessao = new Sessao(
                        response.optInt("id"),
                        response.optString("data"),
                        response.optString("hora_inicio"),
                        response.optString("hora_fim"),
                        response.optString("cinema_nome"),
                        sala.optString("nome"),
                        sala.optDouble("preco_bilhete"),
                        sala.optInt("num_filas"),
                        sala.optInt("num_colunas"),
                        lugaresOcupados
                );

                listener.onSuccess(sessao);
            }
            catch (Exception e) {
                listener.onError();
            }
        },
                error -> listener.onError()
        );

        getRequestQueue().add(request);
    }
    // endregion

    // region Cinemas
    private List<Cinema> cacheCinemas = new ArrayList<>();

    public List<Cinema> getCacheCinemas() {
        return cacheCinemas;
    }

    public void clearCacheCinemas() {
        cacheCinemas.clear();
    }

    public void getCinemas(Context context, CinemasListener listener) {
        // Evitar pedido à API se tiver cache
        if (!cacheCinemas.isEmpty()) {
            listener.onSuccess(cacheCinemas);
            return;
        }

        // Obter o URL
        String url = ApiRoutes.cinemas(new PreferencesManager(context).getApiUrl());

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
            // Limpar cacheCinemas
            cacheCinemas.clear();

            // Nenhum cinema foi encontrado
            if (response.length() == 0) {
                listener.onEmpty();
                return;
            }

            // Guardar cinemas em cacheCinemas
            for (int i = 0; i < response.length(); i++) {
                JSONObject obj = response.optJSONObject(i);
                if (obj == null) continue;

                cacheCinemas.add(new Cinema(
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

            listener.onSuccess(cacheCinemas);
        },
        error -> listener.onError()
        );

        getRequestQueue().add(request);
    }
    // endregion

    // region Compras
    private List<Compra> cacheCompras = new ArrayList<>();

    public List<Compra> getCacheCompras() {
        return cacheCompras;
    }

    public void clearCacheCompras() {
        cacheCompras.clear();
    }

    public Compra getCompraFromList(List<Compra> compras, int compraId) {
        for (Compra compra : compras) if (compra.getId() == compraId) return compra;
        return null;
    }

    public boolean hasBilhetesStored(int compraId) {
        return !bilhetesDB.getBilhetesByCompraId(compraId).isEmpty();
    }

    public void createCompra(Context context, Compra compra, StandardListener listener) {
        // Verificar se a compra tem lugares selecionados
        if (compra.getLugaresSelecionados() == null || compra.getLugaresSelecionados().isEmpty()) {
            listener.onError();
            return;
        }

        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.compras(preferences.getApiUrl(), preferences.getToken());

        // Criar objeto JSON
        JSONObject body = new JSONObject();
        try {
            body.put("sessao_id", compra.getSessaoId());
            body.put("pagamento", compra.getPagamento());
            body.put("lugares", new JSONArray(compra.getLugaresSelecionados()));
        }
        catch (Exception e) {
            listener.onError();
            return;
        }

        // Enviar pedido à API
        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, body,
            response -> {
                // Verificar se os lugares são válidos
                if (response.optString("status").equals("error")) {
                    listener.onError();
                    return;
                }

                listener.onSuccess();
            },
            error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void getCompras(Context context, ComprasListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.compras(preferences.getApiUrl(), preferences.getToken());

        // Evitar pedido à API se tiver cache
        if (!cacheCompras.isEmpty()) {
            listener.onSuccess(cacheCompras);
            return;
        }

        // Se não tiver internet nem cache --> mostrar compras guardadas localmente
        if (!ConnectionUtils.hasInternet(context)) {
            List<Compra> compras = comprasDB.getCompras();
            if (!compras.isEmpty()) {
                listener.onLocal(compras);
                return;
            }
        }

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {
            // Limpar cache
            clearCacheCompras();

            // Nenhuma compra foi encontrada
            if (response.length() == 0) {
                listener.onEmpty();
                return;
            }

            // Guardar compras em cache
            for (int i = 0; i < response.length(); i++) {
                JSONObject obj = response.optJSONObject(i);
                if (obj == null) continue;

                cacheCompras.add(new Compra(
                    obj.optInt("id"),
                    obj.optString("data"),
                    obj.optString("total"),
                    obj.optString("estado"),
                    obj.optString("pagamento"),
                    obj.optString("filme_titulo"),
                    obj.optString("cinema_nome"),
                    obj.optString("sala_nome"),
                    obj.optString("sessao_data"),
                    obj.optString("sessao_hora_inicio"),
                    obj.optString("sessao_hora_fim"),
                    obj.optString("lugares")
                ));
            }

            // Guardar localmente
            comprasDB.saveCompras(cacheCompras);
            listener.onSuccess(cacheCompras);
        },
        error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void getCompra(Context context, int compraId, boolean useCache, CompraListener listener) {
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.compra(preferences.getApiUrl(), compraId, preferences.getToken());

        // Não tem internet --> usar compra guardada localmente se tiver
        if (!ConnectionUtils.hasInternet(context)) {
            Compra compra = getCompraFromList(comprasDB.getCompras(), compraId);
            List<Bilhete> bilhetes = bilhetesDB.getBilhetesByCompraId(compraId);

            if (compra != null && !bilhetes.isEmpty()) {
                listener.onLocal(compra, bilhetes);
                return;
            }
        }

        // Usar cache
        if (useCache) {
            Compra compra = getCompraFromList(cacheCompras, compraId);
            if (compra != null) {
                getBilhetesByCompraId(context, compraId, new BilhetesListener() {
                    @Override
                    public void onSuccess(List<Bilhete> bilhetes) {
                        listener.onSuccess(compra, bilhetes);

                        // Guardar localmente
                        if (compra.getLugares() != null && !compra.getLugares().isEmpty()) {
                            comprasDB.saveCompra(compra);
                        }
                        bilhetesDB.saveBilhetes(compraId, bilhetes);
                    }

                    @Override
                    public void onError() {}
                });
            }
        }

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {
            try {
                // Obter compra
                Compra compra = new Compra(
                    response.optInt("id"),
                    response.optString("data"),
                    response.optString("total"),
                    response.optString("estado"),
                    response.optString("pagamento"),
                    response.optString("filme_titulo"),
                    response.optString("cinema_nome"),
                    response.optString("sala_nome"),
                    response.optString("sessao_data"),
                    response.optString("sessao_hora_inicio"),
                    response.optString("sessao_hora_fim"),
                    response.optString("lugares")
                );

                // Obter bilhetes
                JSONArray arrayBilhetes = response.optJSONArray("bilhetes");
                if (arrayBilhetes == null) {
                    listener.onError();
                    return;
                }

                List<Bilhete> bilhetes = new ArrayList<>();
                for (int i = 0; i < arrayBilhetes.length(); i++) {
                    JSONObject obj = arrayBilhetes.getJSONObject(i);

                    Bilhete bilhete = new Bilhete(
                        obj.optInt("id"),
                        response.optInt("id"),
                        obj.optString("codigo"),
                        obj.optString("lugar"),
                        obj.optString("preco"),
                        obj.optString("estado")
                    );

                    bilhetes.add(bilhete);
                }

                // Guardar localmente
                if (compra.getLugares() != null && !compra.getLugares().isEmpty()) {
                    comprasDB.saveCompra(compra);
                }
                bilhetesDB.saveBilhetes(compraId, bilhetes);

                listener.onSuccess(compra, bilhetes);
            }
            catch (Exception e) {
                listener.onError();
            }
        },
        error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void getBilhetesByCompraId(Context context, int compraId, BilhetesListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.bilhetes(preferences.getApiUrl(), compraId, preferences.getToken());

        JsonArrayRequest request = new JsonArrayRequest(
                Request.Method.GET, url, null, response -> {
            // Obter bilhetes
            List<Bilhete> bilhetes = new ArrayList<>();

            for (int i = 0; i < response.length(); i++) {
                JSONObject obj = response.optJSONObject(i);
                if (obj == null) continue;

                bilhetes.add(new Bilhete(
                    obj.optInt("id"),
                    compraId,
                    obj.optString("codigo"),
                    obj.optString("lugar"),
                    obj.optString("preco"),
                    obj.optString("estado")
                ));
            }

            // Guardar localmente
            bilhetesDB.saveBilhetes(compraId, bilhetes);

            listener.onSuccess(bilhetes);
        },

        error -> listener.onError()
        );

        getRequestQueue().add(request);
    }
    // endregion

    // region Perfil
    private User cachePerfil;

    public User getCachePerfil() {
        return cachePerfil;
    }

    public void setCachePerfil(User cachePerfil) {
        this.cachePerfil = cachePerfil;
    }

    public void clearCachePerfil() {
        cachePerfil = null;
    }

    public void getPerfil(Context context, PerfilListener listener) {
        // Evitar pedido à API se tiver cache
        if (cachePerfil != null) {
            listener.onSuccess(cachePerfil);
            return;
        }

        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {
            // Guardar em cache
            setCachePerfil(new User(
                response.optInt("id"),
                response.optString("username"),
                response.optString("email"),
                response.optString("nome"),
                response.optString("telemovel")
            ));

            listener.onSuccess(cachePerfil);
        },
        error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void updatePerfil(Context context, User original, User edited, UserValidationListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        // Obter campos editados
        Map<String, String> params = getFormEditedFields(original, edited);

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
            clearCachePerfil();
            listener.onSuccess();
        },
        error -> listener.onError()
        );

        getRequestQueue().add(request);
    }

    public void deletePerfil(Context context, StandardListener listener) {
        // Obter o URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.perfil(preferences.getApiUrl(), preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.DELETE, url, null, response -> {
            // Eliminar token e limpar cache
            preferences.deleteToken();
            clearCachePerfil();

            listener.onSuccess();
        },
        error -> {
            // Eliminar token e limpar cache se o perfil não existe
            if (error.networkResponse != null && error.networkResponse.statusCode == 404) {
                preferences.deleteToken();
                clearCachePerfil();
            }

            listener.onError();
        });

        getRequestQueue().add(request);
    }

    private Map<String, String> getFormEditedFields(User original, User edited) {
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

    // endregion
}