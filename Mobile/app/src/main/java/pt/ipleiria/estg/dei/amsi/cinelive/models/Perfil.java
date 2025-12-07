package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Perfil {
    private int id;
    private String username, email, nome, telemovel;

    public Perfil(int id, String username, String email, String nome, String telemovel) {
        this.id = id;
        this.username = username;
        this.email = email;
        this.nome = nome;
        this.telemovel = telemovel;
    }

    public int getId() { return id; }
    public String getUsername() { return username; }
    public String getEmail() { return email; }
    public String getNome() { return nome; }
    public String getTelemovel() { return telemovel; }
}
