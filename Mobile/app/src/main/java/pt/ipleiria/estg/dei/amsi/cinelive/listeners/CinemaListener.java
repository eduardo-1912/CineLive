package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

public interface CinemaListener {
    void onCinemasLoaded(List<Cinema> cinemas);
    void onError(String message);
}
