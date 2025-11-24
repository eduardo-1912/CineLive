package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import android.content.Context;
import android.content.SharedPreferences;

public class PreferencesManager {

    private static final String PREFERENCES_NAME = "UserPreferences";
    private static final String KEY_CINEMA_ID = "cinema_id";

    private final SharedPreferences preferences;

    public PreferencesManager(Context context) {
        preferences = context.getSharedPreferences(PREFERENCES_NAME, Context.MODE_PRIVATE);
    }


    // Obter cinema selecionado
    public int getCinemaId() {
        return preferences.getInt(KEY_CINEMA_ID, -1);
    }

    // Guardar cinema selecionado
    public void setCinemaId(int id) {
        preferences.edit().putInt(KEY_CINEMA_ID, id).apply();
    }


}
