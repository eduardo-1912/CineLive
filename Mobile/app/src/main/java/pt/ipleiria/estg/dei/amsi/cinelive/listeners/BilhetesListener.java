package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;

public interface BilhetesListener {
    void onSuccess(List<Bilhete> bilhetes);
    void onError();
}
