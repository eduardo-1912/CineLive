package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;
import android.content.SharedPreferences;

public class PreferencesManager {

    private final SharedPreferences preferences;

    private static final String PREFERENCES_NAME = "UserPreferences";

    private static final String CINEMA_ID = "cinema_id";
    private static final String ACCESS_TOKEN = "access_token";

    private static final String DEFAULT_API_HOST = "http://172.22.21.212";
    private static final String DEFAULT_API_PATH = "/CineLive/Web/backend/web/api";
    private static final String API_HOST = "api_host";
    private static final String API_PATH = "api_path";

    public PreferencesManager(Context context) {
        preferences = context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE);

        // Se o URL da API for null --> reset
        if (this.getApiUrl() == null) {
            this.resetApiUrl();
        }
    }

    public int getCinemaId() {

        return preferences.getInt(CINEMA_ID, -1);
    }

    public void setCinemaId(int id) {
        preferences.edit().putInt(CINEMA_ID, id).apply();
    }

    public String getAccessToken() {
        return preferences.getString(ACCESS_TOKEN, null);
    }

    public void setAccessToken(String accessToken) {
        preferences.edit().putString(ACCESS_TOKEN, accessToken).apply();
    }

    public String getApiHost() {
        return preferences.getString(API_HOST, null);
    }

    public String getApiPath() {
        return preferences.getString(API_PATH, null);
    }

    public String getApiUrl() {
        if (getApiHost() != null && getApiPath() != null) {
            return getApiHost() + getApiPath();
        }
        return null;
    }

    public void setApiUrl(String apiHost, String apiPath) {
        preferences.edit().putString(API_HOST, apiHost).apply();
        preferences.edit().putString(API_PATH, apiPath).apply();
    }

    public String resetApiUrl() {
        preferences.edit().putString(API_HOST, DEFAULT_API_HOST).apply();
        preferences.edit().putString(API_PATH, DEFAULT_API_PATH).apply();

        return this.getApiUrl();
    }
}
