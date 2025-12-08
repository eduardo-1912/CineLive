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

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CompraListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ComprasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class ComprasManager {
    private static ComprasManager instance = null;
    private static RequestQueue queue;

    public static synchronized ComprasManager getInstance() {
        if (instance == null) instance = new ComprasManager();
        return instance;
    }

    private List<Compra> compras = new ArrayList<>();

    public void clearCache() {
        compras.clear();
    }

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

        // Enviar à API
        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST, url, body,
            response -> {
                listener.onSuccess();
            },
            error -> {
                listener.onError();
            }
        );

        Volley.newRequestQueue(context).add(request);
    }

    public void fetchCompras(Context context, ComprasListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.compras(preferences.getApiUrl(), preferences.getToken());

        if (compras != null) {
            listener.onSuccess(compras);
        }

        clearCache();

        JsonArrayRequest request = new JsonArrayRequest(
            Request.Method.GET, url, null, response -> {

            if (response.length() == 0) {
                listener.onEmpty();
                return;
            }

            // Obter compras
            for (int i = 0; i < response.length(); i++) {
                JSONObject obj = response.optJSONObject(i);
                if (obj != null) {
                    compras.add(new Compra(
                        obj.optInt("id"),
                        obj.optString("filme_titulo"),
                        obj.optString("data"),
                        obj.optString("cinema_nome"),
                        obj.optString("nome_sala"),
                        obj.optString("estado"),
                        obj.optString("total"),
                        obj.optString("sessao_data"),
                        obj.optString("sessao_hora_inicio"),
                        obj.optString("sessao_hora_fim"),
                        obj.optString("lugares")
                    ));
                }
            }

            listener.onSuccess(compras);

            },
            error -> listener.onError()
        );

        Volley.newRequestQueue(context).add(request);
    }

    public void getCompra(Context context, int id, CompraListener listener) {
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.compra(preferences.getApiUrl(), id, preferences.getToken());

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.GET, url, null,
                response -> {
                    try {
                        // Construir objeto Compra
                        Compra compra = new Compra(
                                response.optInt("id"),
                                response.optString("filme_titulo"),
                                response.optString("data"),
                                response.optString("cinema_nome"),
                                response.optString("sala_nome"),
                                response.optString("estado"),
                                response.optString("total"),
                                response.optString("sessao_data"),
                                response.optString("sessao_hora_inicio"),
                                response.optString("sessao_hora_fim"),
                                response.optString("lugares")
                        );

                        // Bilhetes
                        JSONArray arr = response.optJSONArray("bilhetes");
                        List<Bilhete> bilhetes = new ArrayList<>();

                        if (arr != null) {
                            for (int i = 0; i < arr.length(); i++) {
                                JSONObject obj = arr.getJSONObject(i);

                                Bilhete b = new Bilhete(
                                        obj.optInt("id"),
                                        response.optInt("id"),
                                        obj.optString("codigo"),
                                        obj.optString("lugar"),
                                        obj.optString("preco"),
                                        obj.optString("estado")
                                );

                                bilhetes.add(b);
                            }
                        }

                        // 🚨 FINALMENTE! DEVOLVER AO LISTENER
                        listener.onSuccess(compra, bilhetes);
                    }
                    catch (Exception e) {
                        listener.onError();
                    }
                },
                error -> listener.onError()
        );

        Volley.newRequestQueue(context).add(request);
    }

}
