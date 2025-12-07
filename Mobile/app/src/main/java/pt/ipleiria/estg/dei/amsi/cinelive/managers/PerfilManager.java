package pt.ipleiria.estg.dei.amsi.cinelive.managers;

import com.android.volley.RequestQueue;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Perfil;

public class PerfilManager {
    private static PerfilManager instance = null;
    private static RequestQueue queue;
    private Perfil perfil;

    public static synchronized PerfilManager getInstance() {
        if (instance == null) {
            instance = new PerfilManager();
        }

        return instance;
    }

    public Perfil getPerfil() {
        return perfil;
    }

    public void setPerfil(Perfil perfil) {
        this.perfil = perfil;
    }

    public void clearCache() {
        perfil = null;
    }
}
