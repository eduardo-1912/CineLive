package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

public interface ConnectionListener {
    void onSuccess(String response);
    void onError();
}