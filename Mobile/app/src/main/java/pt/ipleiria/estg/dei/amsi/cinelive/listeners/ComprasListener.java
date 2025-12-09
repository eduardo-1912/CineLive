package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Compra;

public interface ComprasListener {
    void onSuccess(List<Compra> compras);
    void onLocal(List<Compra> compras);
    void onEmpty();
    void onError();
}
