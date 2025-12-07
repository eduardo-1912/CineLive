package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

public interface UserFormListener {
    void onSuccess();
    void onUsernameTaken();
    void onEmailTaken();
    void onError();
}
