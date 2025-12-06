package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;

public interface FilmeListener {
    void onSuccess(List<Filme> filmes);
    void onInvalidCinema();
    void onError();
}
