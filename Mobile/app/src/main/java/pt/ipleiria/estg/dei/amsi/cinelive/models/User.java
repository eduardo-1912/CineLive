package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class User {
    private int id;
    private String username, password, email, nome, telemovel;

    // Login
    public User(String username, String password) {
        this.username = username;
        this.password = password;
    }

    // Perfil
    public User(int id, String username, String email, String nome, String telemovel) {
        this.id = id;
        this.username = username;
        this.email = email;
        this.nome = nome;
        this.telemovel = telemovel;
    }

    // Signup
    public User(String username, String password, String email, String nome, String telemovel) {
        this.username = username;
        this.password = password;
        this.email = email;
        this.nome = nome;
        this.telemovel = telemovel;
    }

    public int getId() {return id;}
    public String getUsername() {return username;}
    public String getPassword() {return password;}
    public String getEmail() {return email;}
    public String getNome() {return nome;}
    public String getTelemovel() {return telemovel;}
}
