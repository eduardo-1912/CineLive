package pt.ipleiria.estg.dei.amsi.cinelive.models;

public class Cinema {

    public int id;
    public String nome;
    public String morada;
    public int telefone;
    public String email;
    public String horario;
    public String capacidade;

    public Cinema(int id, String nome, String morada, int telefone, String email, String horario, String capacidade) {
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
    public int getTelefone() {return telefone;}
    public String getEmail() {return email;}
    public String getHorario() {return horario;}
    public String getCapacidade() {return capacidade;}

    @Override
    public String toString() {return nome;}

}
