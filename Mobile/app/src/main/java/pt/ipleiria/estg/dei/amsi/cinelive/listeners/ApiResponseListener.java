package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

public interface ApiResponseListener {
    void onSuccess(String response);
    void onError();
}