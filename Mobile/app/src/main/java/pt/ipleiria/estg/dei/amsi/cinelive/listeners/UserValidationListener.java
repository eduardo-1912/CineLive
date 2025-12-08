package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

public interface UserValidationListener {
    void onSuccess();
    void onUsernameTaken();
    void onEmailTaken();
    void onError();
}
