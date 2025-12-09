package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessaoListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.SessoesListener;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class SessoesManager {
    private static SessoesManager instance = null;
    private static RequestQueue queue;

    public static synchronized SessoesManager getInstance() {
        if (instance == null) instance = new SessoesManager();
        return instance;
    }

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    // region Requests
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

        getRequestQueue(context).add(request);
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

        getRequestQueue(context).add(request);
    }
    // endregion
}
