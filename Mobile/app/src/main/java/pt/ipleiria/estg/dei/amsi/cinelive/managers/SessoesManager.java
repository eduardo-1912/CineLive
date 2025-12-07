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
import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ApiRoutes;

public class SessoesManager {
    private static SessoesManager instance = null;
    private static RequestQueue queue;

    public static synchronized SessoesManager getInstance() {
        if (instance == null) {
            instance = new SessoesManager();
        }

        return instance;
    }

    public void getSessoes(Context context, int filmeId, SessaoListener listener) {
        // Obter URL
        PreferencesManager preferences = new PreferencesManager(context);
        String url = ApiRoutes.sessoes(preferences.getApiUrl(), filmeId, preferences.getCinemaId());

        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.GET, url, null, response -> {
                // Obter os dados da sessão
                try {
                    Map<String, List<Sessao>> sessoesPorData = new LinkedHashMap<>();

                    Iterator<String> keys = response.keys();

                    while (keys.hasNext()) {
                        String data = keys.next();

                        JSONArray arr = response.getJSONArray(data);
                        List<Sessao> sessoes = new ArrayList<>();

                        for (int i = 0; i < arr.length(); i++) {
                            JSONObject obj = arr.getJSONObject(i);

                            Sessao s = new Sessao(
                                    obj.getInt("id"),
                                    obj.getString("hora_inicio")
                            );

                            sessoes.add(s);
                        }

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

        Volley.newRequestQueue(context).add(request);
    }
}
