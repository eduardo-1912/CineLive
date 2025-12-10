package pt.ipleiria.estg.dei.amsi.cinelive.utils;

import android.content.Context;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ApiResponseListener;

public class ConnectionUtils {
    public static final int FAST_TIMEOUT = 400;
    public static final int DEFAULT_TIMEOUT = 2000;

    private static RequestQueue queue;

    private static RequestQueue getRequestQueue(Context context) {
        if (queue == null) queue = Volley.newRequestQueue(context.getApplicationContext());
        return queue;
    }

    public static void testApiConnection(Context context, String url, int timeout, ApiResponseListener listener) {
        StringRequest request = new StringRequest(
            Request.Method.GET, url, listener::onSuccess, error -> listener.onError()
        );

        // Timeout
        request.setRetryPolicy(new DefaultRetryPolicy(
            timeout, 0, DefaultRetryPolicy.DEFAULT_BACKOFF_MULT
        ));

        getRequestQueue(context).add(request);
    }

    public static boolean hasInternet(Context context) {
        ConnectivityManager cm =(ConnectivityManager)context.getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo netInfo = cm.getActiveNetworkInfo();

        return netInfo != null && netInfo.isConnected();
    }
}
