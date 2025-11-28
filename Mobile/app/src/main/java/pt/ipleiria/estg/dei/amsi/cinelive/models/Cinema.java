package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Cinema {
    private int id;
    private String nome, morada, telefone, email, horario, capacidade;

    public Cinema(int id, String nome) {
        this.id = id;
        this.nome = nome;
    }

    public Cinema(int id, String nome, String morada, String telefone, String email, String horario, String capacidade) {
        this.id = id;
        this.nome = nome;
        this.morada = morada;
        this.telefone = telefone;
        this.email = email;
        this.horario = horario;
        this.capacidade = capacidade;
    }

    public int getId() {return id;}
    public String getNome() {return nome;}
    public String getMorada() {return morada;}
    public String getTelefone() {return telefone;}
    public String getEmail() {return email;}
    public String getHorario() {return horario;}
    public String getCapacidade() {return capacidade;}

    @Override
    public String toString() {return nome;}
}
