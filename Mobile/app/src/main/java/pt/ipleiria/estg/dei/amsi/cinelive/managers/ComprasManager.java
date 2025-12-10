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
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.helpers.BilheteDBHelper;
import pt.ipleiria.estg.dei.amsi.cinelive.helpers.CompraDBHelper;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.BilhetesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CompraListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ComprasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class ComprasManager {
    private static ComprasManager instance = null;
    private static RequestQueue queue;
    private List<Compra> cache = new ArrayList<>();
    private CompraDBHelper comprasDB;
    private BilheteDBHelper bilhetesDB;

    public void init(Context context) {
        if (comprasDB == null) comprasDB = new CompraDBHelper(context);
        if (bilhetesDB == null) bilhetesDB = new BilheteDBHelper(context);
    }

    public static synchronized ComprasManager getInstance() {
        if (instance == null) instance = new ComprasManager();
        return instance;
    }

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    public List<Compra> getCache() {
        return cache;
    }

    public void clearCache() {
        cache.clear();
    }

    public Compra getCompraFromList(List<Compra> compras, int compraId) {
        for (Compra compra : compras) if (compra.getId() == compraId) return compra;
        return null;
    }

    public boolean hasBilhetesStored(int compraId) {
        return !bilhetesDB.getBilhetesByCompraId(compraId).isEmpty();
    }


    private void updateCache(Compra updatedCompra) {
        for (int i = 0; i < cache.size(); i++) {
            if (cache.get(i).getId() == updatedCompra.getId()) {
                // Substituir a compra antiga pela atualizada
                cache.set(i, updatedCompra);
                return;
            }
        }

        // Adicionar se não existir
        cache.add(updatedCompra);
    }

    // region Requests
    public void createCompra(Context context, Compra compra, StandardListener listener) {
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
            response -> listener.onSuccess(),
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
    }

    public void getCompras(Context context, ComprasListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.compras(preferences.getApiUrl(), preferences.getToken());

        // Evitar pedido à API se tiver cache
        if (!cache.isEmpty()) {
            listener.onSuccess(cache);
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
                clearCache();

                // Nenhuma compra foi encontrada
                if (response.length() == 0) {
                    listener.onEmpty();
                    return;
                }

                // Guardar compras em cache
                for (int i = 0; i < response.length(); i++) {
                    JSONObject obj = response.optJSONObject(i);
                    if (obj == null) continue;

                    cache.add(new Compra(
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
                comprasDB.saveCompras(cache);
                listener.onSuccess(cache);
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
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
            Compra compra = getCompraFromList(cache, compraId);
            if (compra != null) {
                getBilhetesByCompraId(context, compraId, new BilhetesListener() {
                    @Override
                    public void onSuccess(List<Bilhete> bilhetes) {
                        listener.onSuccess(compra, bilhetes);

                        // Guardar localmente
                        comprasDB.saveCompra(compra);
                        bilhetesDB.saveBilhetes(compraId, bilhetes);
                    }

                    @Override
                    public void onError() {
                    }
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

                    // Atualizar cache
                    updateCache(compra);

                    // Guardar localmente
                    comprasDB.saveCompra(compra);
                    bilhetesDB.saveBilhetes(compraId, bilhetes);

                    listener.onSuccess(compra, bilhetes);
                }
                catch (Exception e) {
                    listener.onError();
                }
            },
            error -> listener.onError()
        );

        getRequestQueue(context).add(request);
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

        getRequestQueue(context).add(request);
    }

    // endregion
}
