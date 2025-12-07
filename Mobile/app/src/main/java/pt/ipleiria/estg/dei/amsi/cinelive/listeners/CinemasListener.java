package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

public interface CinemasListener {
    void onSuccess(List<Cinema> cinemas);
    void onEmpty();
    void onError();
}
