package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import pt.ipleiria.estg.dei.amsi.cinelive.models.User;

public interface PerfilListener {
    void onSuccess(User perfil);
    void onError();
}
