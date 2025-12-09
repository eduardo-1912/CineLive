package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public interface CompraListener {
    void onSuccess(Compra compra, List<Bilhete> bilhetes);
    void onLocal(Compra compra, List<Bilhete> bilhetes);
    void onError();
}
