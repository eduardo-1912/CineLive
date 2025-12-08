package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;

public interface SessaoListener {
    void onSuccess(Sessao sessao);
    void onError();
}
