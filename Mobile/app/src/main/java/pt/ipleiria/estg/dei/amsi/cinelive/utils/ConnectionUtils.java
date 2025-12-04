package pt.ipleiria.estg.dei.amsi.cinelive.utils;

import android.content.Context;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ConnectionListener;

public class ConnectionUtils {
    public static final int DEFAULT_TIMEOUT = 2000;
    public static final int FAST_TIMEOUT = 400;

    public static void testApiConnection(Context context, String url, int timeout, ConnectionListener listener) {
        RequestQueue queue = Volley.newRequestQueue(context);

        StringRequest request = new StringRequest(
            Request.Method.GET, url, response -> {
                try {
                    JSONObject json = new JSONObject(response);
                    listener.onSuccess(response);
                }
                catch (Exception e) {
                    listener.onError();
                }
            },
            error -> listener.onError()
        );

        request.setRetryPolicy(new DefaultRetryPolicy(
            timeout, 0, DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        ));

        queue.add(request);
    }

    public static boolean hasInternet(Context context) {
        ConnectivityManager connectivityManager = (ConnectivityManager)context.getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo netInfo = connectivityManager.getActiveNetworkInfo();

        return netInfo != null && netInfo.isConnected();
    }
}
